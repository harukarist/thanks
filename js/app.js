
$(function () {
  //-------------------------------------------------
  // フッターを最下部に表示
  var $ftr = $('#footer');
  if (window.innerHeight > $ftr.offset().top + $ftr.outerHeight()) {
    $ftr.attr({
      'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'
    });
  }
  //-------------------------------------------------
  // メッセージ表示
  var $jsShowMsg = $('#js-show-msg');
  var msg = $jsShowMsg.text();
  // 空白スペースを除いた文字数が0でない場合
  if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
    $jsShowMsg.slideToggle('slow');
    // 3000ミリ秒後（3秒後）にslideToggleを再度実行して隠す
    setTimeout(function () {
      $jsShowMsg.slideToggle('slow');
    }, 3000);
  }
  //-------------------------------------------------
  // 画像ライブプレビュー
  // ドラッグ＆ドロップエリアのDOMを取得
  var $dropArea = $('.js-drop-area');
  // 画像情報が入るinput要素のDOMを取得
  var $fileInput = $('.js-file-input');

  // 画像ファイルがドラッグして上に乗った時
  $dropArea.on('dragover', function (e) {
    // イベントの伝播
    e.stopPropagation();
    e.preventDefault();
    // ファイルが上に乗った要素に破線のボーダーを付ける
    $(this).css('border', '3px #ccc dashed');
  });

  // 画像ファイルがドラッグ後、離された時
  $dropArea.on('dragleave', function (e) {
    e.stopPropagation();
    e.preventDefault();
    // ドラッグが離されたらボーダーを消す
    $(this).css('border', 'none');
    if ($(this).hasClass('js-cardCreate')) {
      // チェックが外れた後も再度チェックされるよう、.attrではなく.propを使う
      $('.js-radio-is-check').prop('checked', true);
    }
  });

  // inputタグの中身が変わった時（画像の情報が入った時）
  $fileInput.on('change', function (e) {
    // ボーダーを消す
    $dropArea.css('border', 'none');
    if ($(this).hasClass('js-cardCreate')) {
      // チェックが外れた後も再度チェックされるよう、.attrではなく.propを使う
      $('.js-radio-is-check').prop('checked', true);
    }
    // files配列の最初の要素を格納
    var file = this.files[0],
      // jQueryのsiblingsメソッドで兄弟要素から.prev-imgのDOMを取得
      // DOMを格納する変数には頭に$を付ける
      $img = $(this).siblings('.js-prev-img'),
      // ファイルを読み込むFileReaderオブジェクトを作り、変数に入れる
      fileReader = new FileReader();

    // 読み込みが完了した際のイベントハンドラ。
    fileReader.onload = function (event) {
      // this.setState({ file: reader.result });

      // attrでimgタグのsrc属性に読み込んだ画像データを設定
      // 画像データは引数eventに入っている。event.target.resultで取得。
      // show()で非表示を表示に変更。
      $img.attr('src', event.target.result).show();
    };

    // 画像読み込み
    // fileReaderオブジェクトのreadAsDataURLメソッドで画像をDataURLに変換
    // 画像自体を文字列に変換してimgタグのsrc属性に入れることで表示する
    fileReader.readAsDataURL(file);
    // fileReader.readAsDataURL(event.target.files[0]);
  });
  //-------------------------------------------------
  //フォームバリデーション
  const MSG_TEXT_MAX = '25文字以内で入力してください。';
  const MSG_EMPTY = '入力必須です。';
  const MSG_EMAIL_TYPE = 'Emailの形式ではありません。'
  const MSG_TEXTAREA_MAX = '140文字以内で入力してください。'
  const MSG_PASS_MIN = '6文字以上で入力してください。';
  const MSG_PASS_RETYPE = 'パスワードが一致しません';

  // メッセージチェック
  $('#js-valid-message').on('keyup', function (e) {
    var $this = $(this);
    var maxLength = 140;
    var $label = $this.closest('.js-form-label');
    var $msgArea = $label.find('.js-area-msg');
    var $counter = $label.find('.js-count-view');

    $counter.text($this.val().length);
    if ($this.val().length > maxLength) {
      $label.addClass('is-error');
      $msgArea.text(MSG_TEXTAREA_MAX);
    } else {
      $label.removeClass('is-error');
      $msgArea.text('');
    }
  });

  // 名前チェック
  $("#js-valid-name").keyup(function () {
    var $this = $(this);
    var maxLength = 25;
    var $label = $this.closest('.js-form-label');
    var $msgArea = $label.find('.js-area-msg');

    if ($(this).val().length === 0) {
      $label.addClass('is-error');
      $msgArea.text(MSG_EMPTY);
    } else if ($this.val().length > maxLength) {
      $label.addClass('is-error');
      $msgArea.text(MSG_TEXT_MAX);
    } else {
      $label.removeClass('is-error');
      $msgArea.text('');
    }
  });

  // ひとことチェック
  $("#js-valid-comment").on('keyup', function () {
    var $this = $(this);
    var maxLength = 25;
    var $label = $this.closest('.js-form-label');
    var $msgArea = $label.find('.js-area-msg');
    var $counter = $label.find('.js-count-view');

    $counter.text($this.val().length);
    if ($this.val().length > maxLength) {
      $label.addClass('is-error');
      $msgArea.text(MSG_TEXT_MAX);
    } else {
      $label.removeClass('is-error');
      $msgArea.text('');
    }
  });

  // メールアドレス形式チェック
  $("#js-valid-email").change(function () {
    var $this = $(this);
    var maxLength = 50;
    var $label = $this.closest('.js-form-label');
    var $msgArea = $label.find('.js-area-msg');

    if ($(this).val().length === 0) {
      $label.addClass('is-error');
      $msgArea.text(MSG_EMPTY);
    } else if ($this.val().length > maxLength || !$this.val().match(/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/)) {
      $label.addClass('is-error');
      $msgArea.text(MSG_EMAIL_TYPE);
    } else {
      $label.removeClass('is-error');
      $msgArea.text('');
    }
  });

  // パスワード文字数チェック
  $("#js-valid-password").change(function () {
    var $this = $(this);
    var minLength = 6;
    var $label = $this.closest('.js-form-label');
    var $msgArea = $label.find('.js-area-msg');

    if ($this.val().length < minLength) {
      $label.addClass('is-error');
      $msgArea.text(MSG_PASS_MIN);
    } else {
      $label.removeClass('is-error');
      $msgArea.text('');
    }
  });

  // パスワード再入力チェック
  $("#js-valid-password-re").change(function () {
    var $this = $(this);
    var minLength = 6;
    var $label = $this.closest('.js-form-label');
    var $msgArea = $label.find('.js-area-msg');
    var pass = $("#js-valid-password").val();

    if ($this.val().length < minLength) {
      $label.addClass('is-error');
      $msgArea.text(MSG_PASS_MIN);
    } else if ($this.val() !== pass) {
      $label.addClass('is-error');
      $msgArea.text(MSG_PASS_RETYPE);
    } else {
      $label.removeClass('is-error');
      $msgArea.text('');
    }
  });

  //-------------------------------------------------
  // フォームの必須項目が入力されたらボタンを活性
  $disabledBtn = $('.js-disabled-btn');
  $disabledBtn.prop("disabled", true);
  $('.js-required').change(function () {
    var isFilled = true;
    //必須項目をひとつずつチェック
    $('.js-required').each(function (e) {
      var $this = $('.js-required');
      if ($this.eq(e).val() === "" || $this.closest('.js-form-label').hasClass('is-error')) {
        isFilled = false;
      }
    });
    //全て埋まっていた場合、送信ボタンを復活
    if (isFilled) {
      $disabledBtn.prop("disabled", false);
    }
    else {
      $disabledBtn.prop("disabled", true);
    }
  });
  //-------------------------------------------------
  // お気に入り登録・削除
  var $fav,
    favMessageId;
  // お気に入りアイコンのDOMを取得（DOMが取得できない場合はnullを初期値とする）
  $fav = $('.js-click-fav');

  // お気に入りアイコンがクリックされた時のイベントをセット
  $fav.on('click', function () {
    favMessageId = $(this).data('messageid');
    console.log(favMessageId);
    // 商品IDが取得できた場合（undefinedでない、かつnullでない場合。0はtrueとする）
    if (favMessageId !== undefined && favMessageId !== null) {

      // 自分自身（アイコン）のDOMを変数に代入
      var $this = $(this);
      // ajaxLike.phpでAjax通信を行う
      $.ajax({
        type: "POST",
        url: "ajaxLike.php",
        data: {
          messageId: favMessageId
        } //key(messageId),value(商品ID)を渡す
      }).done(function (data) {
        // Ajax通信が成功した場合
        // ※console.logはユーザーに見えてしまうため通常は使用しない
        console.log('Ajax Success');
        // アイコンDOMのクラス属性をtoggleClass()でつけ外しする
        $this.toggleClass('active');
      }).fail(function (msg) {
        // Ajax通信が失敗した場合（相手先が見当たらない、サーバーダウン、コード誤り等）
        console.log('Ajax Error');
      });
    };
  });
  //-------------------------------------------------
  // スマホ用メニュー
  // ハンバーガーアイコンがクリックされた時
  $('.js-sp-menu-trigger').on('click', function () {
    $(this).toggleClass('is-active');
    // メニューリンクにis-activeクラスを付ける
    $('.js-sp-menu-target').toggleClass('is-active');
    // アニメーションはCSSで定義
  });
  // リンクがクリックされたらis-activeクラスを外す
  $('.js-menu-link').on('click', function () {
    $('.js-sp-menu-trigger').removeClass('is-active');
    $('.js-sp-menu-target').removeClass('is-active');
  });

});
