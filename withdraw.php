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
if (!empty($_POST) && $_SESSION['user_id'] === '6') {
  $err_msg['common'] = MSG17;
} elseif (!empty($_POST) && $_SESSION['user_id'] !== '6') {
  debug('POST送信あり');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成（削除フラグを1に変更）
    $sql1 = 'UPDATE users SET is_deleted = 1 WHERE id = :u_id';
    $sql2 = 'UPDATE favorites SET is_deleted = 1 WHERE user_id = :u_id';
    // データ流し込み
    $data = array(':u_id' => $_SESSION['user_id']);
    // クエリ実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    // クエリ実行成功の場合
    if ($stmt1 && $stmt2) {
      // セッションを初期化
      $_SESSION = array();
      debug('セッション初期化：' . print_r($_SESSION, true));
      // クッキーを削除
      if (isset($_COOKIE["PHPSESSID"])) {
        setcookie("PHPSESSID", '', time() - 1800, '/');
      }
      // セッションを破棄
      session_destroy();
      debug('トップページへ遷移');
      header("Location:index.php");
      exit();
    } else {
      debug('クエリが失敗しました。');
      $err_msg['common'] = MSG07;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
?>
<?php
$siteTitle = '退会';
require('head.php');
?>

<body>
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <div class="l-wrapper u-clearfix">
    <main id="main" class="l-main--two-column">
      <h1 class="c-title__top">退会</h1>
      <section class="c-form-container">
        <form action="" method="post" class="c-form c-form--thin">
          <p class="c-form__guide">本当に退会しますか？<br>
            退会すると、あなたが受け取ったカード、<br>
            贈ったカードは見ることができなくなります。</p>
          <div class="c-form__area-msg">
            <p class="c-text--center">
              <?php
              if (!empty($err_msg['common'])) echo $err_msg['common'];
              ?>
            </p>
          </div>
          <div class="c-form__btn-container">
            <input type="submit" class="c-btn c-btn--colored" value="退会する" name="submit">
          </div>
          <a href="mypage.php" class="c-text--center"><i class="fas fa-chevron-left c-icon-back"></i>マイページに戻る</a>
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
