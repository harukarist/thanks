<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

// GETデータを格納
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';
debug('GET：' . print_r($u_id, true));

if ($u_id === '6') {
  $email = 'guest@example.com';
  $pass = 'example';
  $pass_save = false;

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

    debug('クエリ結果の中身：' . print_r($result, true));

    // パスワード照合
    // password_verifyでハッシュ化したパスワードと比較
    // array_shiftで配列の1つ目（パスワード）を取得
    if (!empty($result) && password_verify($pass, array_shift($result))) {
      debug('パスワードがマッチしました。');

      //ログイン有効期限（デフォルトを1時間とする）
      $sesLimit = 60 * 60;
      // 最終ログイン日時を現在日時に
      $_SESSION['login_date'] = time();
      $_SESSION['login_limit'] = $sesLimit;
      // ユーザーIDを格納
      $_SESSION['user_id'] = $result['id'];
      // 管理者フラグを格納
      $_SESSION['is_admin'] = $result['is_admin'];

      debug('セッション変数の中身：' . print_r($_SESSION, true));
      debug('マイページへ遷移');
      $_SESSION['msg_success'] = SUC08;
      header("Location:mypage.php"); //マイページへ
      exit();
    } else {
      debug('パスワードがアンマッチです。');
      $err_msg['common'] = MSG09;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
