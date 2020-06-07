<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();
?>
<!-- head部 -->
<?php
$siteTitle = 'Welcome';
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

  <main id="main">
    <!-- トップメッセージ -->
    <section class="p-hero">
      <h1 class="p-hero__heading">Thanks! へようこそ</h1>
      <h2 class="p-hero__message">Thanks! はちょっとした「ありがとう」の気持ちを<br>
        カードにして贈り合えるサービスです</h2>

      <a href="login.php" class="c-btn c-btn--colored p-hero__btn">
        <i class="fas fa-sign-in-alt"></i>ログイン
      </a>
      <a href="signup.php" class="c-btn c-btn--colored p-hero__btn">
        <i class="fas fa-user-plus"></i>ユーザー登録
      </a>
      <a href="guest.php?u_id=6" class="c-btn c-btn--border  p-hero__btn">
        ゲストユーザーとしてログイン<br>
        <span class="u-font-size--s">ユーザー登録なしでご利用いただけます（パスワード変更・退会機能を除く）</span>
      </a>
    </section>
  </main>

  <!-- フッター -->
  <?php
  require('footer.php');
  ?>
