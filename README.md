# linebot_lib
linebotをシンプルに使えるライブラリ

# 設定
define.phpにlinebotのトークンを設定して下さい
~~~
define('ACCESS_TOKEN','test_token');  //アクセストークン
define('CHANNEL_SECRET','channel_token');  //シークレット
~~~

linebotのdevelopersからWebhook URLをsample_linebot.phpに設定して下さい

# linebot
linebotにメッセージを送信するとそのままオウム返しします

コマンドを送信すると多数のメッセージタイプを返します
- イメージ
- 位置情報
- スタンプ
- ボタン
- 確認
- カルーセル
- イメージカルーセル
- flex
- flex2
- flex3
- flex4
- all_flex

以下は物を用意しurlを設定する必要があります
- 動画
- 音声
- イメージマップ

# コード
非常に少なくシンプルなコードで動作します
オウム返しに必要なコード
~~~
<?php
// ライブラリを読み込み
require_once __DIR__ . '/linebot.php';
// クラスをインスタンス化
$bot = new LineBotClass();
// メッセージがなくなるまでループ
while ($bot->check_shift_event()) {
    // テキストを取得
    $text = $bot->get_text();
    // テキストメッセージを追加
    $bot->add_text_builder($text);
    // 返信
    $bot->reply();
}
~~~

返信するメッセージはストックされていきます
なの2件のメッセージを返信したいときは
~~~
<?php
// ライブラリを読み込み
require_once __DIR__ . '/linebot.php';
// クラスをインスタンス化
$bot = new LineBotClass();
// メッセージがなくなるまでループ
while ($bot->check_shift_event()) {
    // テキストを取得
    $text = $bot->get_text();
    // テキストメッセージを追加
    $bot->add_text_builder($text);
    $bot->add_text_builder($text); // 追記
    // 返信
    $bot->reply();
}
~~~
これで送信したメッセージと同じメッセージが2つ返信されます

# Qiita
[こちらで](https://qiita.com/jyuki/items/035cfeb72d7d359c738b)もう少し解説してますのでご覧ください