<?php
  //共通ファイル読込み・デバッグスタート
  require('function.php');
  debugLogStart();

//================================
// 画面処理
//================================
//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信あり');
  debug('POST情報：'.print_r($_POST,true));
  
  //変数にPOST情報代入
  $email = $_POST['email'];

  //未入力チェック
  validRequired($email, 'email');

  if(empty($err_msg)){
    debug('未入力チェックOK。');
    
    //emailの形式チェック
    validEmail($email, 'email');
    //emailの最大文字数チェック
    validMaxLen($email, 'email');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成（入力されたEmailと一致し、かつ退会していないものがあるか）
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND is_deleted = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を連想配列形式で取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // EmailがDBに登録されている場合
        // array_shiftで配列の1つ目を取得
        if($stmt && array_shift($result)){
          debug('クエリ成功。DB登録あり。');
          $_SESSION['msg_success'] = SUC03;
          //function.phpのmakeRandKey関数で認証キー生成
          $auth_key = makeRandKey();
          debug('authキー'.$auth_key);
          
          //メールを送信
          $from = 'xxx@gmail.com';
          $to = $email;
          $subject = '【パスワード再発行認証】｜Thanks!';
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost/thanks/passRemindReceive.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーが無効となった場合は
再度下記ページより再発行をお願い致します。
http://localhost/thanks/passRemindSend.php

-----------------------------------
Thanks! サポートセンター
URL  http://xxxxx.xxx/
E-mail info@xxxxx.xxx
-----------------------------------
EOT;
          //function.phpのsendMail関数でメール送信
          sendMail($from, $to, $subject, $comment);
          
          //認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          //現在時刻+(60秒*30分)を有効期限に
          $_SESSION['auth_key_limit'] = time()+(60*30);
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          //認証キー入力ページへ
          header("Location:passRemindReceive.php");
          exit();
        }else{
          debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
          $err_msg['common'] = MSG07;
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
$siteTitle = 'パスワード再発行メール送信';
require('head.php'); 
?>

<body>
  <!-- ヘッダー -->
  <?php
    require('header.php'); 
  ?>

  <div id="contents" class="site-width">

    <section id="main" class="page-1column">
    <h2 class="page-title">パスワード再発行メール送信</h2>

      <div class="form-container">
        <form action="" method="post" class="form">
          <p class="center">ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送りします。</p>
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg">
              <?php 
              if(!empty($err_msg['email'])) echo $err_msg['email'];
              ?>
            </div>
            <div class="btn-container">
              <input type="submit" class="btn-colored" value="送信する">
            </div>
          </form>
        </div>
      <a href="index.html">&lt; TOPに戻る</a>
    </section>

  </div>

<!-- フッター -->
<?php
require('footer.php'); 
?>