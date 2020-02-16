<footer id="footer">
  Copyright <a href="#">Thanks!</a> All Rights Reserved.
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
  $(function(){

    //================================
    // フッターを最下部に表示
    var $ftr = $('#footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
    }
    //================================
    // メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    // 空白スペースを除いた文字数が0でない場合
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $jsShowMsg.slideToggle('slow');
      // 5000ミリ秒後（5秒後）にスライドトグル関数を再度実行して隠す
      setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
    }
    //================================
    // 画像ライブプレビュー
    // ドラッグ＆ドロップエリアのDOMを取得
    var $dropArea = $('.area-drop');
    // 画像情報が入るinput要素のDOMを取得
    var $fileInput = $('.input-file');

    // 画像ファイルがドラッグして上に乗った時
    $dropArea.on('dragover', function(e){
      // イベントの伝播
      e.stopPropagation();
      e.preventDefault();
      // ファイルが上に乗った要素に破線のボーダーを付ける
      $(this).css('border', '3px #ccc dashed');
    });
    
    // 画像ファイルがドラッグ後、離された時
    $dropArea.on('dragleave', function(e){
      e.stopPropagation();
      e.preventDefault();
      // ドラッグが離されたらボーダーを消す
      $(this).css('border', 'none');
    });

    // inputタグの中身が変わった時（画像の情報が入った時）
    $fileInput.on('change', function(e){
      // ボーダーを消す
      $dropArea.css('border', 'none');
      // files配列の最初の要素を格納
      var file = this.files[0],
          // jQueryのsiblingsメソッドで兄弟要素から.prev-imgのDOMを取得
          // DOMを格納する変数には頭に$を付ける
          $img = $(this).siblings('.prev-img'), 
          // ファイルを読み込むFileReaderオブジェクトを作り、変数に入れる（詳細はオブジェクト指向部）
          fileReader = new FileReader();   

      // 読み込みが完了した際のイベントハンドラ。
      fileReader.onload = function(event) {
        // attrでimgタグのsrc属性に読み込んだ画像データを設定
        // 画像データは引数eventに入っている。event.target.resultで取得。
        // show()で非表示を表示に変更。
        $img.attr('src', event.target.result).show();
      };

      // 画像読み込み
      // fileReaderオブジェクトのreadAsDataURLメソッドで画像をDataURLに変換
      // 画像自体を文字列に変換してimgタグのsrc属性に入れることで表示する
      fileReader.readAsDataURL(file);
    });
    //================================
    // テキストエリアカウント
    var $countUp = $('#js-count'),  //テキストエリア
        $countView = $('#js-count-view');  //文字数表示カウンター
    //テキストエリアでキーが離されたとき
    $countUp.on('keyup', function(e){
      //valでテキストエリアの文字列を取得し、長さをカウンターのDOMに表示
      $countView.html($(this).val().length);
    });
    
    //================================
    // お気に入り登録・削除
    var $fav,
        favMessageId;
    // お気に入りアイコンのDOMを取得（DOMが取得できない場合はnullを初期値とする）
    $fav = $('.js-click-fav');
    
    // お気に入りアイコンがクリックされた時のイベントをセット
    $fav.on('click',function(){
      favMessageId = $(this).data('messageid');
      console.log(favMessageId);
      // 商品IDが取得できた場合（undefinedでない、かつnullでない場合。0はtrueとする）
      if(favMessageId !== undefined && favMessageId !== null){
        
        // 自分自身（アイコン）のDOMを変数に代入
        var $this = $(this);
        // ajaxLike.phpでAjax通信を行う
        $.ajax({
          type: "POST",
          url: "ajaxLike.php",
          data: {messageId : favMessageId} //key(messageId),value(商品ID)を渡す
        }).done(function( data ){
          // Ajax通信が成功した場合
          // ※console.logはユーザーに見えてしまうため通常は使用しない
          console.log('Ajax Success');
          // アイコンDOMのクラス属性をtoggleClass()でつけ外しする
          $this.toggleClass('active');
        }).fail(function( msg ) {
          // Ajax通信が失敗した場合（相手先が見当たらない、サーバーダウン、コード誤り等）
          console.log('Ajax Error');
        });
      };
    });
    
  });
</script>
</body>
</html>