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
      if($stmt1 && $stmt2){
        // セッションを初期化
        $_SESSION = array();
        debug('セッション初期化：'.print_r($_SESSION,true));
        // クッキーを削除
        if (isset($_COOKIE["PHPSESSID"])) {
          setcookie("PHPSESSID", '', time() - 1800, '/');
        }
        // セッションを破棄
        session_destroy();
        debug('トップページへ遷移');
        header("Location:index.php");
        exit();
      }else{
        debug('クエリが失敗しました。');
        $err_msg['common'] = MSG07;
      }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
  debug(basename($_SERVER['PHP_SELF']).'画面表示処理終了 <<<<<<<<<<');
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

  <div id="contents">
    <main class="main page-2column">
      <h1 class="page-title">退会</h1>
      <section id="main">

        <div class="form-container">
          <form action="" method="post" class="form">
            <p class="center">本当に退会しますか？<br>
            退会すると、あなたが受け取ったカード、<br>贈ったカードは見ることができなくなります。</p>
            <div class="area-msg">
              <?php 
              if(!empty($err_msg['common'])) echo $err_msg['common'];
              ?>
            </div>
            <div class="btn-container">
              <input type="submit" class="btn-colored" value="退会する" name="submit">
            </div>
            <a href="mypage.php">&lt; マイページに戻る</a>
          </form>
          <p></p>
        </div>

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