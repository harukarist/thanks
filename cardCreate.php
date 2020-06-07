<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

//-------------------------------------------------
// 画面処理
//-------------------------------------------------

// 画面表示用データ取得
//-------------------------------------------------
// GETパラメータを格納
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
$tu_id = (!empty($_GET['tu_id'])) ? $_GET['tu_id'] : '';
$user_id = $_SESSION['user_id'];

// GETパラメータがあればDBからメッセージデータを取得
$dbFormData = (!empty($m_id)) ? getUsersMessage($_SESSION['user_id'], $m_id) : '';
// 新規登録か編集かの判別フラグ
$is_edit = (empty($dbFormData)) ? false : true;

// DBからメンバーデータを取得
$dbUsersList = getOtherUsers($_SESSION['user_id']);
// DBからテンプレートデータを取得
$dbTemplate = getTemplate();

// デバッグ出力
debug('メッセージID：' . $m_id);
debug('$dbFormData：' . print_r($dbFormData, true));
debug('$dbUsersList：' . print_r($dbUsersList, true));
debug('$dbTemplate：' . print_r($dbTemplate, true));

// パラメータ改ざんチェック
//-------------------------------------------------
// GETパラメータがあるがDBから取得したデータが空だった場合
if (!empty($m_id) && empty($dbFormData)) {
  debug('GETパラメータのIDが違います。新規登録画面を表示します。');
  header("Location:cardCreate.php");
  exit();
}

// POST送信時処理
//-------------------------------------------------
if (!empty($_POST)) {
  debug('POST送信あり');
  //テキスト情報
  debug('POST情報：' . print_r($_POST, true));
  debug('FILE情報：' . print_r($_FILES, true));

  // カード削除の場合
  if (isset($_POST['card-delete'])) {

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      $sql = 'UPDATE messages SET is_deleted = :is_deleted WHERE from_user = :u_id AND id = :m_id';
      // データ流し込み
      $data = array(':is_deleted' => 1, ':u_id' => $user_id, ':m_id' => $m_id);

      // カード削除のクエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        $sql = 'UPDATE favorites SET is_deleted = :is_deleted WHERE message_id = :m_id';
        // データ流し込み
        $data = array(':is_deleted' => 1, ':m_id' => $m_id);
        // お気に入り削除のクエリ実行
        $stmt2 = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if ($stmt2) {
          $_SESSION['msg_success'] = SUC05;
          debug('マイページへ遷移');
          header("Location:mypage.php"); //マイページへ
          exit();
        }
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
    // カードの登録・変更の場合
  } elseif (isset($_POST['card-create'])) {
    //変数にユーザー情報を代入
    $to_user = $_POST['to_user'];
    $card_id = $_POST['card_id'];
    $msg = $_POST['msg'];
    $temp_pic = $_POST['temp_pic'];
    debug('$temp_pic：' . $temp_pic);

    //POST画像があればアップロードし、パスを格納
    if (!empty($_FILES['pic']['name'])) {
      $pic = uploadImg($_FILES['pic'], 'pic');
    } else {
      // 一時保存した画像のパスがあれば格納
      $pic = (!empty($temp_pic)) ? $temp_pic : '';
      // POST画像がなくDBに登録がある場合はDBのパスを格納
      $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
    }
    debug('$pic：' . $pic);

    // バリデーションチェック
    // 新規登録時
    if (empty($dbFormData)) {
      //宛先チェック
      validRequired($to_user, 'to_user');
      //カード選択チェック
      validSelect($card_id, 'card_id');
      //メッセージチェック
      validRequired($msg, 'msg');
      validMaxLen($msg, 'msg', 140);
    } else {
      // 更新の場合
      if ($dbFormData['to_user'] !== $to_user) {
        //宛先チェック
        validRequired($to_user, 'to_user');
        validSelect($to_user, 'to_user');
      }
      if ($dbFormData['card_id'] !== $card_id) {
        //ラジオボタンチェック
        validRequired($card_id, 'card_id');
        validSelect($card_id, 'card_id');
      }
      if ($dbFormData['msg'] !== $msg) {
        // メッセージチェック
        validRequired($msg, 'msg');
        validMaxLen($msg, 'msg', 140);
      }
    }

    if (empty($err_msg)) {
      debug('バリデーションOK');

      //例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // 編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
        if ($is_edit) {
          debug('DB更新です。');
          // SQL文作成
          $sql = 'UPDATE messages SET to_user = :to_user, card_id = :card_id, msg = :msg, pic = :pic WHERE from_user = :u_id AND id = :m_id';
          // データ流し込み
          $data = array(':to_user' => $to_user, ':card_id' => $card_id, ':msg' => $msg, ':pic' => $pic, ':u_id' => $user_id, ':m_id' => $m_id);
        } else {
          debug('DB新規登録です。');
          // SQL文作成
          $sql = 'INSERT INTO messages (to_user, from_user, msg, card_id, pic, created_at) VALUES (:to_user, :u_id, :msg, :card_id, :pic, :created_at)';
          // データ流し込み
          $data = array(':to_user' => $to_user, ':card_id' => $card_id, ':msg' => $msg, ':pic' => $pic, ':u_id' => $user_id, ':created_at' => date('Y-m-d H:i:s'));
        }
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if ($stmt) {
          $_SESSION['msg_success'] = SUC04;
          debug('マイページへ遷移');
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
debug(basename($_SERVER['PHP_SELF']) . '処理終了 <<<<<');
?>
<!-- 編集フラグの値によって表示を変える -->
<?php
$siteTitle = (!$is_edit) ? 'カードを贈る' : 'カードを編集';
require('head.php');
?>

<body>
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <main id="main" class="l-main--one-column">
    <h1 class="c-title__top"><i class="fas fa-paint-brush"></i><?php echo (!$is_edit) ? 'カードを贈る' : 'カードを編集する'; ?></h1>
    <section class="c-form-container">

      <div class="c-form__guide">
        <?php if (!$is_edit) : ?>
          カードを作成して送信します
        <?php else : ?>
          送信したカードの内容を編集します
        <?php endif ?>
      </div>


      <form action="" method="post" enctype="multipart/form-data" class="c-form p-cardCreate__form">
        <div class="c-form__area-msg">
          <?php
          if (!empty($err_msg['common'])) echo $err_msg['common'];
          ?>
        </div>
        <label <?php if (!empty($err_msg['to_user'])) echo 'class="is-error"'; ?>>
          <div class="c-form__item-title">贈り先を選択<span class="c-label__required">必須</span><span class="c-form__area-msg"><?php echo getErrMsg('to_user'); ?></span></div>

          <?php if (empty($tu_id) && empty(getFormData('to_user'))) {
            echo '<select name="to_user" size="7">';
          } else {
            echo '<select name="to_user" size="1">';
          } ?>
          <?php if (!empty($dbUsersList)) foreach ($dbUsersList as $key => $val) : ?>
            <option value="<?php echo $val['id']; ?>" <?php if (getFormData('to_user') === $val['id'] || $tu_id === $val['id']) echo 'selected'; ?> class="js-required">
              <!-- セレクトボックスに表示する値 -->
              <?php echo $val['username']; ?> さん
            </option>
          <?php endforeach; ?>

          </select>
        </label>

        <label class="u-mt--xxxl <?php if (!empty($err_msg['card_id'])) echo 'is-error'; ?>">
          <div class="c-form__item-title">カードを選ぶ<span class="c-label__required">必須</span><span class="c-form__area-msg"><?php echo getErrMsg('card_id'); ?></span></div>
          <div class="c-form__area-msg">
            <?php echo getErrMsg('pic'); ?>
          </div>


          <input id="trigger1" class="expand-trigger" type="checkbox">
          <div class="expand-item">
            <div class="p-cardCreate__card-list">
              <div class="p-cardCreate__card-item u-padding-0">
                <label class="c-area-drop c-area-drop__panel js-drop-area js-cardCreate<?php if (!empty($err_msg['pic'])) echo 'is-error'; ?>">
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="hidden" name="temp_pic" value="<?php echo sanitize(getFormData('pic')); ?>">
                  <input type="file" name="pic" class="c-input-file js-file-input js-cardCreate">
                  <img src="<?php echo sanitize(getFormData('pic')); ?>" alt="アップロード画像" class="c-prev-img p-cardCreate__card-img js-prev-img <?php if (empty(getFormData('pic'))) echo ' u-display-none' ?>">

                  <span class="p-cardCreate__radio-original"><input type="radio" name="card_id" value="0" class="js-radio-is-check js-required" <?php if (getFormData('card_id') === '0') echo 'checked'; ?>>
                    オリジナル画像</span>
                </label>
              </div>
              <?php
              if (!empty($dbTemplate)) foreach ($dbTemplate as $key => $val) :
              ?>
                <div class="p-cardCreate__card-item">
                  <label>
                    <img src="<?php echo showImg(sanitize($val['card_pic'])); ?>" class="p-cardCreate__card-img" alt="<?php echo sanitize($val['name']); ?>">


                    <span class="p-cardCreate__radio-template"><input type="radio" name="card_id" value="<?php echo $val['id']; ?>" class='js-required' <?php if (getFormData('card_id') === $val['id']) echo 'checked'; ?>>
                      <?php echo sanitize($val['card_name']); ?></span>

                  </label>
                </div>
              <?php
              endforeach;
              ?>
            </div>
          </div>
          <span class="expand-btn" for="trigger1"></span>
        </label>

        <label class="js-form-label <?php if (!empty($err_msg['msg'])) echo 'is-error'; ?>">
          <div class="c-form__item-title">ひとことメッセージを書く<span class="c-label__required">必須</span><span class="c-form__area-msg js-area-msg"><?php echo getErrMsg('msg'); ?></span></div>
          <textarea name="msg" id="js-valid-message" class="p-cardCreate__textarea js-required"><?php echo getFormData('msg'); ?></textarea>
          <!-- 文字数カウント -->
          <p class="c-form__counter-text"><span class="js-count-view">0</span>/140文字</p>
        </label>


        <div class="c-form__btn-container">
          <input type="submit" name="card-create" class="c-btn c-btn--large c-btn--colored js-disabled-btn" disabled="disabled" value="<?php echo (!$is_edit) ? 'カードを贈る' : '変更する'; ?>">

          <?php if ($is_edit) : ?>
            <input type="submit" name="card-delete" class="c-btn c-btn--thin c-btn--border" value="カードを削除する">
          <?php endif; ?>
        </div>
      </form>
    </section>

  </main>

  <!-- フッター -->
  <?php
  require('footer.php');
  ?>
