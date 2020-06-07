<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

//-------------------------------------------------
// 画面処理
//-------------------------------------------------

// post送信されていた場合
if (!empty($_POST)) {
  debug('$_POST：' . print_r($_POST, true));
  debug('$_FILES：' . print_r($_FILES, true));

  //変数にPOST情報を代入
  $temp_pic = $_POST['temp_pic'];
  $card_name = $_POST['card_name'];

  //POST画像があればアップロードし、パスを格納
  if (!empty($_FILES['card_pic']['name'])) {
    $pic = uploadImg($_FILES['card_pic'], 'card_pic');
  } else {
    // 一時保存した画像のパスがあれば格納
    $pic = (!empty($temp_pic)) ? $temp_pic : '';
  }
  debug('$pic：' . $pic);
  // 未入力チェック
  validRequired($pic, 'card_pic');

  if (empty($err_msg)) {
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO cards (card_pic, card_name, user_id, created_at) VALUES (:pic, :card_name, :u_id, :created_at)';
      $data = array(':pic' => $pic, ':card_name' => $card_name, ':u_id' => $_SESSION['user_id'], ':created_at' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        // トグル出力
        $_SESSION['msg_success'] = SUC09;
        header("Location:templateEdit.php");
        exit();
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
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
  <p id="js-show-msg" class="c-msg-slide">
    <!-- 引数をキーとするセッション変数の値を取得 -->
    <?php echo getSessionFlash('msg_success'); ?>
  </p>
  <main id="main" class="l-main--one-column">
    <h1 class="c-title__top">カードテンプレート登録</h1>
    <section class="c-form-container">
      <form action="" method="post" class="c-form c-form--thin" enctype="multipart/form-data">
        <div class="c-form__area-msg">
          <?php echo getErrMsg('common'); ?>
        </div>
        <div class="c-form__item-title">カード名</div>
        <label class="<?php if (!empty($err_msg['card_name'])) echo 'is-error'; ?>">
          <input type="text" name="card_name" value="<?php echo getFormData('card_name'); ?>">
        </label>
        <div class="c-form__area-msg">
          <?php echo getErrMsg('card_name'); ?>
        </div>
        <div class="c-form__item-title">カード画像<span class="c-label__required">必須</span>
          <span class="c-form__area-msg"><?php echo getErrMsg('card_pic'); ?></span></div>
        <label class="c-area-drop c-area-drop--large js-drop-area <?php if (!empty($err_msg['card_pic'])) echo 'is-error'; ?>">
          <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
          <input type="hidden" name="temp_pic" value="<?php echo sanitize(getFormData('pic')); ?>">
          <input type="file" name="card_pic" class="c-input-file js-file-input">
          <img src="<?php echo sanitize(getFormData('pic')); ?>" alt="画像の登録" class="c-prev-img js-prev-img <?php if (empty(getFormData('pic'))) echo ' u-display-none' ?>">
        </label>
        <div class="c-form__btn-container">
          <input type="submit" class="c-btn c-btn-large c-btn--colored" value="登録する">
        </div>
      </form>
    </section>
  </main>

  <!-- フッター -->
  <?php
  require('footer.php');
  ?>
