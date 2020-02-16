<?php
//共通ファイル読込み・デバッグスタート
require('function.php');
debugLogStart();

debug('ログアウトします。');
// セッションを削除（ログアウト）
session_destroy();
debug('ログインページへ遷移');
// ログインページへ
header("Location:login.php");
exit();