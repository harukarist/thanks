<?php
  // 共通ファイル読込み
  require('function.php');
  // デバッグスタート
  debugLogStart();

  // post送信されていた場合
  if(!empty($_POST)){

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

    if(empty($err_msg)){
      
      // 名前の最大文字数チェック
      validMaxLen($username, 'username');

      // emailの形式チェック
      validEmail($email, 'email');
      // emailの最大文字数チェック
      validMaxLen($email, 'email');

      if(empty($err_msg)){
        // emailの重複チェック
        validEmailDup($email);
      }

      // パスワードの半角英数字チェック
      validHalf($pass, 'pass');
      // パスワードの最大文字数チェック
      validMaxLen($pass, 'pass');
      // パスワードの最小文字数チェック
      validMinLen($pass, 'pass');

      if(empty($err_msg)){
        // パスワードと再入力パスワードが一致するかチェック
        validMatch($pass, $pass_re, 'pass_re');

        if(empty($err_msg)){
          //例外処理
          try {
            // DBへ接続
            $dbh = dbConnect();
            // SQL文作成
            // パスワードはpassword_hash()でハッシュ化（第二引数は基本的にPASSWORD_DEFAULT）
            $sql = 'INSERT INTO users (username,email,pass,logined_at,created_at) VALUES(:username,:email,:pass,:logined_at,:created_at)';
            $data = array(':username' => $username, ':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT),':logined_at' => date('Y-m-d H:i:s'),':created_at' => date('Y-m-d H:i:s'));
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);

            // クエリ成功の場合
            if($stmt){
              //ログイン有効期限（デフォルトを1時間とする）
              $sesLimit = 60*60;
              // 最終ログイン日時を現在日時に
              $_SESSION['login_date'] = time();
              $_SESSION['login_limit'] = $sesLimit;
              // ユーザーIDを格納
              // lastInsertId()でINSERT後のAUTO_INCREMENTの値を取得
              $_SESSION['user_id'] = $dbh->lastInsertId();

              debug('セッション変数の中身：'.print_r($_SESSION,true));
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
  <div id="contents" class="site-width">
    <section id="main" class="page-1column">
    <h1 class="page-title"><i class="fas fa-user-plus"></i>ユーザー登録</h1>

      <div class="form-container">
        <form action="" method="post" class="form">
          
          <!-- エラーメッセージ出力 -->
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            ユーザー名
             <input type="text" name="username" value="<?php if(!empty($_POST['username'])) echo $_POST['username']; ?>">
           </label>
           <div class="area-msg">
            <?php 
            if(!empty($err_msg['username'])) echo $err_msg['username'];
            ?>
          </div>

          <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
           メールアドレス
           <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
          </label>
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['email'])) echo $err_msg['email'];
            ?>
          </div>

          <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
            パスワード<span class="notice">※英数字6文字以上</span>
            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
          </label>
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['pass'])) echo $err_msg['pass'];
            ?>
          </div>

          <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
            パスワード（再入力）<span class="notice">※英数字6文字以上</span>
            <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
          </label>
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];
            ?>
          </div>

           <div class="btn-container">
             <input type="submit" class="btn-colored" value="登録する">
           </div>
        </form>
      </div>

    </section>
  </div>

<!-- フッター -->
<?php
require('footer.php'); 
?>