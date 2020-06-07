<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

// 受信カードデータ取得
//-------------------------------------------------
// 表示件数を指定
$listSpan = 6;
$currentMinNum = 0;
$category = 'RECEIVED';
// DBからメッセージデータを取得
$dbReceived = getMessageList($_SESSION['user_id'], $listSpan, $currentMinNum, $category);
debug('$dbReceived：' . print_r($dbReceived, true));

// 送信カードデータ取得
//-------------------------------------------------
// 表示件数を指定
$listSpan = 6;
$category = 'SENT';
// DBからメッセージデータを取得
$dbSent = getMessageList($_SESSION['user_id'], $listSpan, $currentMinNum, $category);

debug('$dbSent：' . print_r($dbSent, true));

debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
?>
<?php
$siteTitle = 'MYPAGE';
require('head.php');
?>

<body>
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <!-- スライドトグル -->
  <p id="js-show-msg" class="c-msg-slide">
    <!-- 引数をキーとするセッション変数の値を取得 -->
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <div class="l-wrapper u-clearfix">
    <main id="main" class="l-main--two-column">
      <h1 class="c-title__top"><i class="fas fa-home"></i>マイページ</h1>

      <section>
        <h2 class="c-title__sub"><i class="fas fa-envelope-open-text"></i>あなたに届いたカード</h2>
        <?php if ($dbReceived['total'] !== 0) : ?>
          <div class="p-card-panel">
            <?php foreach ($dbReceived['data'] as $key => $val) : ?>
              <div class="p-card-panel__card-item">
                <a href="cardDetail.php?m_id=<?php echo sanitize($val['id']); ?>" class="p-card-panel__card-link">
                  <img src="<?php echo (sanitize($val['card_id']) === '0') ? sanitize($val['pic']) : sanitize($val['card_pic']); ?>" alt="<?php echo sanitize($val['username']) . 'さんからのカード'; ?>" class="p-card-panel__card-img">
                  <p class="p-card-panel__card-message"><?php echo sanitize($val['msg'], true); ?></p>
                </a>
                <div class="p-card-panel__card-info">
                  <p class="p-card-panel__name">
                    <?php if ($val['from_user_deleted'] === '0') : ?>
                      <a href="profile.php?u_id=<?php echo sanitize($val['from_user']); ?>">
                        <?php echo sanitize($val['username']); ?></a>
                    <?php else : ?>
                      <?php echo sanitize($val['username']); ?>
                    <?php endif; ?>
                    <span class="u-font-size--s">さんより</span>
                  </p>
                  <p class="p-card-panel__date"><?php echo date('Y-m-d H:i', strtotime(sanitize($val['created_at']))); ?></p>

                  <i class="fab fa-gratipay p-card-panel__icon c-icon c-icon-fav js-click-fav <?php if (isFavorite($_SESSION['user_id'], $val['id'])) echo 'active'; ?>" aria-hidden="true" data-messageid="<?php echo sanitize($val['id']); ?>"></i>

                  <a href="cardCreate.php?tu_id=<?php echo sanitize($val['from_user']); ?>"><i class="fas fa-reply p-card-panel__icon c-icon c-icon-reply"></i></a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <a href="cardList.php" class="c-btn c-btn--border">もっと見る<i class="fas fa-angle-double-right c-btn--angle-right"></i></a>
        <?php else : ?>
          <p class="c-text--center">カードがまだありません</p>
        <?php endif; ?>
      </section>

      <section>
        <h2 class="c-title__sub"><i class="fas fa-envelope"></i>あなたが贈ったカード</h2>

        <?php if ($dbSent['total'] !== 0) : ?>
          <div class="p-card-panel">
            <?php foreach ($dbSent['data'] as $key => $val) : ?>
              <div class="p-card-panel__card-item">
                <a href="cardDetail.php?m_id=<?php echo sanitize($val['id']); ?>" class="p-card-panel__card-link">
                  <img src="<?php echo (sanitize($val['card_id']) === '0') ? sanitize($val['pic']) : sanitize($val['card_pic']); ?>" alt="<?php echo sanitize($val['username']) . 'さんへのカード'; ?>" class="p-card-panel__card-img">
                  <p class="p-card-panel__card-message"><?php echo sanitize($val['msg'], true); ?></p>
                </a>
                <div class="p-card-panel__card-info">
                  <p class="p-card-panel__name">
                    <?php if ($val['to_user_deleted'] === '0') : ?>
                      <a href="profile.php?u_id=<?php echo sanitize($val['to_user']); ?>"><?php echo sanitize($val['username']); ?></a>
                    <?php else : ?>
                      <?php echo sanitize($val['username']); ?>
                    <?php endif; ?>
                    <span class="u-font-size--s">さんへ</span>
                  </p>
                  <p class="p-card-panel__date"><?php echo date('Y-m-d H:i', strtotime(sanitize($val['created_at']))); ?></p>

                  <i class="fab fa-gratipay p-card-panel__icon c-icon c-icon-fav js-click-fav <?php if (isFavorite($_SESSION['user_id'], $val['id'])) echo 'active'; ?>" aria-hidden="true" data-messageid="<?php echo sanitize($val['id']); ?>"></i>

                  <a href="cardCreate.php?m_id=<?php echo sanitize($val['id']); ?>"><i class="fas fa-edit p-card-panel__icon c-icon c-icon-reply"></i></a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <a href="cardList_sent.php" class="c-btn c-btn--border">もっと見る<i class="fas fa-angle-double-right c-btn--angle-right"></i></a>
        <?php else : ?>
          <p class="c-text--center">カードがまだありません</p>
        <?php endif; ?>
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
