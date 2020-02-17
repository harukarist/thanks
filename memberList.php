<?php
  //共通ファイル読込み・デバッグスタート
  require('function.php');
  debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================

// 一覧画面表示用データ取得
//================================
// GETパラメータを取得
//----------------------------------
debug('GETパラメータ：'.print_r($_GET,true));
// グループのGETパラメータを取得(セレクトボックスで指定)
$group = (!empty($_GET['group_id'])) ? $_GET['group_id'] : '';

// DBからデータを取得
$dbMembers = getUsersList($group);
$dbGroups = getGroups();
debug('$dbMembers：'.print_r($dbMembers,true));
debug('$dbGroups：'.print_r($dbGroups,true));

debug(basename($_SERVER['PHP_SELF']).'画面表示処理終了 <<<<<<<<<<');
?>
<?php
$siteTitle = 'メンバーリスト';
require('head.php'); 
?>
<body>
  <!-- ヘッダー -->
  <?php
    require('header.php'); 
  ?>

  <div id="contents" class="site-width">
    <h1 class="page-title"><i class="fas fa-user-friends"></i>メンバーリスト</h1>

    <section id="main" class="main page-1column">
      <div class="sort-icon">
        <form class="form sort" method="get">
          <h3>部署名で検索</h3>
          <select name="group_id" id="">
            <option value="0" <?php if(getFormData('group_id',true) == 0 ){ echo 'selected'; } ?> >選択してください</option>
            <?php
              if(!empty($dbGroups)) foreach($dbGroups as $key => $val):
            ?>
            <option value="<?php echo $val['id'] ?>" <?php if(getFormData('group_id',true) == $val['id'] ){ echo 'selected'; } ?> >
              <?php echo $val['group_name']; ?>
            </option>
            <?php endforeach; ?>
          </select>
          <input type="submit" value="検索">
        </form>
      </div>
      <div class="member-list">
        <?php
          if(!empty($dbMembers)) foreach($dbMembers as $key => $val):
        ?>
        <div class="member">
          <div class="prof-icon">
            <a href="profile.php?u_id=<?php echo sanitize($val['id']); ?>">
            <?php echo (!empty($val['pic'])) ? '<img src="'.sanitize($val['pic']).'" alt="'.sanitize($val['username']).'" class="profile-img">' : '<i class="fas fa-user-circle img-null"></i>' ?></a>
          </div>
          <div class="prof-right">
            <div class="prof-user">
              <a href="profile.php?u_id=<?php echo sanitize($val['id']); ?>"><p class="member-name"><?php echo sanitize($val['username']); ?></p></a>
              <div class="list-icon <?php if($val['id'] == $_SESSION['user_id']) echo 'visibility-hidden'; ?>" >
                <a href="cardCreate.php?u_id=<?php echo sanitize($val['id']); ?>"><i class="fas fa-reply icon icon-reply"></i></a>
              </div>
              <p class="group"><?php echo sanitize($val['group_name']); ?></p>
            </div>
            <div class="comment">
              <p><?php echo sanitize($val['comment']); ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </section>
  </div>
<!-- フッター -->
<?php
  require('footer.php'); 
?>