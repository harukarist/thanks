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
// GETデータを格納
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
// DBからメッセージデータを取得
$dbMsgData = (!empty($m_id)) ? getMessageDetail($m_id) : '';

// デバッグ出力
debug('メッセージID：' . $m_id);
debug('$dbMsgData：' . print_r($dbMsgData, true));

// パラメータ改ざんチェック
//-------------------------------------------------
// GETパラメータがあるがDBから取得したデータが空だった場合

if (!empty($m_id) && empty($dbMsgData)) {
  debug('GETパラメータのIDが違います。マイページを表示します。');
  header("Location:mypage.php");
  exit();
}

debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
?>
<?php
$siteTitle = sanitize($dbMsgData['from_username']) . 'さんからのカード';
require('head.php');
?>

<body>
  <main id="main-header0" class="u-site-width">
    <section class="p-card-detail">
      <div class="p-card-detail__item">
        <img src="<?php echo (sanitize($dbMsgData['card_id']) === '0') ? sanitize($dbMsgData['pic']) : sanitize($dbMsgData['card_pic']); ?>" alt="<?php echo sanitize($dbMsgData['card_id']); ?>" class="p-card-detail__img">
        <p class="p-card-detail__to-user"><?php echo sanitize($dbMsgData['to_username']); ?>さんへ</p>
        <p class="p-card-detail__message"><?php echo sanitize($dbMsgData['msg'], true); ?></p>
        <p class="p-card-detail__from-user"><?php echo sanitize($dbMsgData['from_username']); ?></p>
        <p class="p-card-detail__date"><?php echo date('Y.m.d', strtotime(sanitize($dbMsgData['created_at']))); ?></p>
      </div>
      <div class="p-card-detail__icon">
        <a href="#" onclick="window.history.back(); return false;"><i class="fas fa-chevron-left c-icon-back"></i>戻る</a>
        <?php if ($dbMsgData['from_user'] === $_SESSION['user_id']) { ?>
          <a href="cardCreate.php?m_id=<?php echo sanitize($dbMsgData['id']); ?>"><i class="fas fa-edit c-icon c-icon-reply"></i></a>
        <?php } elseif ($dbMsgData['to_user'] === $_SESSION['user_id']) { ?>
          <a href="cardCreate.php?tu_id=<?php echo sanitize($dbMsgData['from_user']); ?>"><i class="fas fa-reply c-icon c-icon-reply"></i></a>
        <?php } ?>
        <i class="fab fa-gratipay c-icon c-icon-fav js-click-fav <?php if (isFavorite($_SESSION['user_id'], $dbMsgData['id'])) echo 'active'; ?>" aria-hidden="true" data-messageid="<?php echo sanitize($dbMsgData['id']); ?>"></i>
      </div>
    </section>
  </main>
