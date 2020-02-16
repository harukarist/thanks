<?php
  //共通ファイル読込み・デバッグスタート
  require('function.php');
  debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
// DBからユーザーデータを取得
$dbFormData = getUsersPass($_SESSION['user_id']);
debug('$dbFormData：'.print_r($dbFormData,true));

//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信あり');
  debug('$_POST：'.print_r($_POST,true));
  
  //変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  //未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if(empty($err_msg)){
    debug('パスワード変更 未入力チェックOK。');
    
    //古いパスワードの形式チェック
    validPass($pass_old, 'pass_old');
    //新しいパスワードの形式チェック
    validPass($pass_new, 'pass_new');
    
    //古いパスワードとDBパスワードを照合
    if(!password_verify($pass_old, $dbFormData['pass'])){
      $err_msg['pass_old'] = MSG10;
    }
    
    //新しいパスワードと古いパスワードが同じかチェック
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = MSG11;
    }
    //パスワードとパスワード再入力が合っているかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');
    
    if(empty($err_msg)){
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
        if($stmt){
          // javascriptで成功メッセージを表示
          $_SESSION['msg_success'] = SUC01;
          
          //メールを送信
          $username = ($dbFormData['username']) ? $dbFormData['username'] : 'お名前未登録';
          $from = 'xxx@gmail.com';
          $to = $dbFormData['email'];
          $subject = 'パスワード変更通知｜Thanks!';
          $comment = <<<EOT
{$username}　さん
パスワードが変更されました。
                      
-----------------------------------
Thanks! サポートセンター
URL  http://xxxxx.xxx/
E-mail info@xxxxx.xxx
-----------------------------------
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

  <div id="contents">
    <main class="main page-2column">
      <h1 class="page-title">パスワード変更</h1>
        <div class="form-container">
          <form action="" method="post" class="form">
            <div class="area-msg">
              <?php echo getErrMsg('common'); ?>
            </div>
              <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
                古いパスワード
                <input type="password" name="pass_old" value="<?php if(!empty($_POST['pass_old'])) echo $_POST['pass_old'];?>">
                
              </label>
              <div class="area-msg">
              <?php echo getErrMsg('pass_old'); ?>
            </div>
              <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
                新しいパスワード<span class="notice">※英数字6文字以上</span>
                <input type="password" name="pass_new" value="<?php if(!empty($_POST['pass_new'])) echo $_POST['pass_new'];?>">
              </label>
              <div class="area-msg">
              <?php echo getErrMsg('pass_new'); ?>
            </div>
              <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
                新しいパスワード（再入力）<span class="notice">※英数字6文字以上</span>
                <input type="password" name="pass_new_re" value="<?php if(!empty($_POST['pass_new_re'])) echo $_POST['pass_new_re'];?>">
              </label>
              <div class="area-msg">
              <?php echo getErrMsg('pass_new_re'); ?>
            </div>
              <div class="btn-container">
                <input type="submit" class="btn-colored" value="変更する">
              </div>
            </form>
        </div>
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