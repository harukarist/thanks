<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//-------------------------------------------------
// Ajax処理
//-------------------------------------------------

// postがあり、ユーザーIDがあり、ログインしている場合(issetは0も「入っている」とみなす)
// function.phpのisLogin関数でログインしていることをチェック
if(isset($_POST['messageId']) && isset($_SESSION['user_id']) && isLogin()){
  debug('POST送信あり');
  $m_id = $_POST['messageId'];
  debug('メッセージID：'.$m_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // レコードがあるか検索
    $sql = 'SELECT * FROM favorites WHERE message_id = :m_id AND user_id = :u_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':m_id' => $m_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // rowCount()でレコード数を取得
    $resultCount = $stmt->rowCount();
    debug('お気に入り件数：'.$resultCount);
    // レコードが1件でもある場合（お気に入り登録されている場合）
    if(!empty($resultCount)){
      // レコードを削除する
      $sql = 'DELETE FROM favorites WHERE message_id = :m_id AND user_id = :u_id';
      $data = array(':u_id' => $_SESSION['user_id'], ':m_id' => $m_id);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
    }else{
      // レコードがない場合はお気に入り登録する
      $sql = 'INSERT INTO favorites (message_id, user_id, created_at) VALUES (:m_id, :u_id, :created_at)';
      $data = array(':u_id' => $_SESSION['user_id'], ':m_id' => $m_id, ':created_at' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
debug('Ajax処理終了 -------------');
