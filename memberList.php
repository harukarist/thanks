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
// グループのGETパラメータを取得(セレクトボックスで指定)
$group = (!empty($_GET['group_id'])) ? $_GET['group_id'] : '';

// DBからデータを取得
$dbMembers = getUsersList($group);
$dbGroups = getGroups();
debug('$dbMembers：' . print_r($dbMembers, true));
debug('$dbGroups：' . print_r($dbGroups, true));

debug(basename($_SERVER['PHP_SELF']) . '画面表示処理終了 --------------');
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

  <main id="main" class="l-main--one-column">
    <h1 class="c-title__top"><i class="fas fa-user-friends"></i>メンバーリスト</h1>

    <section>
      <form class="c-form p-member-list__group-select" method="get">
        <h3>部署名で検索</h3>
        <select name="group_id" id="" class="js-required-select">
          <option value="" <?php if (getFormData('group_id', true) === '0') echo 'selected'; ?>>選択してください</option>
          <?php
          if (!empty($dbGroups)) foreach ($dbGroups as $key => $val) :
          ?>
            <option value="<?php echo $val['id'] ?>" <?php if (getFormData('group_id', true) === $val['id']) echo 'selected'; ?>>
              <?php echo $val['group_name']; ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="submit" class="c-btn c-btn--large c-btn--colored js-disabled-btn" value="検索">
      </form>

      <?php if (!empty($dbMembers)) : ?>
        <table class="c-table p-member-list__table">
          <?php foreach ($dbMembers as $key => $val) : ?>
            <tr class="p-member-list__item">
              <td class="p-member-list__avatar">
                <a href="profile.php?u_id=<?php echo sanitize($val['id']); ?>">
                  <?php if (!empty($val['pic'])) : ?>
                    <img src="<?php echo sanitize($val['pic']); ?>" alt="<?php echo sanitize($val['username']); ?>" class="p-member-list__img">
                  <?php else : ?>
                    <i class="fas fa-user-circle c-avatar__img-null p-member-list__img"></i>
                  <?php endif; ?>
                </a>
              </td>
              <td class="p-member-list__detail">
                <a href="profile.php?u_id=<?php echo sanitize($val['id']); ?>">
                  <p class="p-member-list__name"><?php echo sanitize($val['username']); ?></p>
                </a>
                <p class="p-member-list__comment"><?php echo sanitize($val['comment']); ?></p>
                <p class="p-member-list__group"><?php echo sanitize($val['group_name']); ?></p>
              </td>

              <td class="p-member-list__action">
                <span class="<?php if ((int) $val['id'] === (int) $_SESSION['user_id']) echo 'u-visibility-hidden'; ?>">
                  <a href="profile.php?u_id=<?php echo sanitize($val['id']); ?>"><i class="fas fa-inbox c-icon c-icon-inbox"></i></a>
                </span>
              </td>
              <td class="p-member-list__action">
                <span class="<?php if ((int) $val['id'] === (int) $_SESSION['user_id']) echo 'u-visibility-hidden'; ?>">
                  <a href="cardCreate.php?tu_id=<?php echo sanitize($val['id']); ?>"><i class="fas fa-comment-dots c-icon c-icon-comment"></i></a>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php else : ?>
        <p class="c-text--center">まだメンバーが登録されていません</p>
      <?php endif; ?>

    </section>
  </main>
  <!-- フッター -->
  <?php
  require('footer.php');
  ?>
