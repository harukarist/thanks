<?php
  //共通ファイル読込み・デバッグスタート
  require('function.php');
  debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信あり');
  debug('$_POST：'.print_r($_POST,true));
  debug('$_FILES：'.print_r($_FILES,true));

  //変数にPOST情報を代入
  $temp_pic = $_POST['temp_pic'];
  $name = $_POST['name'];
  debug('★$temp_pic：'.$temp_pic);

  if(!empty($_FILES['card_pic']['name']) ) {
    //POST画像を一時アップロードし、パスを格納
    $pic = uploadImg($_FILES['card_pic'],'card_pic') ;
  }else{
    // 一時保存した画像のパスがあれば格納
    $pic = (!empty($temp_pic) ) ? $temp_pic : '';
  }
  debug('★$pic：'.$pic);

  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'INSERT INTO cards (card_pic, name, user_id, created_at) VALUES (:pic, :name, :u_id,:created_at)';
    $data = array(':pic' => $pic, ':name' => $name, ':u_id' => $_SESSION['user_id'], ':created_at' => date('Y-m-d H:i:s'));
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ成功の場合
    if($stmt){
      // トグル出力
      $_SESSION['msg_success'] = SUC05;
      header("Location:templeteEdit.php");
      exit();
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

debug(basename($_SERVER['PHP_SELF']).'画面表示処理終了 <<<<<<<<<<');
?>
<?php
$siteTitle = 'カードテンプレート登録';
require('head.php'); 
?>
<body>
  <!-- ヘッダー -->
  <?php
    require('header.php'); 
  ?>
  <!-- スライドトグル -->
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <!-- 引数をキーとするセッション変数の値を取得 -->
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
<div id="contents" class="site-width">
      <h1 class="page-title">カードテンプレート登録</h1>
      <section id="main" class="page-1column">

        <div class="form-container">
          <form action="" method="post" class="form" enctype="multipart/form-data">
            <div class="area-msg">
            <?php echo getErrMsg('common'); ?>
            </div>
            カード名
            <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
            <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
           </label>
           <div class="area-msg">
            <?php echo getErrMsg('name'); ?>
            </div>
            カード画像
            <label class="area-drop <?php if(!empty($err_msg['card_pic'])) echo 'err'; ?>" style="height:350px;line-height:350px;">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="hidden" name="temp_pic" value="<?php echo getFormData('card_pic'); ?>">
              <input type="file" name="card_pic" class="input-file" style="height:350px;">
              <img src="<?php echo getFormData('card_pic'); ?>" alt="画像の登録" class="prev-img<?php if(empty(getFormData('pic'))) echo ' display-none' ?>">
                ドラッグ＆ドロップ
            </label>
            <div class="area-msg">
            <?php echo getErrMsg('card_pic'); ?>
            </div>
            <div class="btn-container">
              <input type="submit" class="btn-colored" value="登録する">
            </div>
          </form>
        </div>
  </div>

<!-- フッター -->
<?php
require('footer.php'); 
?>