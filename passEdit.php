<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

//-------------------------------------------------
// 画面処理
//-------------------------------------------------
// DBからユーザーデータを取得
$dbFormData = getUsersPass($_SESSION['user_id']);
debug('$dbFormData：' . print_r($dbFormData, true));

//post送信されていた場合
// ゲストユーザーの場合は変更不可
if (!empty($_POST) && $_SESSION['user_id'] === '6') {
  $err_msg['common'] = MSG17;
  // ゲストユーザー以外の場合は続行
} elseif (!empty($_POST) && $_SESSION['user_id'] !== '6') {
  debug('POST送信あり');
  debug('$_POST：' . print_r($_POST, true));

  //変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  //未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if (empty($err_msg)) {
    debug('パスワード変更 未入力チェックOK。');

    //古いパスワードの形式チェック
    validPass($pass_old, 'pass_old');
    //新しいパスワードの形式チェック
    validPass($pass_new, 'pass_new');
    debug('$pass_old:' . $pass_old);
    debug('$dbFormData:' . $dbFormData['pass']);
    //古いパスワードとDBパスワードを照合
    if (!password_verify($pass_old, $dbFormData['pass'])) {
      $err_msg['pass_old'] = MSG10;
    }

    //新しいパスワードと古いパスワードが同じかチェック
    if ($pass_old === $pass_new) {
      $err_msg['pass_new'] = MSG11;
    }
    //パスワードとパスワード再入力が合っているかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    if (empty($err_msg)) {
      debug('パスワード変更 バリデーションOK。');

      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        // パスワードはpassword_hash()でハッシュ化（第二引数は基本的にPASSWORD_DEFAULTを使う）
        $sql = 'UPDATE users SET pass = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if ($stmt) {
          // javascriptで成功メッセージを表示
          $_SESSION['msg_success'] = SUC01;

          //メールを送信
          $username = ($dbFormData['username']) ? $dbFormData['username'] : '名称未設定';
          $from = 'harukarist@gmail.com';
          $to = $dbFormData['email'];
          $subject = 'パスワード変更通知｜Thanks!';
          $comment = <<<EOT
{$username}　さん
パスワードが変更されました。
                      
-----------------------------------------------
Thanks! サポートセンター
URL  https://harukarist.sakura.ne.jp/thanks/
E-mail harukarist@gmail.com
-----------------------------------------------
EOT;
          // function.phpのsendMail関数でメール送信
          sendMail($from, $to, $subject, $comment);

          header("Location:mypage.php"); //マイページへ
          exit();
        }
      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}
?>
<?php
$siteTitle = 'パスワード変更';
require('head.php');
?>

<body>
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <div class="l-wrapper u-clearfix">
    <main id="main" class="l-main--two-column">
      <h1 class="c-title__top">パスワード変更</h1>
      <section class="c-form-container">
        <form action="" method="post" class="c-form c-form--thin">
          <div class="c-form__area-msg">
            <?php echo getErrMsg('common'); ?>
          </div>
          <label class="js-form-label<?php if (!empty($err_msg['pass_old'])) echo ' is-error'; ?>">
            <div class="c-form__item-title">古いパスワード<span class="c-label__required">必須</span></div>
            <input type="password" name="pass_old" id="js-valid-password-old" class="js-required" value="<?php if (!empty($_POST['pass_old'])) echo $_POST['pass_old']; ?>">
            <div class="c-form__area-msg js-area-msg">
              <?php echo getErrMsg('pass_old'); ?>
            </div>
          </label>
          <label class="js-form-label<?php if (!empty($err_msg['pass_new'])) echo ' is-error'; ?>">
            <div class="c-form__item-title">新しいパスワード<span class="c-label__required">必須</span><span class="c-form__notice">※英数字6文字以上</span></div>
            <input type="password" name="pass_new" id="js-valid-password" class="js-required" value="<?php if (!empty($_POST['pass_new'])) echo $_POST['pass_new']; ?>">
            <div class="c-form__area-msg js-area-msg">
              <?php echo getErrMsg('pass_new'); ?>
            </div>
          </label>
          <label class="js-form-label<?php if (!empty($err_msg['pass_new_re'])) echo ' is-error'; ?>">
            <div class="c-form__item-title">新しいパスワード（再入力）<span class="c-label__required">必須</span><span class="c-form__notice">※英数字6文字以上</span></div>
            <input type="password" name="pass_new_re" id="js-valid-password-re" value="<?php if (!empty($_POST['pass_new_re'])) echo $_POST['pass_new_re']; ?>">
            <div class="c-form__area-msg js-area-msg">
              <?php echo getErrMsg('pass_new_re'); ?>
            </div>
          </label>
          <div class="c-form__btn-container">
            <input type="submit" class="c-btn c-btn--large c-btn--colored js-disabled-btn" value="変更する">
          </div>
        </form>
      </section>
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
