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
  <p id="js-show-msg" style="display:none;" class="msg-slide">
  <!-- 引数をキーとするセッション変数の値を取得 -->
  <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <div id="contents">
    <!-- トップメッセージ -->
    <section class="hero">
      <h1>Thanks! へようこそ</h1>
      <h2>Thanks! はちょっとした「ありがとう」の気持ちを<br>
      カードにして贈り合えるサービスです</h2>
    </section>
  </div>

<!-- フッター -->
<?php
require('footer.php'); 
?>