<?php
  //共通ファイル読込み・デバッグスタート
  require('function.php');
  debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// GETデータを格納
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';

// DBからメッセージデータを取得
$dbFormData = (!empty($m_id)) ? getUsersMessage($_SESSION['user_id'], $m_id) : '';
// 新規登録か編集かの判別フラグ
$is_edit = (empty($dbFormData)) ? false : true;
// DBからメンバーデータを取得
$dbUsersList = getOtherUsers($_SESSION['user_id']);

// DBからテンプレートデータを取得
$dbTemplate = getTemplate();

// デバッグ出力
debug('メッセージID：'.$m_id);
debug('$dbFormData：'.print_r($dbFormData,true));
debug('$dbUsersList：'.print_r($dbUsersList,true));
debug('$dbTemplate：'.print_r($dbTemplate,true));

// パラメータ改ざんチェック
//================================
// GETパラメータがあるがDBから取得したデータが空だった場合
if(!empty($m_id) && empty($dbFormData)){
  debug('GETパラメータのIDが違います。新規登録画面を表示します。');
  header("Location:cardCreate.php");
  exit();
}

// POST送信時処理
//================================
if(!empty($_POST)){
  debug('POST送信あり');
  //テキスト情報
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));

  //変数にユーザー情報を代入
  $to_user = $_POST['to_user'];
  $card_id = $_POST['card_id'];
  $msg = $_POST['msg']; 
  $temp_pic = $_POST['temp_pic'];
  debug('★$temp_pic：'.$temp_pic);

  //POST画像があればアップロードし、パスを格納
  if(!empty($_FILES['pic']['name']) ) {
    $pic = uploadImg($_FILES['pic'],'pic') ;
  }else{
    // 一時保存した画像のパスがあれば格納
    $pic = (!empty($temp_pic) ) ? $temp_pic : '';
    // POST画像がなくDBに登録がある場合はDBのパスを格納
    $pic = ( empty($pic) && !empty($dbFormData['pic']) ) ? $dbFormData['pic'] : $pic;
  }
  debug('★$pic：'.$pic);

  // バリデーションチェック
  // 新規登録時
  if(empty($dbFormData)){
    //セレクトボックスチェック
    validRequired($to_user, 'to_user');
    validSelect($to_user, 'to_user');
    //ラジオボタンチェック
    validRequired($card_id, 'card_id');
    validSelect($card_id, 'card_id');
    //メッセージチェック
    validRequired($msg, 'msg');
    validMaxLen($msg, 'msg', 140);

  }else{
    // 更新の場合
    if($dbFormData['to_user'] !== $to_user){
      //セレクトボックスチェック
      validRequired($to_user, 'to_user');
      validSelect($to_user, 'to_user');
    }
    if($dbFormData['card_id'] !== $card_id){
      //ラジオボタンチェック
      validRequired($card_id, 'card_id');
      validSelect($card_id, 'card_id');
    }
    if($dbFormData['msg'] !== $msg){
      // メッセージチェック
      validRequired($msg, 'msg');
      validMaxLen($msg, 'msg', 140);
    }
  }

  if(empty($err_msg)){
    debug('バリデーションOK');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // 編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
      if($is_edit){
        debug('DB更新です。');
        // SQL文作成
        $sql = 'UPDATE messages SET to_user = :to_user, card_id = :card_id, msg = :msg, pic = :pic WHERE from_user = :u_id AND id = :m_id';
        // データ流し込み
        $data = array(':to_user' => $to_user, ':card_id' => $card_id, ':msg' => $msg, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':m_id' => $m_id);
      }else{
        debug('DB新規登録です。');
        // SQL文作成
        $sql = 'INSERT INTO messages (to_user, from_user, msg, card_id, pic, created_at) VALUES (:to_user, :u_id, :msg, :card_id, :pic, :created_at)';
        // データ流し込み
        $data = array(':to_user' => $to_user, ':card_id' => $card_id, ':msg' => $msg, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':created_at' => date('Y-m-d H:i:s'));
      }
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if($stmt){
        $_SESSION['msg_success'] = SUC04;
        debug('マイページへ遷移');
        header("Location:mypage.php"); //マイページへ
        exit();
      }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug(basename($_SERVER['PHP_SELF']).'処理終了 <<<<<');
?>
<!-- 編集フラグの値によって表示を変える -->
<?php
$siteTitle = (!$is_edit) ? 'カードを贈る' : 'カードを編集';
require('head.php'); 
?>

<body>
  <!-- ヘッダー -->
  <?php
    require('header.php'); 
  ?>

  <div id="contents" class="site-width">
    <h1 class="page-title"><i class="fas fa-paint-brush"></i><?php echo (!$is_edit) ? 'カードを贈る' : 'カードを編集する'; ?></h1>

    <section id="main" class="page-1column">

      <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data" class="form cardCreate">
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['to_user'])) echo 'err'; ?>">
            贈り先を選択<span class="label-required">必須</span><span class="area-msg"><?php echo getErrMsg('to_user'); ?></span>
            <select name="to_user">
              <option value="0" <?php if(getFormData('to_user') == 0 ){ echo 'selected'; } ?> >選択してください</option>
              <?php
                foreach($dbUsersList as $key => $val):
              ?>
              <option value="<?php echo $val['id']; ?>" <?php if(getFormData('to_user') == $val['id'] || $u_id == $val['id'] ){ echo 'selected'; } ?> >
                <!-- セレクトボックスに表示する値 -->
                <?php echo $val['username']; ?> さん
              </option>
              <?php endforeach; ?>
            </select>
          </label>
        
          <label class="<?php if(!empty($err_msg['card_id'])) echo 'err'; ?>">
            カードを選ぶ<span class="label-required">必須</span><span class="area-msg"><?php echo getErrMsg('card_id'); ?></span>
            <div class="area-msg">
              <?php echo getErrMsg('pic'); ?>
            </div>
            <div class="form-cardlist">
              <div class="card">
                <label class="area-drop card-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>" style="height:100px;">
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="hidden" name="temp_pic" value="<?php echo sanitize(getFormData('pic')); ?>">
                  <input type="file" name="pic" class="input-file" style="height:100px;">
                  <img src="<?php echo sanitize(getFormData('pic')); ?>" alt="アップロード画像" class="prev-img<?php if(empty(getFormData('pic'))) echo ' display-none' ?>">
                  ドラッグ＆ドロップ
                </label>
                好きな画像を使う
                <input type="radio" name="card_id" value="0" <?php if(getFormData('card_id') == 0 ){ echo 'checked'; } ?>>
              </div>
              <?php
              if(!empty($dbTemplate)):
                foreach($dbTemplate as $key => $val):
              ?>
              <div class="card">
                <img src="<?php echo showImg(sanitize($val['card_pic'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
                <?php echo sanitize($val['name']); ?>
                <input type="radio" name="card_id" value="<?php echo $val['id']; ?>" <?php if(getFormData('card_id') == $val['id'] ){ echo 'checked'; } ?>>
              </div>
              <?php
                endforeach;
              endif;
              ?>
            </div>
          </label>

          <label class="<?php if(!empty($err_msg['msg'])) echo 'err'; ?>">
            ひとことメッセージを書く<span class="label-required">必須</span><span class="area-msg"><?php echo getErrMsg('msg'); ?></span>
            <textarea name="msg" id="js-count" cols="30" rows="10"><?php echo getFormData('msg'); ?></textarea>
            <!-- 文字数カウント -->
            <p class="counter-text"><span id="js-count-view">0</span>/140文字</p>
          </label>

          
          <div class="btn-container">
            <input type="submit" class="btn-colored" value="<?php echo (!$is_edit) ? 'カードを贈る' : '変更する'; ?>">
          </div>
        </form>
      </div>

    </section>
  </div>

  <!-- フッター -->
  <?php
  require('footer.php'); 
  ?>