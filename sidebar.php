<?php
// DBからユーザーデータを取得
$dbUsersData = getUsersData($_SESSION['user_id']);
debug('$dbUsersData：' . print_r($dbUsersData, true));

// DBからメッセージ数を取得
$amount = getAmount($_SESSION['user_id']);

debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
?>

<aside class="l-sidebar">
  <section class="p-sidebar__info">
    <div class="p-sidebar__avatar">
      <a href="profEdit.php">
        <?php echo (!empty($dbUsersData['pic'])) ? '<img src="' . $dbUsersData['pic'] . '" alt="' . sanitize($dbUsersData['username']) . '" class="p-sidebar__avatar-img">' : '<i class="fas fa-user-circle c-avatar__img-null"></i>' ?>
      </a>
    </div>
    <a href="profEdit.php" class="p-sidebar__username">
      <?php echo sanitize($dbUsersData['username']); ?> さん
    </a>
    <a href="profile.php?u_id=<?php echo sanitize($dbUsersData['id']); ?>" class="p-sidebar__profile-link">プロフィールを見る</a>
    <div class="p-sidebar__amount-box">
      <a href="cardList.php">
        <h4><i class="fas fa-envelope-open-text"></i>届いたカード</h4>
        <span class="u-amount-num"><?php echo sanitize($amount['received']); ?></span> 枚
      </a>
    </div>
    <div class="p-sidebar__amount-box">
      <a href="cardList_sent.php">
        <h4><i class="fas fa-envelope"></i>贈ったカード</h4>
        <span class="u-amount-num"><?php echo sanitize($amount['sent']); ?></span> 枚
      </a>
    </div>

  </section>
  <section class="p-sidebar__nav-menu">
    <ul>
      <li><a href="history.php">活動履歴</a></li>
      <li><a href="profEdit.php">プロフィール編集</a></li>
      <li><a href="passEdit.php">パスワード変更</a></li>
      <li><a href="withdraw.php">退会する</a></li>
    </ul>
  </section>
</aside>
