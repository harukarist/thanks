<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

// 画面表示用データ取得
//================================
// GETデータを格納
$partner_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';

// カレントページのGETパラメータを取得(pagination()で付与)
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは1ページ目
// GETパラメータを取得
$is_asc = (!empty($_GET['asc'])) ? $_GET['asc'] : '';
$is_fav = (!empty($_GET['fav'])) ? $_GET['fav'] : '';

// DBからユーザーデータを取得
$dbUsersData = (!empty($partner_id)) ? getUsersData($partner_id) : '';
// DBからメッセージ数を取得
$amount = (!empty($partner_id)) ? getAmount($partner_id): '';
// デバッグ出力
debug('$dbUsersData：'.print_r($dbUsersData,true));

// パラメータ改ざんチェック
//================================
// GETパラメータがあるがDBから取得したデータが空だった場合
if(!empty($partner_id) && empty($dbUsersData)){
  debug('GETパラメータのIDが違います。');
  header("Location:index.php");
  exit();
}elseif(!is_int((int)$currentPageNum) || empty($partner_id)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php");
  exit();
}

if($partner_id !== $_SESSION['user_id']){
  // 1ページあたりの表示件数を指定
  $listSpan = 20;
  // スキップする件数を算出
  $currentMinNum = (($currentPageNum-1)*$listSpan);
  // DBからメッセージデータを取得
  $dbMessages = getConversations($_SESSION['user_id'], $partner_id, $listSpan, $currentMinNum, $is_asc, $is_fav);
  debug('$dbMessages：'.print_r($dbMessages,true));
}else{
  $dbMessages = '';
}



debug(basename($_SERVER['PHP_SELF']).'画面表示処理終了 <<<<<<<<<<');
?>
<?php
$siteTitle = 'プロフィール';
require('head.php'); 
?>
<body>
  <!-- ヘッダー -->
  <?php
    require('header.php'); 
  ?>

  <div id="contents" class="site-width">
    <section id="main" class="main page-1column">
      <div class="profile-list">
        <div class="prof-icon">
          <?php echo (!empty($dbUsersData['pic'])) ? '<img src="'.$dbUsersData['pic'].'" alt="'.sanitize($dbUsersData['username']).'" class="profile-img">' : '<i class="fas fa-user-circle img-null"></i>' ?>
        </div>
        <div class="prof-detail">
          <h3><?php echo sanitize($dbUsersData['username']); ?>さん</h3>
          <p><?php echo sanitize($dbUsersData['group_name']); ?></p>
          <p><?php echo sanitize($dbUsersData['comment']); ?></p>
          <div class="box-wrapper">
            <div class="box">
            <h4><i class="fas fa-envelope-open-text"></i>届いたカード</h4>
            <span class="amount-num"><?php echo sanitize($amount['received']); ?></span> 枚
            </div>
            <div class="box">
              <h4><i class="fas fa-envelope"></i>贈ったカード</h4>
              <span class="amount-num"><?php echo sanitize($amount['sent']); ?></span> 枚
            </div>
          </div>
        </div>
      </div>

      <div class="<?php if($dbMessages == '') echo ' display-none' ?>">
        <h2 class="page-title"><?php echo sanitize($dbUsersData['username']); ?>さんとのやりとり</h2>
        <div class="sort-icon">
          <?php if(!$is_asc){ ?>
            <a href="profile.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&asc=1' : '?asc=1'; ?>"><i class="fas fa-sort-numeric-down icon icon-sort"></i>日付の古い順に並べ替え</a> 
          <?php }else{ ?>
            <a href="profile.php<?php echo (!empty(appendGetParam())) ? appendGetParam(array('asc')) : '?asc=0'; ?>"><i class="fas fa-sort-numeric-down-alt icon icon-sort"></i>日付の新しい順に並べ替え</a> 
          <?php } ?>

          <?php if(!$is_fav){ ?>
            <a href="profile.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&fav=1' : '?fav=1'; ?>"><i class="fab fa-gratipay icon icon-fav"></i>お気に入りカードのみ表示</a> 
          <?php }else{ ?>
            <a href="profile.php<?php echo (!empty(appendGetParam())) ? appendGetParam(array('fav')) : '?fav=0'; ?>"><i class="fab fa-gratipay icon icon-fav"></i>すべてのカードを表示</a> 
          <?php } ?>
        </div>
        <div class="search-title">
          <?php if($dbMessages['total'] !== 0){ ?>
            <?php echo (!empty($dbMessages['data'])) ? $currentMinNum+1 : '0'; ?> - <?php echo (!empty($dbMessages['data'])) ? $currentMinNum+count($dbMessages['data']) : '0'; ?>枚目を表示（<?php echo sanitize($dbMessages['total']); ?>枚中）
          <?php }else{ echo 'カードがまだありません'; } ?>
        </div>

        <div class="message-list">
          <?php
            if(!empty($dbMessages)) foreach($dbMessages['data'] as $key => $val):
          ?>

          <div class="message">
            <div class="message-left">
              <a href="cardDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&m_id='.$val['id'] : '?m_id='.$val['id']; ?>" class="card-link">
              <img src="<?php echo (sanitize($val['card_id']) == 0) ? sanitize($val['pic']) : sanitize($val['card_pic']); ?>" alt="<?php echo sanitize($val['from_username']).'さんからのカード'; ?>" class="message-img">
              </a>
            </div>
            <div class="message-right">
              <div class="messages-message">
                <p><?php echo sanitize($val['msg'],true); ?></p>
              </div>
              <div class="messages-detail">
                <p class="messages-name">
                <?php echo sanitize($val['from_username']); ?><span class="small">さん &gt;&gt;</span> <?php echo sanitize($val['to_username']); ?><span class="small">さん</span></p>
                <p class="messages-date"><?php echo date('Y-m-d H:i', strtotime(sanitize($val['created_at']))); ?></p>

                <i class="fab fa-gratipay icon icon-fav js-click-fav <?php if(isFavorite($_SESSION['user_id'], $val['id'])) echo 'active'; ?>" aria-hidden="true" data-messageid="<?php echo sanitize($val['id']); ?>" ></i>

                <a href="cardCreate.php?<?php echo ($val['from_user'] == $_SESSION['user_id']) ? 'm_id='.sanitize($val['id']) : 'u_id='.sanitize($val['from_user']); ?>"><i class="fas fa-comment-dots icon icon-reply"></i></a>


              </div>
            </div>
          </div>

          <?php endforeach; ?> 
        </div>

        <!-- ページネーション -->
        <?php
        pagination($currentPageNum, $dbMessages['total_page']);
        ?>

      </div>
    </section>
  </div>

<!-- フッター -->
<?php
require('footer.php'); 
?>