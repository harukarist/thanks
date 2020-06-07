<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

//-------------------------------------------------
// 画面処理
//-------------------------------------------------
// DBからユーザーデータを取得
$dbFormData = getUsersProf($_SESSION['user_id']);
debug('$dbFormData：' . print_r($dbFormData, true));

// DBからグループデータを取得
$dbGroups = getGroups();
// debug('$dbGroups：'.print_r($dbGroups,true));

// post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信あり');
  debug('$_POST：' . print_r($_POST, true));
  debug('$_FILES：' . print_r($_FILES, true));

  //変数にPOST情報を代入
  $username = $_POST['username'];
  $email = $_POST['email'];
  $group = $_POST['group_id'];
  $comment = $_POST['comment'];
  $temp_pic = $_POST['temp_pic'];
  debug('$temp_pic：' . $temp_pic);

  // 画像削除チェックがあればパスを削除
  if (!empty($_POST['pic_delete'])) {
    $pic = '';
  } else {
    if (!empty($_FILES['pic']['name'])) {
      //POST画像を一時アップロードし、パスを格納
      $pic = uploadImg($_FILES['pic'], 'pic');
    } else {
      // 一時保存した画像のパスがあれば格納
      $pic = (!empty($temp_pic)) ? $temp_pic : '';
      // POST画像がなくDBに登録がある場合はDBのパスを格納
      $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
    }
  }
  debug('$pic：' . $pic);
  //変更があった場合はバリデーション
  if ($dbFormData['username'] !== $username) {
    //未入力チェック
    validRequired($username, 'username');
    //名前の最大文字数チェック
    validMaxLen($username, 'username', 25);
  }
  if ($dbFormData['group_id'] !== $group) {
    //セレクトボックスチェック
    validSelect($group, 'group_id');
  }
  if ($dbFormData['comment'] !== $comment) {
    //最大文字数チェック
    validMaxLen($comment, 'comment', 25);
  }

  if ($dbFormData['email'] !== $email) {
    if ($_SESSION['user_id'] === '6') {
      $err_msg['email'] = MSG17;
    } else {
      //実運用時は認証キーをメール送信して認証を行う
      //emailの未入力チェック
      validRequired($email, 'email');
    }
    if (empty($err_msg['email'])) {
      //emailの最大文字数チェック
      validMaxLen($email, 'email');
      //emailの形式チェック
      validEmail($email, 'email');
      if (empty($err_msg['email'])) {
        //emailの重複チェック
        validEmailDup($email);
      }
    }
  }

  if (empty($err_msg)) {
    debug('バリデーションOK');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET username = :u_name, email = :email, group_id = :group_id, comment = :comment, pic = :pic WHERE id = :u_id';
      $data = array(':u_name' => $username, ':email' => $email, ':group_id' => $group, ':comment' => $comment, ':pic' => $pic, ':u_id' => $dbFormData['id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        // トグル出力
        $_SESSION['msg_success'] = SUC02;
        debug('マイページへ遷移');
        header("Location:mypage.php");
        exit();
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
?>
<?php
$siteTitle = 'プロフィール編集';
require('head.php');
?>

<body>
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <div class="l-wrapper u-clearfix">
    <main id="main" class="l-main--two-column">
      <h1 class="c-title__top">プロフィール編集</h1>

      <section class="c-form-container">
        <form action="" method="post" class="c-form c-form--thin" enctype="multipart/form-data">
          <div class="c-form__area-msg">
            <?php echo getErrMsg('common'); ?>
          </div>

          <div class="c-form__item-title">お名前<span class="c-label__required">必須</span></div>
          <label class="js-form-label<?php if (!empty($err_msg['username'])) echo ' is-error'; ?>">
            <input type="text" name="username" id="js-valid-name" value="<?php echo getFormData('username'); ?>">
            <div class="c-form__area-msg js-area-msg">
              <?php echo getErrMsg('username'); ?>
            </div>
          </label>

          <div class="c-form__item-title">Email<span class="c-label__required">必須</span></div>
          <label class="js-form-label<?php if (!empty($err_msg['email'])) echo ' is-error'; ?>">
            <input type="text" name="email" id="js-valid-email" value="<?php echo getFormData('email'); ?>">
            <div class="c-form__area-msg js-area-msg">
              <?php echo getErrMsg('email'); ?>
            </div>
          </label>

          <div class="c-form__item-title">部署</div>
          <label class="<?php if (!empty($err_msg['group_id'])) echo 'is-error'; ?>">
            <select name="group_id" id="">
              <option value="0" <?php if (getFormData('group_id') === '0') {
                                  echo 'selected';
                                } ?>>選択してください</option>
              <?php
              foreach ($dbGroups as $key => $val) {
              ?>
                <option value="<?php echo $val['id']; ?>" <?php if (getFormData('group_id') === $val['id']) {
                                                            echo 'selected';
                                                          } ?>>
                  <?php echo $val['group_name']; ?>
                </option>
              <?php
              }
              ?>
            </select>
            <div class="c-form__area-msg">
              <?php echo getErrMsg('group_id'); ?>
            </div>
          </label>

          <div class="c-form__item-title">ひとこと</div>
          <label class="js-form-label<?php if (!empty($err_msg['comment'])) echo ' is-error'; ?>">
            <input type="text" name="comment" id="js-valid-comment" value="<?php echo getFormData('comment'); ?>">
            <!-- 文字数カウント -->
            <p class="c-form__counter-text"><span class="js-count-view">0</span>/25文字</p>
            <div class="c-form__area-msg js-area-msg">
              <?php echo getErrMsg('comment'); ?>
            </div>
          </label>

          <div class="c-form__item-title">プロフィール画像</div>
          <label class="c-area-drop c-area-drop--large js-drop-area <?php if (!empty($err_msg['pic'])) echo 'is-error'; ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            <input type="hidden" name="temp_pic" value="<?php echo sanitize(getFormData('pic')); ?>">
            <input type="file" name="pic" class="c-input-file js-file-input">
            <img src="<?php echo sanitize(getFormData('pic')); ?>" alt="プロフィール画像" class="c-prev-img js-prev-img<?php if (empty(getFormData('pic'))) echo ' u-display-none' ?>">
            <div class="c-form__area-msg">
              <?php echo getErrMsg('pic'); ?>
            </div>
          </label>
          <label>
            <input type="checkbox" name="pic_delete" value="on">画像を削除する
          </label>

          <div class="c-form__btn-container">
            <input type="submit" class="c-btn c-btn--large c-btn--colored" value="変更する">
          </div>
        </form>
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
