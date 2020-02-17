<?php
// DBからユーザーデータを取得
$dbUsersData = getUsersData($_SESSION['user_id']);
debug('$dbUsersData：'.print_r($dbUsersData,true));

// DBからメッセージ数を取得
$amount = getAmount($_SESSION['user_id']);

debug(basename($_SERVER['PHP_SELF']).'画面表示処理終了 <<<<<<<<<<');
?>

<aside class="sidebar">
      <section class="info">
        <div class="side-prof">
          <div class="side-prof-icon">
            <a href="profile.php?u_id=<?php echo sanitize($dbUsersData['id']); ?>">
              <?php echo (!empty($dbUsersData['pic'])) ? '<img src="'.$dbUsersData['pic'].'" alt="'.sanitize($dbUsersData['username']).'" class="profile-img">' : '<i class="fas fa-user-circle img-null"></i>' ?>
            </a>
          </div>
          <div class="side-prof-name">
          <a href="profile.php?u_id=<?php echo sanitize($dbUsersData['id']); ?>">
          <?php echo sanitize($dbUsersData['username']); ?> さん</a>
          </div>
        </div>
        <div class="box">
          <h4><i class="fas fa-envelope-open-text"></i>届いたカード</h4>
          <span class="amount-num"><?php echo sanitize($amount['received']); ?></span> 枚
        </div>
        <div class="box">
          <h4><i class="fas fa-envelope"></i>贈ったカード</h4>
          <span class="amount-num"><?php echo sanitize($amount['sent']); ?></span> 枚
        </div>

      </section>
      <section class="sub-menu">
        <ul>
          <li><a href="history.php">活動履歴</a></li>
          <li><a href="profEdit.php">プロフィール編集</a></li>
          <li><a href="passEdit.php">パスワード変更</a></li>
          <li><a href="withdraw.php">退会する</a></li>
        </ul>
      </section>
    </aside>
