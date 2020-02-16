<?php
  //共通ファイル読込み・デバッグスタート
  require('function.php');
  debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$dbFormData = getUsersProf($_SESSION['user_id']);
debug('$dbFormData：'.print_r($dbFormData,true));

// DBからグループデータを取得
$dbGroups = getGroups();
// debug('$dbGroups：'.print_r($dbGroups,true));

// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信あり');
  debug('$_POST：'.print_r($_POST,true));
  debug('$_FILES：'.print_r($_FILES,true));

  //変数にPOST情報を代入
  $username = $_POST['username'];
  $email = $_POST['email'];
  $group = $_POST['group_id'];
  $comment = $_POST['comment'];
  $temp_pic = $_POST['temp_pic'];
  debug('★$temp_pic：'.$temp_pic);

  // 画像削除チェックがあればパスを削除
  if(!empty($_POST['pic_delete'])){
    $pic = '';
  }else{
    if(!empty($_FILES['pic']['name']) ) {
      //POST画像を一時アップロードし、パスを格納
      $pic = uploadImg($_FILES['pic'],'pic') ;
    }else{
      // 一時保存した画像のパスがあれば格納
      $pic = (!empty($temp_pic) ) ? $temp_pic : '';
      // POST画像がなくDBに登録がある場合はDBのパスを格納
      $pic = ( empty($pic) && !empty($dbFormData['pic']) ) ? $dbFormData['pic'] : $pic;
    }
  }
  debug('★$pic：'.$pic);
  //DBの情報と入力情報が異なる場合はバリデーション
  if($dbFormData['username'] !== $username){
    //未入力チェック
    validRequired($username, 'username');
    //名前の最大文字数チェック
    validMaxLen($username, 'username', 25);
  }
  if($dbFormData['group_id'] !== $group){
    //セレクトボックスチェック
    validSelect($group, 'group_id');
  }
  if($dbFormData['comment'] !== $comment){
    //最大文字数チェック
    validMaxLen($comment, 'comment', 25);
  }

  if($dbFormData['email'] !== $email){
    //実運用時は認証キーをメール送信して認証を行う
    //emailの未入力チェック
    validRequired($email, 'email');
    if(empty($err_msg['email'])){
      //emailの最大文字数チェック
      validMaxLen($email, 'email');
      //emailの形式チェック
      validEmail($email, 'email');
      if(empty($err_msg['email'])){
        //emailの重複チェック
        validEmailDup($email);
      }
    }
  }

  if(empty($err_msg)){
    debug('バリデーションOK');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET username = :u_name, email = :email, group_id = :group_id, comment = :comment, pic = :pic WHERE id = :u_id';
      $data = array(':u_name' => $username , ':email' => $email, ':group_id' => $group, ':comment' => $comment, ':pic' => $pic, ':u_id' => $dbFormData['id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if($stmt){
        // トグル出力
        $_SESSION['msg_success'] = SUC02;
        debug('マイページへ遷移');
        header("Location:mypage.php");
        exit();
      }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug(basename($_SERVER['PHP_SELF']).'画面表示処理終了 <<<<<<<<<<');
?>
<?php
$siteTitle = 'プロフィール編集';
require('head.php'); 
?>
<body>
  <!-- ヘッダー -->
  <?php
    require('header.php'); 
  ?>

  <div id="contents">
    <main class="main page-2column">
      <h1 class="page-title">プロフィール編集</h1>

        <div class="form-container">
          <form action="" method="post" class="form" enctype="multipart/form-data">
            <div class="area-msg">
            <?php echo getErrMsg('common'); ?>
            </div>

            お名前<span class="label-required">必須</span>
            <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
           </label>
           <div class="area-msg">
            <?php echo getErrMsg('username'); ?>
            </div>

            Email<span class="label-required">必須</span>
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg">
            <?php echo getErrMsg('email'); ?>
            </div>

            部署
            <label class="<?php if(!empty($err_msg['group_id'])) echo 'err'; ?>">
              <select name="group_id" id="">
                <option value="0" <?php if(getFormData('group_id') == 0 ){ echo 'selected'; } ?> >選択してください</option>
                <?php
                  foreach($dbGroups as $key => $val){
                ?>
                <option value="<?php echo $val['id']; ?>" <?php if(getFormData('group_id') == $val['id'] ){ echo 'selected'; } ?> >
                  <?php echo $val['group_name']; ?>
                </option>
                <?php
                  }
                ?>
              </select>
            </label>
            <div class="area-msg">
            <?php echo getErrMsg('group_id'); ?>
            </div>

            ひとこと
            <label class="<?php if(!empty($err_msg['comment'])) echo 'err'; ?>">
              <input type="text" name="comment" id="js-count" value="<?php echo getFormData('comment'); ?>">
            </label>
            <!-- 文字数カウント -->
            <p class="counter-text"><span id="js-count-view">0</span>/25文字</p>
            <div class="area-msg">
              <?php echo getErrMsg('comment'); ?>
            </div>

          プロフィール画像
          <label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>" style="height:350px;line-height:350px;">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            <input type="hidden" name="temp_pic" value="<?php echo sanitize(getFormData('pic')); ?>">
            <input type="file" name="pic" class="input-file" style="height:350px;">
            <img src="<?php echo sanitize(getFormData('pic')); ?>" alt="プロフィール画像" class="prev-img<?php if(empty(getFormData('pic'))) echo ' display-none' ?>">
              ドラッグ＆ドロップ
          </label>
          <input type="checkbox" name="pic_delete" value="on">画像を削除する
          <div class="area-msg">
            <?php echo getErrMsg('pic'); ?>
            </div>
            <div class="btn-container">
              <input type="submit" class="btn-colored" value="変更する">
            </div>
          </form>
        </div>

    </main>

    <!-- サイドバー -->
    <?php
      require('sidebar.php');
    ?>

  </div>

<!-- フッター -->
<?php
require('footer.php'); 
?>