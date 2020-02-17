<?php
  //共通ファイル読込み・デバッグスタート
  require('function.php');
  debugLogStart();

//ログイン認証
require('auth.php');

// 受信カードデータ取得
//================================
// 表示件数を指定
$listSpan = 6;
$currentMinNum = 0;
$category = 'RECEIVED';
// DBからメッセージデータを取得
$dbReceived = getMessageList($_SESSION['user_id'], $listSpan, $currentMinNum, $category);
debug('$dbReceived：'.print_r($dbReceived,true));

// 送信カードデータ取得
//================================
// 表示件数を指定
$listSpan = 6;
$category = 'SENT';
// DBからメッセージデータを取得
$dbSent = getMessageList($_SESSION['user_id'], $listSpan, $currentMinNum, $category);

debug('$dbSent：'.print_r($dbSent,true));

debug(basename($_SERVER['PHP_SELF']).'画面表示処理終了 <<<<<<<<<<');
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
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <!-- 引数をキーとするセッション変数の値を取得 -->
    <?php echo getSessionFlash('msg_success'); ?>
  </p>


  <div id="contents">
    <main class="main page-2column">
      <h1 class="page-title"><i class="fas fa-home"></i>マイページ</h1>

      <section class="article received">
        <h2><i class="fas fa-envelope-open-text"></i>あなたに届いたカード</h2>
        <div class="panel-list">
          <?php
            if($dbReceived['total'] !== 0) :
              foreach($dbReceived['data'] as $key => $val):
          ?>

          <div class="card">
            <a href="cardDetail.php?m_id=<?php echo sanitize($val['id']); ?>" class="card-link">
              <img src="<?php echo (sanitize($val['card_id']) == 0) ? sanitize($val['pic']) : sanitize($val['card_pic']); ?>" alt="<?php echo sanitize($val['username']).'さんからのカード'; ?>" class="card-img">
              <p class="card-message"><?php echo sanitize($val['msg'],true); ?></p>
            </a>
            <div class="card-info">
              <p class="name">
              <?php if ($val['from_user_deleted'] == 0): ?>
                <a href="profile.php?u_id=<?php echo sanitize($val['from_user']); ?>">
                <?php echo sanitize($val['username']); ?></a>
                <?php else: ?>
                  <?php echo sanitize($val['username']); ?>
                <?php endif; ?>
                <span class="small">さんより</span>
              </p>
              <p class="date"><?php echo date('Y-m-d H:i', strtotime(sanitize($val['created_at']))); ?></p>

              <i class="fab fa-gratipay icon icon-fav js-click-fav <?php if(isFavorite($_SESSION['user_id'], $val['id'])) echo 'active'; ?>" aria-hidden="true" data-messageid="<?php echo sanitize($val['id']); ?>" ></i>

              <a href="cardCreate.php?u_id=<?php echo sanitize($val['from_user']); ?>"><i class="fas fa-reply icon icon-reply"></i></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="btn-border"><a href="cardList.php">もっと見る<i class="fas fa-angle-double-right"></i></a></div>
        <?php else : ?>
          <p class="center">カードがまだありません</p>
        <?php endif; ?>
      </section>

      <section class="article sent">
        <h2><i class="fas fa-envelope"></i>あなたが贈ったカード</h2>
        <div class="panel-list">
          <?php
            if($dbSent['total'] !== 0):
              foreach($dbSent['data'] as $key => $val):
          ?>
          <div class="card">
            <a href="cardDetail.php?m_id=<?php echo sanitize($val['id']); ?>" class="card-link">
              <img src="<?php echo (sanitize($val['card_id']) == 0) ? sanitize($val['pic']) : sanitize($val['card_pic']); ?>" alt="<?php echo sanitize($val['username']).'さんへのカード'; ?>" class="card-img">
              <p class="card-message"><?php echo sanitize($val['msg'],true); ?></p>
            </a>
            <div class="card-info">
              <p class="name">
              <?php if ($val['to_user_deleted'] == 0): ?>
                <a href="profile.php?u_id=<?php echo sanitize($val['to_user']); ?>"><?php echo sanitize($val['username']); ?></a>
              <?php else: ?>
                <?php echo sanitize($val['username']); ?>
              <?php endif; ?>
                <span class="small">さんへ</span>
              </p>
              <p class="date"><?php echo date('Y-m-d H:i', strtotime(sanitize($val['created_at']))); ?></p>

              <i class="fab fa-gratipay icon icon-fav js-click-fav <?php if(isFavorite($_SESSION['user_id'], $val['id'])) echo 'active'; ?>" aria-hidden="true" data-messageid="<?php echo sanitize($val['id']); ?>" ></i>

              <a href="cardCreate.php?m_id=<?php echo sanitize($val['id']); ?>"><i class="fas fa-edit icon icon-reply"></i></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="btn-border"><a href="cardList_sent.php">もっと見る<i class="fas fa-angle-double-right"></i></a></div>
        <?php else : ?>
          <p class="center">カードがまだありません</p>
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