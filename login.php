<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// ログイン処理
//================================
// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信あり');

  $email = $_POST['email'];
  $pass = $_POST['pass'];
  //次回ログイン省略チェック
  $pass_save = (!empty($_POST['pass_save'])) ? true : false; 

  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

  if(empty($err_msg)){

    //emailの形式チェック
    validEmail($email, 'email');
    //emailの最大文字数チェック
    validMaxLen($email, 'email');

    //パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    //パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    //パスワードの最小文字数チェック
    validMinLen($pass, 'pass');
    


    if(empty($err_msg)){
      debug('バリデーションOK');
      
      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT pass, id, is_admin FROM users WHERE email = :email AND is_deleted = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を連想配列形式で取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        debug('クエリ結果の中身：'.print_r($result,true));
        
        // パスワード照合
        // password_verifyでハッシュ化したパスワードと比較
        // array_shiftで配列の1つ目（パスワード）を取得
        if(!empty($result) && password_verify($pass, array_shift($result))){
          debug('パスワードがマッチしました。');
          
          //ログイン有効期限（デフォルトを1時間とする）
          $sesLimit = 60*60;
          // 最終ログイン日時を現在日時に
          $_SESSION['login_date'] = time();
          
          // ログイン保持にチェックがある場合
          if($pass_save){
            debug('ログイン保持にチェックがあります。');
            // ログイン有効期限を30日にセット
            $_SESSION['login_limit'] = $sesLimit * 24 * 30;
          }else{
            debug('ログイン保持にチェックはありません。');
            // ログイン有効期限を1時間後（デフォルト）にセット
            $_SESSION['login_limit'] = $sesLimit;
          }
          // ユーザーIDを格納
          $_SESSION['user_id'] = $result['id'];
          // 管理者フラグを格納
          $_SESSION['is_admin'] = $result['is_admin'];
          
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          debug('マイページへ遷移');
          header("Location:mypage.php"); //マイページへ
          exit();
        }else{
          debug('パスワードがアンマッチです。');
          $err_msg['common'] = MSG09;
        }

      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}
debug(basename($_SERVER['PHP_SELF']).'画面表示処理終了 <<<<<<<<<<');
?>
<?php
$siteTitle = 'ログイン';
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

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <section id="main" class="page-1column">
    <h1 class="page-title"><i class="fas fa-sign-in-alt"></i>ログイン</h1>

      <div class="form-container">
        <form action="" method="post" class="form">
          
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['common'])) echo $err_msg['common'];
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
            パスワード
            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
          </label>
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['pass'])) echo $err_msg['pass'];
            ?>
          </div>
          <label>
            <input type="checkbox" name="pass_save">次回ログインを省略する
          </label>
          <div class="btn-container">
            <input type="submit" class="btn_colored" value="ログイン">
          </div>
          パスワードを忘れた方は<a href="passRemindSend.php">こちら</a>
        </form>
      </div>

    </section>
  </div>

  <!-- フッター -->
  <?php
  require('footer.php'); 
  ?>