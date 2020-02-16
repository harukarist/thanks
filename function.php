<?php
//================================
// ログ
//================================
// ini_set()で設定オプションの値を変更
// ログ出力の有無
ini_set('log_errors','on');
// ログ出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
// デバッグフラグ(trueの時のみエラーログを出力)
$debug_flg = true;
// デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    // error_log()でエラーログを出力
    error_log('デバッグ：'.$str);
  }
}

//================================
// セッション準備・セッション有効期限の延長
//================================
// セッションファイルのパスを変更（/var/tmp/以下に置くと30日は削除されない）
// session_save_path("/var/tmp/");
session_save_path('C:\xampp\var\tmp');
// ini_set('session.save_path','C:\xampp\var\tmp');
// セッションの有効期限を設定（30日以上経過したものだけガベージコレクションが100分の1の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
// クッキー自体の有効期限を延ばす（ブラウザを閉じても削除されない）
ini_set('session.cookie_lifetime ', 60*60*24*30);
// セッションスタート
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすまし対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
  debug(basename($_SERVER['PHP_SELF']).'処理開始 >>>>>>>>>>');
  debug('セッションID：'.session_id());
  debug('セッション変数：'.print_r($_SESSION,true));
  debug('現在日時：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug( 'ログイン期限日時：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
  }
}

//================================
// 定数
//================================
// エラーメッセージ
define('MSG01','入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03','パスワード（再入力）が合っていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05','文字以上で入力してください');
define('MSG06','文字以内で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '古いパスワードが違います');
define('MSG11', '古いパスワードと同じです');
define('MSG12', '文字で入力してください');
define('MSG13', '正しくありません');
define('MSG14', '有効期限が切れています');
define('MSG15', '半角数字のみご利用いただけます');
// 成功メッセージ
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', 'カードを贈りました！');
define('SUC05', 'カードテンプレートを登録しました');

//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================

// 未入力チェック
function validRequired($str, $key){
  if(empty($str)){
  // if($str === ''){
  // if($str == ''){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
// Email形式チェック
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
// Email重複チェック
function validEmailDup($email){
  global $err_msg;
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文（入力したEmailと一致し、かつ退会していないデータの個数を取得）
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND is_deleted = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を連想配列形式で取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // array_shift関数で配列形式のクエリ結果から1つ目だけを取り出して判定
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
// 同値チェック
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
// 最小文字数チェック
function validMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = $min.MSG05;
  }
}
// 最大文字数チェック
function validMaxLen($str, $key, $max = 256){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = $max.MSG06;
  }
}
// 半角チェック
function validHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}

// 半角数字チェック
function validNumber($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}
// 固定長チェック
function validLength($str, $key, $len = 8){
  if( mb_strlen($str) !== $len ){
    global $err_msg;
    $err_msg[$key] = $len . MSG12;
  }
}
// パスワードチェック
function validPass($str, $key){
  // 半角英数字チェック
  validHalf($str, $key);
  // 最大文字数チェック
  validMaxLen($str, $key);
  // 最小文字数チェック
  validMinLen($str, $key);
}
// selectboxチェック
function validSelect($str, $key){
  debug('★$str'.$str);
  debug('★$key'.$key);
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG13;
  }
}
// エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}

//================================
// ログイン認証 
//================================
// ※ajaxLikeで使用（auth.phpと違い、ページ遷移は行わない）

function isLogin(){
  // ログインしている場合
  if( !empty($_SESSION['login_date']) ){
    // debug('ログイン済みユーザー');
    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      // debug('ログイン有効期限オーバー');
      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    }else{
      // debug('ログイン有効期限以内');
      return true;
    }
  }else{
    debug('未ログインユーザー');
    return false;
  }
}

//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
  //DBへの接続準備
  $dsn = 'mysql:dbname=thankscard;host=localhost;charset=utf8';
  $user = 'xxxx';
  $password = 'xxxx';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

//SQL実行関数
function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行（失敗した場合はデバッグ）
  // $stmt->execute($data);
  if(!$stmt->execute($data)){
    debug('queryPost()クエリ失敗：'.print_r($stmt,true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('queryPost()クエリ成功：'.print_r($stmt,true));
  return $stmt;
}

//SQL実行関数
function queryPostLimit($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行（失敗した場合はデバッグ）
  foreach ($data as $param_id => $value) {
    switch (gettype($value)) {
      case 'integer':
          $param_type = PDO::PARAM_INT;
          break;
      case 'string':
          $param_type = PDO::PARAM_STR;
          break;
      case 'NULL':
          $param_type = PDO::PARAM_NULL;
          break;
      case 'boolean':
        $param_type = PDO::PARAM_BOOL;
        break;
      case 'double':
        $param_type = PDO::PARAM_STR;
        break;
      default:
          $param_type = PDO::PARAM_STR;
    }
    $stmt->bindValue($param_id, $value, $param_type);
  }
  // $stmt->execute();
  if(!$stmt->execute()){
    debug('queryPost()クエリ失敗：'.print_r($stmt,true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('queryPost()クエリ成功：'.print_r($stmt,true));
  return $stmt;
}
//================================
// ユーザー情報取得
function getUsersProf($u_id){
  debug('getUsersProf()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT id, username, email, group_id, comment, pic FROM users WHERE id = :u_id AND is_deleted = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを1レコード返却（連想配列形式で取得）
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//================================
// ユーザー情報取得
function getUsersData($u_id){
  debug('getUsersData()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT u.id, username, u.pic, comment, u.group_id, g.group_name FROM users AS u LEFT JOIN groups AS g ON u.group_id = g.id WHERE u.id = :u_id AND u.is_deleted = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを1レコード返却（連想配列形式で取得）
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
// ユーザーパスワード取得
function getUsersPass($u_id){
  debug('getUsersPass()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT id, username, email, pass FROM users WHERE u.id = :u_id AND is_deleted = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを1レコード返却（連想配列形式で取得）
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//================================
// カードテンプレート情報取得
function getTemplete($c_id = ''){
  debug('getTemplete()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT id, card_pic, name, user_id FROM cards WHERE is_deleted = 0';
    $data = array();
    // 画像IDを指定した場合はその画像データのみ取得
    if(!empty($c_id)){
      $sql .= ' AND id = :c_id';
      $data = array(':c_id' => $c_id);
    }
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//================================
// 送信カード情報取得
function getUsersMessage($u_id, $m_id){
  debug('getUsersMessage()');
  debug('ユーザーID：'.$u_id);
  debug('メッセージID：'.$m_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT * FROM messages WHERE from_user = :u_id AND id = :m_id AND is_deleted = 0';
    $data = array(':u_id' => $u_id, ':m_id' => $m_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを1レコード返却（連想配列形式で取得）
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//================================
// カード詳細情報取得
function getMessageDetail($m_id){
  debug('getMessageDetail()');
  debug('メッセージID：'.$m_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT m.id, msg, card_id, m.pic, c.card_pic, m.created_at, m.from_user, m.to_user, fu.username AS from_username, tu.username AS to_username FROM messages AS m LEFT JOIN users AS fu ON m.from_user = fu.id LEFT JOIN users AS tu ON m.to_user = tu.id LEFT JOIN cards AS c ON m.card_id = c.id WHERE m.id = :m_id AND m.is_deleted = 0';
    $data = array(':m_id' => $m_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを1レコード返却（連想配列形式で取得）
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
//相手とのやりとりを取得
function getConversations($u_id, $p_id, $span = 20,$currentMinNum = 0, $is_asc = '', $is_fav = ''){
  debug('getConversations()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文
    $sql = 'SELECT m.id FROM messages AS m';
    if($is_fav){
      $sql .= ' INNER JOIN favorites AS f ON m.id = f.message_id';
    }
    $sql .= ' WHERE to_user = :u_id AND from_user = :p_id AND m.is_deleted = 0 OR to_user = :p_id AND from_user = :u_id AND m.is_deleted = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // 総レコード数と総ページ数を連想配列に代入
    // rowCount()でクエリで取得したレコード数を取得
    $rst['total'] = $stmt->rowCount(); //総レコード数
    // 総ページ数 = 総レコード数 ÷ 1ページの表示レコード数（ceil()で切り上げ）
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    // クエリ失敗の場合はfalseで返す
    if(!$stmt){
      return false;
    }
    // SQL文
    $sql = 'SELECT m.id, msg, card_id, m.pic, c.card_pic, m.created_at, m.from_user, fu.username AS from_username, fu.group_id AS from_group, tu.username AS to_username, tu.group_id AS to_group FROM messages AS m LEFT JOIN users AS fu ON m.from_user = fu.id LEFT JOIN users AS tu ON m.to_user = tu.id LEFT JOIN cards AS c ON m.card_id = c.id';
    // お気に入りフラグがある場合
    if($is_fav){
      $sql .= ' INNER JOIN favorites AS f ON m.id = f.message_id';
    }
    $sql .= ' WHERE to_user = :u_id AND from_user = :p_id AND m.is_deleted = 0 OR to_user = :p_id AND from_user = :u_id AND m.is_deleted = 0';
    // 昇順・降順の指定がある場合
    if($is_asc){
      $sql .= ' ORDER BY created_at ASC';
    }else{
      $sql .= ' ORDER BY created_at DESC';
    }
    // LIMITに取得するレコード数、OFFSETにスキップする件数を指定
    $sql .= ' LIMIT :span OFFSET :currentMinNum';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id, ':span' => $span, ':currentMinNum' => $currentMinNum);
    // クエリ実行
    $stmt = queryPostLimit($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコード格納
      $rst['data'] = $stmt->fetchAll();
      debug('★$rst：'.print_r($rst,true));
      // クエリ結果の全データを返却
      return $rst;
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//================================
//メッセージ一覧を取得
function getMessageList($u_id, $span = 20, $currentMinNum = 0, $category, $is_asc = '', $is_fav = ''){
  debug('getMessageList()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文
    $sql = 'SELECT m.id FROM messages AS m';
    if($is_fav){
      $sql .= ' INNER JOIN favorites AS f ON m.id = f.message_id';
    }
    $sql .= ' WHERE m.is_deleted = 0';
    // 受信・送信分の場合、WHERE句を追加
    if(!empty($category)){
      switch($category){
        case 'RECEIVED':
          $sql .= ' AND to_user = :u_id';
          break;
        case 'SENT':
          $sql .= ' AND from_user = :u_id';
          break;
        case 'ALL':
          break;
      }
    }
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // 総レコード数と総ページ数を連想配列に代入
    // rowCount()でクエリで取得したレコード数を取得
    $rst['total'] = $stmt->rowCount(); //総レコード数
    // 総ページ数 = 総レコード数 ÷ 1ページの表示レコード数（ceil()で切り上げ）
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    // クエリ失敗の場合はfalseで返す
    if(!$stmt){
      return false;
    }

    // データを取得
    if(!empty($category)){
      switch($category){
        // 受信一覧の場合
        case 'RECEIVED':
          $sql = 'SELECT m.id, from_user, msg, card_id, m.pic, c.card_pic, m.created_at, u.username, u.group_id, u.is_deleted AS from_user_deleted FROM messages AS m LEFT JOIN users AS u ON m.from_user = u.id LEFT JOIN cards AS c ON m.card_id = c.id';
          break;
        // 送信一覧の場合
        case 'SENT':
          $sql = 'SELECT m.id, to_user, msg, card_id, m.pic, c.card_pic, m.created_at, u.username, u.group_id, u.is_deleted AS to_user_deleted FROM messages AS m LEFT JOIN users AS u ON m.to_user = u.id LEFT JOIN cards AS c ON m.card_id = c.id';
          break;
        // みんなのカード一覧の場合
        case 'ALL':
            $sql = 'SELECT m.id, msg, card_id, m.pic, c.card_pic, m.created_at, m.from_user, fu.username AS from_username, m.to_user, tu.username AS to_username, fu.is_deleted AS from_user_deleted, tu.is_deleted AS to_user_deleted FROM messages AS m LEFT JOIN users AS fu ON m.from_user = fu.id LEFT JOIN users AS tu ON m.to_user = tu.id LEFT JOIN cards AS c ON m.card_id = c.id';
            break;
      }
    }
    if($is_fav){
      $sql .= ' INNER JOIN favorites AS f ON m.id = f.message_id';
    }
    if(!empty($category)){
      switch($category){
        case 'RECEIVED':
          $sql .= ' WHERE m.to_user = :u_id AND m.is_deleted = 0';
          $data = array(':u_id' => $u_id, ':span' => $span, ':currentMinNum' => $currentMinNum);
          break;
        case 'SENT':
          $sql .= ' WHERE m.from_user = :u_id AND m.is_deleted = 0';
          $data = array(':u_id' => $u_id, ':span' => $span, ':currentMinNum' => $currentMinNum);
          break;
        case 'ALL':
          $sql .= ' WHERE m.is_deleted = 0';
          $data = array(':span' => $span, ':currentMinNum' => $currentMinNum);
          break;
      }
    }
    if($is_asc){
      $sql .= ' ORDER BY created_at ASC';
    }else{
      $sql .= ' ORDER BY created_at DESC';
    }
    // LIMITに取得するレコード数、OFFSETにスキップする件数を指定
    $sql .= ' LIMIT :span OFFSET :currentMinNum';
    
    // クエリ実行
    $stmt = queryPostLimit($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコード格納
      $rst['data'] = $stmt->fetchAll();
      debug('★$rst：'.print_r($rst,true));
      // クエリ結果の全データを返却
      return $rst;
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
//メッセージ数を取得
function getAmount($u_id){
  debug('getAmount()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 受信件数用のSQL文
    $sql = 'SELECT id FROM messages WHERE to_user = :u_id AND is_deleted = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // rowCount()でレコード数を取得
    $rst['received'] = $stmt->rowCount();

    // 送信件数用のSQL文
    $sql = 'SELECT id FROM messages WHERE from_user = :u_id AND is_deleted = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // rowCount()でレコード数を取得
    $rst['sent'] = $stmt->rowCount();

    if($stmt){
      debug('★$rst：'.print_r($rst,true));
      return $rst;
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
//ユーザーの活動履歴を取得
function getUsersHistory($currentMinNum = 0, $span = 20, $u_id){
  debug('getUsersHistory()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文
    $sql = 'SELECT m.id FROM messages AS m WHERE to_user = :u_id AND m.is_deleted = 0 OR from_user = :u_id AND m.is_deleted = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // 総レコード数と総ページ数を連想配列に代入
    // rowCount()でクエリで取得したレコード数を取得
    $rst['total'] = $stmt->rowCount(); //総レコード数
    // 総ページ数 = 総レコード数 ÷ 1ページの表示レコード数（ceil()で切り上げ）
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    // クエリ失敗の場合はfalseで返す
    if(!$stmt){
      return false;
    }
    // SQL文
    $sql = 'SELECT m.id, card_id, m.pic, c.card_pic, m.created_at, m.from_user, fu.username AS from_username, m.to_user, tu.username AS to_username, fu.is_deleted AS from_user_deleted, tu.is_deleted AS to_user_deleted FROM messages AS m LEFT JOIN users AS fu ON m.from_user = fu.id LEFT JOIN users AS tu ON m.to_user = tu.id LEFT JOIN cards AS c ON m.card_id = c.id WHERE from_user = :u_id AND m.is_deleted = 0 OR to_user = :u_id AND m.is_deleted = 0 ORDER BY created_at DESC';

    // LIMITに取得するレコード数、OFFSETにスキップする件数を指定
    $sql .= ' LIMIT :span OFFSET :currentMinNum';
    $data = array(':u_id' => $u_id, ':span' => $span, ':currentMinNum' => $currentMinNum);
    // クエリ実行
    $stmt = queryPostLimit($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコード格納
      $rst['data'] = $stmt->fetchAll();
      debug('★$rst：'.print_r($rst,true));
      // クエリ結果の全データを返却
      return $rst;
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
// メンバー一覧取得
function getUsersList($g_id = ''){
  debug('getUsersList()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT u.id, username, u.pic, comment, group_name FROM users AS u LEFT JOIN groups AS g ON g.id = u.group_id WHERE u.is_deleted = 0';
    $data = array();
    if(!empty($g_id)){
      $sql .= ' AND group_id = :g_id';
      $data = array(':g_id' => $g_id);
    }
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
// ログインユーザー以外のユーザー情報を取得
function getOtherUsers($u_id){
  debug('getOtherUsers()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT id, username, email FROM users WHERE id != :u_id AND is_deleted = 0';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
// グループ情報取得
function getGroups(){
  debug('getGroups()');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT * FROM groups WHERE is_deleted = 0';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
//お気に入り情報確認
function isFavorite($u_id, $m_id){
  debug('isFavorite()');
  debug('ユーザーID：'.$u_id);
  debug('カードID：'.$m_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT * FROM favorites WHERE message_id = :m_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':m_id' => $m_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt->rowCount()){
      debug('お気に入りです');
      return true;
    }else{
      debug('お気に入りではありません');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
//ユーザーのお気に入り情報取得 マイページ
function getFavorites($u_id){
  debug('getFavorites()');
  debug('ユーザーID：'.$u_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文
    $sql = 'SELECT * FROM favorites AS f LEFT JOIN messages AS m ON f.message_id = m.id WHERE f.user_id = :u_id';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

//================================
// メール送信
//================================
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        //文字化け防止
        mb_language("Japanese"); //現在使っている言語を設定
        mb_internal_encoding("UTF-8"); //エンコーディング（機械語への変換）設定
        
        //メール送信（送信結果はtrueかfalseで返却される）
        $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
        //送信結果を判定
        if ($result) {
          debug('メールを送信しました。');
          debug('★from：'.$from);
          debug('★to：'.$to);
          debug('★subject：'.$subject);
          debug('★comment：'.$comment);
        } else {
          debug('【エラー発生】メールの送信に失敗しました。');
        }
    }
}

//================================
// その他
//================================
// サニタイズ
function sanitize($str, $is_br = false){
  if($is_br){
    //nl2br()で改行を維持しつつサニタイズ
    return nl2br(htmlspecialchars($str,ENT_QUOTES));
  }else{
    // 改行コードも含めてサニタイズ
    return htmlspecialchars($str,ENT_QUOTES);
  }
}
//================================
// フォーム入力保持
function getFormData($str, $flg = false){
  global $dbFormData;
  global $err_msg;
  global $pic;
  
  // debug('★getFormData()$str：'.print_r($str,true));
  if($flg){
    $method = $_GET;
    // debug('★GET情報を$methodに入れる');
  }else{
    $method = $_POST;
    // debug('★POST情報を$methodに入れる');
  }
  // debug('getFormData()$method：'.print_r($method,true));

  if($str == 'pic' && !empty($pic)){
    // debug('★$picを返す：'.$pic);
    return sanitize($pic);
  }else{
    // DBにユーザーデータがある場合
    if(!empty($dbFormData)){
      //フォームエラーがある場合
      if(!empty($err_msg[$str])){
        //POSTデータがある場合は返す
        if(isset($method[$str])){
          // debug('getFormData()フォームエラーあり・POST情報を返す'.$str);
          return sanitize($method[$str]);
        }else{
          //データがない場合はDBの情報を表示
          // debug('getFormData()フォームエラーあり・DB情報を返す'.$str);
          return sanitize($dbFormData[$str]);
        }
      }else{
          //POSTデータがあり、DBの情報と違う場合
          if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
          // debug('getFormData()フォームエラーなし・POST情報を返す'.$str);
          return sanitize($method[$str]);
        }else{
          //変更されていない場合、DBの情報をそのまま表示
          // debug('getFormData()フォームエラーなし・DB情報を返す'.$str);
          return sanitize($dbFormData[$str]);
        }
      }
    // DBにユーザーデータがない場合
    }else{
      if(isset($method[$str])){
        // debug('getFormData()POST情報を返す'.$str);
        return sanitize($method[$str]);
      }
    }
  }
}
//================================
// セッション変数の値を一時取得
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    // 引数で指定したキーのセッションデータを格納し、中身を空にする
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
//================================
//認証キー生成（8文字）
function makeRandKey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    //変数を初期化
    $str = '';
    //mt_randで0～61から1つ選択し、対応する文字を.=で連結していく
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    //連結した8文字を返す
    return $str;
}
//================================
// 画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));
  
  //エラーがあり、かつ数値型の場合
  if (isset($file['error']) && is_int($file['error'])) {
    try {
      switch ($file['error']) {
          case UPLOAD_ERR_OK: // エラーなしの場合、何もしない
              break;
          case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
              throw new RuntimeException('ファイルが選択されていません');
          case UPLOAD_ERR_INI_SIZE:  // php.iniで定義した最大サイズを超過した場合
          case UPLOAD_ERR_FORM_SIZE: // フォームで定義した最大サイズを超過した場合
              throw new RuntimeException('ファイルサイズが大きすぎます');
          default: // その他の場合
              throw new RuntimeException('その他のエラーが発生しました');
      }
      // 画像ファイルのMIMEタイプチェック
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      // 必ず手前に@を付けてエラーを無視
      $type = @exif_imagetype($file['tmp_name']);
      debug('★画像の$type：'.print_r($type,true));
      // in_arrayでMIMEタイプに値が含まれているかをチェック
      // 第三引数にはtrueを設定して厳密にチェック
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
          throw new RuntimeException('画像形式が未対応です');
      }

      // sha1_file()でファイルデータからSHA-1ハッシュを取り、ランダムなファイル名を生成
      // image_type_to_extension($type)でファイルの拡張子を取得して連結
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      
      //move_uploaded_file()でファイルをアップロード
      if (!move_uploaded_file($file['tmp_name'], $path)) { 
          throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更
      chmod($path, 0644);
      
      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      // ファイルのパスを返す
      return $path;

    } catch (RuntimeException $e) {
      // 例外発生時はデバッグ、エラーメッセージ格納
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();

    }
  }
}
//================================
// ページネーション
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $pageColNum = 5){
  // 現在のページが総ページ数と同じ かつ 総ページ数が表示項目数以上なら、左にリンクを4つ出す
  if( $currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが総ページ数の1ページ前なら、左にリンクを3つ、右に1つ出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現在のページが2の場合は左にリンクを1つ、右に3つ出す
  }elseif( $currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現在のページが1の場合は左に何も出さず、右に5つ出す
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、ループのMaxを総ページ数、Minを1に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左右にリンクを2つずつ出す
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }
  // GETパラメータが空でない場合
  if(!empty($_GET)){
    // p以外のパラメータを付与
    $link = appendGetParam(array('p'), true);
    debug('★$link:'.$link);
  }else{
    $link = '';
  }
  // echoでhtmlタグを出力
  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      // 1ページ目以外は先頭へ戻るボタンを表示
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
        // echo '<li class="list-item"><a href="'.$link.'&p=1">&lt;</a></li>';
      }
      // リンクを生成（現在のページ番号にはactiveクラスを付与）
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      // 最終ページ以外は末尾へ移動ボタンを表示
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}
//================================
//画像表示
function showImg($path){
  if(empty($path)){
    // パスが空の場合はデフォルト画像を返す
    return 'img/sample-img.png';
  }else{
    return $path;
  }
}
//================================
// GETパラメータの生成
function appendGetParam($arr_del_key = array(), $flg = false){
  // GETパラメータが空でない場合
  if(!empty($_GET)){
    if($flg){
      // 第二引数がtrueの場合は先頭に & をつける
      $str = '&';
    }else{
      // 第二引数がなければ先頭に ? をつける
      $str = '?';
    }
    // foreachでGETパラメータを1つずつ展開し、if文を実行
    foreach($_GET as $key => $val){
      // 展開したkeyが引数と異なる場合はパラメータに追加
      if(!in_array($key,$arr_del_key,true)){
        $str .= $key.'='.$val.'&';
      }
    }
    // 末尾の & を取り除く
    $str = mb_substr($str, 0, -1, "UTF-8");
    debug('★appendGetParam:'.$str);
    return $str;
  }
}
