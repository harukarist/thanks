<header id="header">
  <div class="header-wrapper">
    <h1>
      <?php
      // ログイン前
      if (empty($_SESSION['user_id'])) {
      ?>
        <a href="index.php"><i class="fas fa-praying-hands"></i>Thanks!</a>
      <?php
        // ログイン後
      } else {
      ?>
        <a href="mypage.php"><i class="fas fa-praying-hands"></i>Thanks!</a>
      <?php
      }
      ?>
    </h1>
    <!-- ハンバーガーメニュー　spanタグで三本線を描写 -->
    <div class="header-trigger js-sp-menu-trigger">
      <span class="header-trigger--line"></span>
      <span class="header-trigger--line"></span>
      <span class="header-trigger--line"></span>
      <span class="header-trigger--text"></span>
    </div>
    <nav id="header-nav" class="js-sp-menu-target">
      <ul class="menu-list">
        <?php
        // ログイン前
        if (empty($_SESSION['user_id'])) {
        ?>
          <li><a href="login.php" class="js-sp-menu-link"><i class="fas fa-sign-in-alt"></i><span class="text">ログイン</span></a></li>
          <li><a href="signup.php" class="js-sp-menu-link"><i class="fas fa-user-plus"></i><span class="text">ユーザー登録</span></a></li>
          <li><a href="index.php" class="js-sp-menu-link"><i class="fas fa-home"></i><span class="text">TOP</span></a></li>
        <?php
          // ログイン後
        } else {
        ?>
          <li><a href="mypage.php" class="js-sp-menu-link"><i class="fas fa-home"></i><span class="text">マイページ</span></a></li>
          <li><a href="cardCreate.php" class="js-sp-menu-link"><i class="fas fa-paint-brush"></i><span class="text">カードを贈る</span></a></li>
          <li><a href="cardList.php" class="js-sp-menu-link"><i class="fas fa-inbox"></i><span class="text">カード一覧</span></a></li>
          <li><a href="memberList.php" class="js-sp-menu-link"><i class="fas fa-user-friends"></i><span class="text">メンバーリスト</span></a></li>
          <?php if (!empty($_SESSION['is_admin'])) echo '<li><a href="templateEdit.php" class="js-sp-menu-link"><i class="fas fa-cog"></i><span class="text">管理</span></a></li>' ?>

          <li><a href="logout.php" class="js-sp-menu-link"><i class="fas fa-sign-out-alt"></i><span class="text">ログアウト</span></a></li>


        <?php
        }
        ?>
      </ul>
    </nav>
  </div>
</header>
