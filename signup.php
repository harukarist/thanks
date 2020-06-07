<?php
// 共通ファイル読込み
require('function.php');
// デバッグスタート
debugLogStart();

// post送信されていた場合
if (!empty($_POST)) {

  // ユーザー情報を代入
  $username = $_POST['username'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  
  // 未入力チェック
  validRequired($username, 'username');
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');
  
  if (empty($err_msg)) {
    // 名前の最大文字数チェック
    validMaxLen($username, 'username');

    // emailの形式チェック
    validEmail($email, 'email');
    // emailの最大文字数チェック
    validMaxLen($email, 'email');

    if (empty($err_msg)) {
      // emailの重複チェック
      validEmailDup($email);
    }

    // パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    // パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    // パスワードの最小文字数チェック
    validMinLen($pass, 'pass');

    if (empty($err_msg)) {
      // パスワードと再入力パスワードが一致するかチェック
      validMatch($pass, $pass_re, 'pass_re');

      if (empty($err_msg)) {
        //例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          // パスワードはpassword_hash()でハッシュ化（第二引数は基本的にPASSWORD_DEFAULT）
          $sql = 'INSERT INTO users (username,email,pass,logined_at,created_at) VALUES(:username,:email,:pass,:logined_at,:created_at)';
          $data = array(':username' => $username, ':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':logined_at' => date('Y-m-d H:i:s'), ':created_at' => date('Y-m-d H:i:s'));
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          // クエリ成功の場合
          if ($stmt) {
            //ログイン有効期限（デフォルトを1時間とする）
            $sesLimit = 60 * 60;
            // 最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            // ユーザーIDを格納
            // lastInsertId()でINSERT後のAUTO_INCREMENTの値を取得
            $_SESSION['user_id'] = $dbh->lastInsertId();

            debug('セッション変数の中身：' . print_r($_SESSION, true));
            $_SESSION['msg_success'] = SUC04;
            //マイページへ
            header("Location:mypage.php");
            // スクリプトの実行を終了
            exit();
          }
        } catch (Exception $e) {
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
?>

<?php
$siteTitle = 'ユーザー登録';
require('head.php');
?>

<body>
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <main id="main" class="p-hero">
    <h1 class="c-title__top"><i class="fas fa-user-plus"></i>ユーザー登録</h1>
    <section class="u-mb--xxl">
      <div class="c-form__area-msg">
        <?php
        if (!empty($err_msg['common'])) echo $err_msg['common'];
        ?>
      </div>

      <form action="" method="post" class="c-form c-form--thin p-hero__form">
        <label class="js-form-label<?php if (!empty($err_msg['username'])) echo ' is-error'; ?>">
          <div class="c-form__item-title--min">ユーザー名<span class="c-label__required">必須</span></div>
          <input type="text" name="username" id="js-valid-name" class="js-required" value="<?php if (!empty($_POST['username'])) echo $_POST['username']; ?>">
          <div class="c-form__area-msg js-area-msg">
            <?php
            if (!empty($err_msg['username'])) echo $err_msg['username'];
            ?>
          </div>
        </label>

        <label class="js-form-label<?php if (!empty($err_msg['email'])) echo ' is-error'; ?>">
          <div class="c-form__item-title--min">メールアドレス<span class="c-label__required">必須</span></div>
          <input type="email" name="email" id="js-valid-email" class="js-required" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>">
          <div class="c-form__area-msg js-area-msg">
            <?php
            if (!empty($err_msg['email'])) echo $err_msg['email'];
            ?>
          </div>
        </label>

        <label class="js-form-label<?php if (!empty($err_msg['pass'])) echo ' is-error'; ?>">
          <div class="c-form__item-title--min">パスワード<span class="c-label__required">必須</span><span class="c-form__notice">※英数字6文字以上</span></div>
          <input type="password" name="pass" id="js-valid-password" class="js-required" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>">
          <div class="c-form__area-msg js-area-msg">
            <?php
            if (!empty($err_msg['pass'])) echo $err_msg['pass'];
            ?>
          </div>
        </label>

        <label class="js-form-label<?php if (!empty($err_msg['pass_re'])) echo ' is-error'; ?>">
          <div class="c-form__item-title--min">パスワード（再入力）<span class="c-label__required">必須</span><span class="c-form__notice">※英数字6文字以上</span></div>
          <input type="password" name="pass_re" id="js-valid-password-re" class="js-required" value="<?php if (!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
          <div class="c-form__area-msg js-area-msg">
            <?php
            if (!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];
            ?>
          </div>
        </label>

        <div class="c-form__btn-container">
          <input type="submit" class="c-btn c-btn--large c-btn--colored js-disabled-btn" value="登録する">
        </div>
      </form>
    </section>
  </main>

  <!-- フッター -->
  <?php
  require('footer.php');
  ?>
