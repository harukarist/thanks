<header class="hidden">
  <div class="site-width">
  <h1>
      <?php
        // ログイン前
        if(empty($_SESSION['user_id'])){
      ?>
        <a href="index.php"><i class="fas fa-praying-hands"></i>Thanks!</a>
      <?php
        // ログイン後
        }else{
      ?>
        <a href="mypage.php"><i class="fas fa-praying-hands"></i>Thanks!</a>
      <?php
        }
      ?>
    </h1>
    <nav id="top-nav">
      <ul>
        <?php
          // ログイン前
          if(empty($_SESSION['user_id'])){
        ?>
            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i><span class="text">ログイン</span></a></li>
            <li><a href="signup.php"><i class="fas fa-user-plus"></i><span class="text">ユーザー登録</span></a></li>
            <li><a href="index.php"><i class="fas fa-home"></i><span class="text">TOP</span></a></li>
        <?php
          // ログイン後
          }else{
        ?>
            <li><a href="mypage.php"><i class="fas fa-home"></i><span class="text">マイページ</span></a></li>
            <li><a href="cardCreate.php"><i class="fas fa-paint-brush"></i><span class="text">カードを贈る</span></a></li>
            <li><a href="cardList.php"><i class="fas fa-inbox"></i><span class="text">カード一覧</span></a></li>
            <li><a href="memberList.php"><i class="fas fa-user-friends"></i><span class="text">メンバーリスト</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span class="text">ログアウト</span></a></li>
        <?php
          }
        ?>
      </ul>
    </nav>
  </div>
</header>