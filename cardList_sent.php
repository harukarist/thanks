<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

//-------------------------------------------------
// 画面処理
//-------------------------------------------------

// 一覧画面表示用データ取得
//-------------------------------------------------
// GETパラメータを取得
//----------------------------------
debug('GETパラメータ：' . print_r($_GET, true));
// カレントページのGETパラメータを取得(pagination()で付与)
$currentPageNum = (!empty($_GET['p'])) ? (int) $_GET['p'] : 1; //デフォルトは1ページ目
// GETパラメータを取得
$is_asc = (!empty($_GET['asc'])) ? $_GET['asc'] : '';
$is_fav = (!empty($_GET['fav'])) ? $_GET['fav'] : '';
$category = 'SENT';

// GETパラメータに不正な値が入っている場合はトップページへ
// int型にキャストしてからis_intで整数型かどうかをチェック
if (!is_int((int) $currentPageNum)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php");
  exit();
}
// 1ページあたりの表示件数を指定
$listSpan = 12;
// スキップする件数を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan);
// DBからメッセージデータを取得
$dbMessages = getMessageList($_SESSION['user_id'], $listSpan, $currentMinNum, $category, $is_asc, $is_fav);

// debug('現在のページ：'.$currentPageNum);
debug('$dbMessages：' . print_r($dbMessages, true));

debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
?>
<?php
$siteTitle = 'カード一覧';
require('head.php');
?>

<body>
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <main id="main" class="l-main--one-column">
    <h1 class="c-title__top"><i class="fas fa-inbox"></i>カード一覧</h1>

    <section class="p-card-list__menu">
      <ul class="p-card-list__menu-list">
        <li class="p-card-list__menu-item"><a href="cardList.php"><i class="fas fa-envelope-open-text"></i>届いたカード</a></li>
        <li class="p-card-list__menu-item active"><a href="cardList_sent.php"><i class="fas fa-envelope"></i>贈ったカード</a></li>
        <li class="p-card-list__menu-item"><a href="cardList_all.php"><i class="fas fa-share-alt-square"></i>みんなのカード</a></li>
      </ul>
    </section>

    <section class="p-card-list__contents">
      <h2 class="c-title__sub"><i class="fas fa-envelope"></i>あなたが贈ったカード</h2>
      <div class="c-sort-menu">
        <?php if (!$is_asc) { ?>
          <a href="cardList_sent.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&asc=1' : '?asc=1'; ?>"><i class="fas fa-sort-numeric-down c-sort-menu__icon"></i>日付の古い順に並べ替え</a>
        <?php } else { ?>
          <a href="cardList_sent.php<?php echo (!empty(appendGetParam())) ? appendGetParam(array('asc')) : '?asc=0'; ?>"><i class="fas fa-sort-numeric-down-alt c-sort-menu__icon"></i>日付の新しい順に並べ替え</a>
        <?php } ?>
      </div>
      <div class="c-sort-menu">
        <?php if (!$is_fav) { ?>
          <a href="cardList_sent.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&fav=1' : '?fav=1'; ?>"><i class="fab fa-gratipay c-sort-menu__icon"></i>お気に入りカードのみ表示</a>
        <?php } else { ?>
          <a href="cardList_sent.php<?php echo (!empty(appendGetParam())) ? appendGetParam(array('fav')) : '?fav=0'; ?>"><i class="fas fa-inbox c-sort-menu__icon"></i>すべてのカードを表示</a>
        <?php } ?>
      </div>

      <div class="p-card-panel">
        <?php
        if (!empty($dbMessages)) foreach ($dbMessages['data'] as $key => $val) :
        ?>

          <div class="p-card-panel__card-item">
            <!-- カード詳細画面へのリンク -->
            <a href="cardDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&m_id=' . $val['id'] : '?m_id=' . $val['id']; ?>" class="p-card-panel__card-link">
              <!-- カード画像 -->
              <img src="<?php echo (sanitize($val['card_id']) === '0') ? sanitize($val['pic']) : sanitize($val['card_pic']); ?>" alt="<?php echo sanitize($val['username']) . 'さんへのカード'; ?>" class="p-card-panel__card-img">
              <!-- メッセージ -->
              <p class="p-card-panel__card-message"><?php echo sanitize($val['msg'], true); ?></p>
            </a>

            <div class="p-card-panel__card-info">
              <p class="p-card-panel__name">
                <?php if ($val['to_user_deleted'] === '0') : ?>
                  <a href="profile.php?u_id=<?php echo sanitize($val['to_user']); ?>">
                    <?php echo sanitize($val['username']); ?></a>
                <?php else : ?>
                  <?php echo sanitize($val['username']); ?>
                <?php endif; ?>
                <span class="u-font-size--s">さんへ</span>

                <p class="p-card-panel__date"><?php echo date('Y-m-d H:i', strtotime(sanitize($val['created_at']))); ?></p>

                <i class="fab fa-gratipay p-card-panel__icon c-icon c-icon-fav js-click-fav <?php if (isFavorite($_SESSION['user_id'], $val['id'])) echo 'active'; ?>" aria-hidden="true" data-messageid="<?php echo sanitize($val['id']); ?>"></i>

                <a href="cardCreate.php?m_id=<?php echo sanitize($val['id']); ?>"><i class="fas fa-edit p-card-panel__icon c-icon c-icon-reply"></i></a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="c-search-result">
        <?php if ($dbMessages['total'] !== 0) { ?>
          <?php echo (!empty($dbMessages['data'])) ? $currentMinNum + 1 : '0'; ?> - <?php echo (!empty($dbMessages['data'])) ? $currentMinNum + count($dbMessages['data']) : '0'; ?>枚目を表示（<?php echo sanitize($dbMessages['total']); ?>枚中）
        <?php } else {
          echo 'カードがまだありません';
        } ?>
      </div>

      <!-- ページネーション -->
      <?php
      pagination($currentPageNum, (int) $dbMessages['total_page']);
      ?>

    </section>
  </main>
  <!-- フッター -->
  <?php
  require('footer.php');
  ?>
