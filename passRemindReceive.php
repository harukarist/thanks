<?php
  //共通ファイル読込み・デバッグスタート
  require('function.php');
  debugLogStart();
//SESSIONに認証キーがあるか確認、なければ認証キー送信ページへリダイレクト
if(empty($_SESSION['auth_key'])){
  header("Location:passRemindSend.php"); //認証キー送信ページへ
  exit();
}

//================================
// 画面処理
//================================
//post送信されていた場合
if(!empty($_POST)){
 debug('POST送信あり');
 debug('POST情報：'.print_r($_POST,true));
 
 //変数に認証キーを代入
 $auth_key = $_POST['token'];

 //未入力チェック
 validRequired($auth_key, 'token');

 if(empty($err_msg)){
   debug('未入力チェックOK。');
   
   //固定長チェック
   validLength($auth_key, 'token');
   //半角チェック
   validHalf($auth_key, 'token');

   if(empty($err_msg)){
     debug('バリデーションOK。');
     //認証キーのバリデーション
     if($auth_key !== $_SESSION['auth_key']){
       $err_msg['common'] = MSG13;
     }
     if(time() > $_SESSION['auth_key_limit']){
       $err_msg['common'] = MSG14;
     }
     
     if(empty($err_msg)){
       debug('認証OK。');
       
       $pass = makeRandKey(); //パスワード生成
       debug('★パスワード'.$pass);
       
       //例外処理
       try {
         // DBへ接続
         $dbh = dbConnect();
         // SQL文作成
         // パスワードはpassword_hash()でハッシュ化（第二引数は基本的にPASSWORD_DEFAULTを使う）
         $sql = 'UPDATE users SET pass = :pass WHERE email = :email AND is_deleted = 0';
         $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
         // クエリ実行
         $stmt = queryPost($dbh, $sql, $data);

         // クエリ成功の場合
         if($stmt){
           debug('クエリ成功。');

           //メールを送信
           $from = 'xxx@gmail.com';
           $to = $_SESSION['auth_email'];
           $subject = '【パスワード再発行完了】｜Thanks!';
           $comment = <<<EOT
パスワードを再発行いたしました。
下記のログインページにて再発行パスワードをご入力頂き、
ログインしてください。
ログイン後、パスワードのご変更をお願い致します

ログインページ：http://localhost/thanks/login.php
再発行パスワード：{$pass}

-----------------------------------
Thanks! サポートセンター
URL  http://xxxxx.xxx/
E-mail info@xxxxx.xxx
-----------------------------------
EOT;
           sendMail($from, $to, $subject, $comment);

           //セッション削除
           session_unset();
           $_SESSION['msg_success'] = SUC03;
           debug('セッション変数の中身：'.print_r($_SESSION,true));

           header("Location:login.php"); //ログインページへ
           exit();
         }else{
           debug('クエリに失敗しました。');
           $err_msg['common'] = MSG07;
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
$siteTitle = 'パスワード再発行認証';
require('head.php'); 
?>
<body>
  <?php
    require('header.php'); 
  ?>
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <!-- 引数をキーとするセッション変数の値を取得 -->
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <div id="contents" class="site-width">

  <section id="main" class="page-1column">
    <h2 class="page-title">パスワード再発行認証</h2>

      <div class="form-container">

      <form action="" method="post" class="form">
          <p>ご指定のメールアドレスにお送りした【パスワード再発行認証メール】内にある「認証キー」をご入力ください。</p>
          <div class="area-msg">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['token'])) echo 'err'; ?>">
            認証キー
            <input type="text" name="token" value="<?php echo getFormData('token'); ?>">
          </label>
          <div class="area-msg">
            <?php if(!empty($err_msg['token'])) echo $err_msg['token']; ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn-colored" value="再発行する">
          </div>
        </form>
      </div>
      <a href="passRemindSend.php">&lt; パスワード再発行メールを再度送信する</a>
    </section>

  </div>

<!-- フッター -->
<?php
require('footer.php'); 
?>