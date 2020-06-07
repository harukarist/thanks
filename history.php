<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

//ログイン認証
require('auth.php');

// 画面表示用データ取得
//-------------------------------------------------
// カレントページのGETパラメータを取得(pagination()で付与)
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは1ページ目

// GETパラメータに不正な値が入っている場合はトップページへ
// int型にキャストしてからis_intで整数型かどうかをチェック
if (!is_int((int) $currentPageNum)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php");
  exit();
}
// 1ページあたりの表示件数を指定
$listSpan = 10;
// スキップする件数を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan);
// DBからユーザーデータを取得
$dbUsersData = getUsersHistory($currentMinNum, $listSpan, $_SESSION['user_id']);

// デバッグ出力
debug('$dbUsersData：' . print_r($dbUsersData, true));


debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
?>
<?php
$siteTitle = '活動履歴';
require('head.php');
?>

<body>
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <div class="l-wrapper u-clearfix">
    <main id="main" class="l-main--two-column">
      <h1 class="c-title__top">活動履歴</h1>
      <section class="p-history">

        <table class="c-table p-history__table">
          <tr>
            <th>カード</th>
            <th>日時</th>
            <th>やりとり</th>
          </tr>

          <?php
          if (!empty($dbUsersData)) foreach ($dbUsersData['data'] as $key => $val) :
          ?>

            <tr>
              <td>
                <a href="cardDetail.php?m_id=<?php echo sanitize($val['id']); ?>" class="p-card-panel__card-link">
                  <img src="<?php echo (sanitize($val['card_id']) === '0') ? sanitize($val['pic']) : sanitize($val['card_pic']); ?>" alt="<?php echo sanitize($val['from_username']) . 'さんからのカード'; ?>" class="p-history__img">
                </a>
              </td>
              <td>
                <p class="p-history__date"><?php echo date('Y-m-d H:i', strtotime(sanitize($val['created_at']))); ?></p>
              </td>
              <td>
                <p class="p-history__detail">
                  <?php if (sanitize($val['from_user']) === $_SESSION['user_id']) { ?>
                    <?php if ($val['to_user_deleted'] === '0') : ?>
                      <a href="profile.php?u_id=<?php echo sanitize($val['to_user']); ?>"><?php echo sanitize($val['to_username']) ?>さん</a>
                    <?php else : ?>
                      <?php echo sanitize($val['to_username']) ?>さん
                    <?php endif; ?>
                    へカードを贈りました！
                  <?php
                  } else {
                  ?>
                    <?php if ($val['from_user_deleted'] === '0') : ?>
                      <a href="profile.php?u_id=<?php echo sanitize($val['from_user']); ?>"><?php echo sanitize($val['from_username']) ?>さん</a>
                    <?php else : ?>
                      <?php echo sanitize($val['from_username']) ?>さん
                    <?php endif; ?>
                    からカードを受け取りました！
                  <?php
                  }
                  ?>
                </p>
            </tr>

          <?php endforeach; ?>
        </table>
        <div class="c-search-result">
          <?php echo (!empty($dbUsersData['data'])) ? $currentMinNum + 1 : '0'; ?> - <?php echo (!empty($dbUsersData['data'])) ? $currentMinNum + count($dbUsersData['data']) : '0'; ?>件目を表示（<?php echo sanitize($dbUsersData['total']); ?>件中）
        </div>

        <!-- ページネーション -->
        <?php
        pagination((int) $currentPageNum, (int) $dbUsersData['total_page']);
        ?>
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
