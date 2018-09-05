<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/define.php';


use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\Constant\HTTPHeader;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use \LINE\LINEBot\Event\MessageEvent;
use \LINE\LINEBot\Event\PostbackEvent;
use \LINE\LINEBot\Event\MessageEvent\TextMessage;
use \LINE\LINEBot\Event\MessageEvent\StickerMessage;
use \LINE\LINEBot\Event\MessageEvent\LocationMessage;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\CameraTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\CameraRollTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\LocationTemplateActionBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\FlexBuilder\BubbleBuilder;
use LINE\LINEBot\MessageBuilder\FlexBuilder\ContentsBuilder;
use LINE\LINEBot\MessageBuilder\FlexBuilder\BoxBuilder;
use LINE\LINEBot\MessageBuilder\FlexBuilder\TextBuilder;
use LINE\LINEBot\MessageBuilder\FlexBuilder\SeparatorBuilder;
use LINE\LINEBot\MessageBuilder\FlexBuilder\ButtonBuilder;
use LINE\LINEBot\MessageBuilder\FlexBuilder\ImageBuilder;


/**
* liinbotのapiを使いやすくまとめたクラス
*/
class LineBotClass extends LINEBot
{
	private $bot;
	private $reply_token;
	private $events;
	private $event;
	private $builder_stok = array();
	private $error_stok = array();

	function __construct($default=true)
	{
		$accessToken = ACCESS_TOKEN;
		$channelSecret = CHANNEL_SECRET;

		// アクセストークンでCurlHTTPClientをインスタンス化
		$http_client = new CurlHTTPClient($accessToken);
		// CurlHTTPClientとシークレットでLINEBotをインスタンス化
		// $this->$bot = new LINEBot($http_client, ['channelSecret' => $channelSecret]);
		parent::__construct($http_client, ['channelSecret' => $channelSecret]);
		if ($default) {
			// LINEAPIが付与した署名を取得
			$signature = $_SERVER['HTTP_' . HTTPHeader::LINE_SIGNATURE];
			// 署名が正当かチェック、正当ならリクエストをパースし配列に代入
			$this->events = $this->parseEventRequest(file_get_contents('php://input'),$signature);
		}

		// flexメッセージのライブラリ読み込み
		$directory = __DIR__ . "/vendor/linecorp/line-bot-sdk/src/LINEBot/FlexMessageBuilder/";
		if($handle = opendir($directory)) {
			while (($file = readdir($handle)) !== false) {
				if($file != '.' && $file != '..') {
					require_once($directory.$file);
				}
			}
			closedir($handle);
		}
	}

	/**
	 * イベントを取り出してイベントを更新
	 * @return bool イベントがあるならtrue ないならfalse
	 */
	public function check_shift_event()
	{
		// イベントを取り出す
		$this->event = array_shift($this->events);
		// イベントがあるなら
		if (!empty($this->event)) {
			// 返信トークンを更新
			$this->reply_token = $this->event -> getReplyToken();
			// ビルダーストックを初期化
			$this->builder_stok = array();
			return true;
		}else{
			return false;
		}
	}

	/**
	 * TextMessageのテキストを取得
	 * @return string or false
	 */
	public function get_text()
	{
		// イベントがTextMessageのclassかチェック
		if ($this->event instanceof TextMessage) {
			return $this->event->getText();
		}else{
			$this->set_error("テキストメッセージではありません");
			return false;
		}
	}

	/**
	 * postされたdataを取得
	 * @return string or false
	 */
	public function get_post_data()
	{
		// イベントがPostbackEventのclassかチェック
		if ($this->event instanceof PostbackEvent) {
			return $this->event->getPostbackData();
		}else{
			$this->set_error("ポストバックイベントではありません");
			return false;
		}
	}

	/**
	 * postされたdeteの情報を取得
	 * @return array or false
	 */
	public function get_post_params()
	{
		// イベントがPostbackEventのclassかチェック
		if ($this->event instanceof PostbackEvent) {
			return $this->event->getPostbackParams();
		}else{
			$this->set_error("ポストバックイベントではありません");
			return false;
		}
	}

	/**
	 * スタンプのステッカーidとパッケージidを取得
	 * @return array or false
	 */
	public function get_stamp_id()
	{
		// イベントがStickerMessageのclassかチェック
		if ($this->event instanceof StickerMessage) {
			$id_data = array();
			$id_data['sticker_id'] = $this->event->getStickerId();
			$id_data['package_id'] = $this->event->getPackageId();
			return $id_data;
		}else{
			$this->set_error("スタンプメッセージではありません");
			return false;
		}
	}

	/**
	 * 位置情報のデータを取得
	 * @return arrya or false
	 */
	public function get_location()
	{
		// イベントがLocationMessageのclassかチェック
		if ($this->event instanceof LocationMessage) {
			$location = array();
			$location['title'] = $this->event->getTitle();
			$location['address'] = $this->event->getAddress();
			$location['latitude'] = $this->event->getLatitude();
			$location['longitude'] = $this->event->getLongitude();
			return $location;
		}else{
			$this->set_error("位置情報ではありません");
			return false;
		}
	}

	/**
	 * テキストのメッセージタイプか判定
	 * @return boolean テキストならtrue それ以外ならfalse
	 */
	public function is_text_message_type()
	{
		return $this->get_message_type() === "text";
	}

	/**
	 * 画像のメッセージタイプか判定
	 * @return boolean 画像ならtrue それ以外ならfalse
	 */
	public function is_image_message_type()
	{
		return $this->get_message_type() === "image";
	}

	/**
	 * 動画のメッセージタイプか判定
	 * @return boolean 動画ならtrue それ以外ならfalse
	 */
	public function is_video_message_type()
	{
		return $this->get_message_type() === "video";
	}

	/**
	 * 音声のメッセージタイプか判定
	 * @return boolean 音声ならtrue それ以外ならfalse
	 */
	public function is_audio_message_type()
	{
		return $this->get_message_type() === "audio";
	}

	/**
	 * 位置情報のメッセージタイプか判定
	 * @return boolean 位置情報ならtrue それ以外ならfalse
	 */
	public function is_location_message_type()
	{
		return $this->get_message_type() === "location";
	}

	/**
	 * ファイルのメッセージタイプか判定
	 * @return boolean ファイルならtrue それ以外ならfalse
	 */
	public function is_file_message_type()
	{
		return $this->get_message_type() === "file";
	}

	/**
	 * メッセージタイプを取得
	 * @return string メッセージタイプ
	 *
	 * text      テキスト
	 * image     画像
	 * video     動画
	 * audio     音声
	 * location 位置情報
	 * file      ファイル
	 */
	public function get_message_type()
	{
		// イベントがMessageEventのclassかチェック
		if ($this->event instanceof MessageEvent) {
			return $this->event->getMessageType();
		}else{
			$this->set_error("メッセージイベントではありません");
			return false;
		}
	}

	/**
	 * メッセージidを取得
	 * @return string メッセージid
	 */
	public function get_message_id()
	{
		// イベントがMessageEventのclassかチェック
		if ($this->event instanceof MessageEvent) {
			return $this->event->getMessageId();
		}else{
			$this->set_error("メッセージイベントではありません");
			return false;
		}
	}

	/**
	 * メッセージのイベントタイプか判定
	 * @return boolean メッセージならtrue それ以外ならfalse
	 */
	public function is_message_event_type()
	{
		return $this->get_event_type() === "message";
	}

	/**
	 * 友達追加のイベントタイプか判定
	 * @return boolean 友達追加ならtrue それ以外ならfalse
	 */
	public function is_follow_event_type()
	{
		return $this->get_event_type() === "follow";
	}

	/**
	 * 友達ブロックのイベントタイプか判定
	 * @return boolean 友達ブロックならtrue それ以外ならfalse
	 */
	public function is_unfollow_event_type()
	{
		return $this->get_event_type() === "unfollow";
	}

	/**
	 * グループまたはルーム参加のイベントタイプか判定
	 * @return boolean グループまたはルーム参加ならtrue それ以外ならfalse
	 */
	public function is_join_event_type()
	{
		return $this->get_event_type() === "join";
	}

	/**
	 * グループまたはルームからの退会のイベントタイプか判定
	 * @return boolean グループまたはルームからの退会ならtrue それ以外ならfalse
	 */
	public function is_leave_event_type()
	{
		return $this->get_event_type() === "leave";
	}

	/**
	 * ポストバックのイベントタイプか判定
	 * @return boolean ポストバックならtrue それ以外ならfalse
	 */
	public function is_postback_event_type()
	{
		return $this->get_event_type() === "postback";
	}

	/**
	 * イベントタイプを取得
	 * @return string イベントタイプ
	 * message  メッセージ
	 * follow   友達追加
	 * unfollow 友達ブロック
	 * join     グループまたはルーム参加
	 * leave    グループまたはルームからの退会
	 * postback ポストバック
	 */
	public function get_event_type()
	{
		return $this->event->getType();
	}

	/**
	 * 送信元タイプを取得
	 * @return string user room group
	 */
	public function get_event_sonrce_type()
	{
		return $this->event->getEventSourceType();
	}

	/**
	 * 送信元のユーザーidを取得
	 * @return string ユーザーid
	 */
	public function get_user_id()
	{
		return $this->event->getUserId();
	}

	/**
	 * 送信元のグループidを取得
	 * @return string グループid
	 */
	public function get_group_id()
	{
		return $this->event->getGroupId();
	}

	/**
	 * 送信元のルームidを取得
	 * @return string ルームid
	 */
	public function get_room_id()
	{
		return $this->event->getRoomId();
	}

	/**
	 * 送信元のidを取得
	 * 個人ユーザーからならuser_id (get_user_id()と同等)
	 * グループからならgroup_id (get_group_id()と同等)
	 * ルームからならroom_id (get_room_id()と同等)
	 * @return string id
	 */
	public function get_event_source_id()
	{
		return $this->event->getEventSourceId();
	}

	/**
	 * ユーザーのプロフィールを取得
	 * @param  string $user_id ユーザーid
	 * @return array           ユーザーデータ
	 *
	 * データ構造
	 * array[
	 *  "displayName" =>   "表示名",
	 *  "pictureUrl"  =>   "画像url",
	 *  "statusMessage" => "ステータスメッセージ"
	 * ]
	 */
	public function get_profile($user_id)
	{
		$user_profile = $this->getProfile($user_id);
		if ($user_profile->isSucceeded()) {
			return $user_profile->getJSONDecodedBody();
		}else{
			$this->set_error("取得できませんでした");
			return false;
		}
	}

	/**
	 * グループのユーザープロフィールを取得
	 * @param  [type] $group_id グループid
	 * @param  [type] $user_id  ユーザーid
	 * @return [type]           ユーザーデータ
	 */
	public function get_group_user_profile($group_id,$user_id)
	{
		$user_profile = $this->getGroupMemberProfile($group_id,$user_id);
		if ($user_profile->isSucceeded()) {
			return $user_profile->getJSONDecodedBody();
		}else{
			$this->set_error("取得できませんでした");
			return false;
		}
	}

	/**
	 * 送信されたコンテンツのバイナリデータを取得
	 * @return  成功ならバイナリデータ 失敗ならfalse
	 */
	public function get_content()
	{
		$response  = $this->getMessageContent($this->get_message_id());
		if ($response->isSucceeded()) {
			return $response->getRawBody();
		}else{
			$this->set_error("取得できませんでした");
			return false;
		}
	}

	/**
	 * テキストのビルダーを追加
	 * @param  string $text 送信するテキストメッセージ
	 * @return bool         成功ならtrue 失敗ならfalse
	 */
	public function add_text_builder($text,$actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		$quick_replys = array();
		if (!empty($actions)) {
			foreach ($actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// 空チェック
		if (isset($text) && $text !== "") {
			$this->builder_stok[] = new TextMessageBuilder($text,null,$quick_replys);
			return true;
		}else{
			$this->set_error("テキストは必須です");
			return false;
		}
	}

	/**
	 * 画像のビルダーを追加
	 * @param  string $original_image_url 画像url
	 * @param  string $preview_image_url  サムネイル画像url
	 * @return bool                       成功ならtrue 失敗ならfalse
	 *
	 * 必須条件
	 * HTTPS で始まるurl
	 * JPEG 拡張子
	 *
	 * 画像
	 * 最大画像サイズ：1024×1024
	 * 最大ファイルサイズ：1MB
	 *
	 * サムネイル画像
	 * 最大画像サイズ：240×240
	 * 最大ファイルサイズ：1MB
	 */
	public function add_image_builder($original_image_url,$preview_image_url,$actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		$quick_replys = array();
		if (!empty($actions)) {
			foreach ($actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// 空チェック
		if (!empty($original_image_url) && !empty($preview_image_url)) {
			$this->builder_stok[] = new ImageMessageBuilder($original_image_url, $preview_image_url,$quick_replys);
			return true;
		}else{
			$this->set_error("画像urlとサムネイル画像urlは必須です");
			return false;
		}
	}

	/**
	 * 位置情報のビルダーを追加
	 * @param  string $title   タイトル
	 * @param  string $address 住所
	 * @param  double $lat     緯度(十進数)
	 * @param  double $lon     経度(十進数)
	 * @return bool           成功ならtrue 失敗ならfalse
	 */
	public function add_location_builder($title,$address,$lat,$lon,$actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($actions)) {
			foreach ($actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// 空チェック
		if (!empty($title) && !empty($address)) {
			$this->builder_stok[] = new LocationMessageBuilder($title, $address, $lat, $lon,$quick_replys);
			return true;
		}else{
			$this->set_error("タイトルと住所は必須です");
			return false;
		}
	}

	/**
	 * スタンプのビルダーを追加
	 * @param  int $sticker_id ステッカーid
	 * @param  int $package_id パッケージid
	 * @return bool            成功ならtrue 失敗ならfalse
	 *
	 * ステッカーidとパッケージidはLINEBot公式リファレンス参照
	 */
	public function add_stamp_builder($sticker_id,$package_id,$actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($actions)) {
			foreach ($actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// 空チェック
		if (!empty($package_id) && !empty($sticker_id)) {
			$this->builder_stok[] = new StickerMessageBuilder($package_id, $sticker_id,$quick_replys);
			return true;
		}else{
			$this->set_error("ステッカーidとパッケージidは必須です");
			return false;
		}
	}

	/**
	 * 動画のビルダーを追加
	 * @param  string $original_content_url 動画url
	 * @param  string $preview_image_url    サムネイル画像url
	 * @return bool                         成功ならtrue 失敗ならfalse
	 *
	 * 必須条件
	 * HTTPS で始まるurl
	 *
	 * 動画
	 * 最大長：1分
	 * 最大ファイルサイズ：10MB
	 * 拡張子:mp4
	 *
	 * サムネイル画像
	 * 最大画像サイズ：240×240
	 * 最大ファイルサイズ：1MB
	 */
	public function add_vido_builder($original_content_url,$preview_image_url,$actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($actions)) {
			foreach ($actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// 空チェック
		if (!empty($original_content_url) && !empty($preview_image_url)) {
			$this->builder_stok[] = new VideoMessageBuilder($original_content_url, $preview_image_url,$quick_replys);
			return true;
		}else{
			$this->set_error("動画urlと画像urlは必須です");
			return false;
		}
	}

	/**
	 * 音声のビルダーを追加
	 * @param  string $original_content_url 音声ファイルurl
	 * @param  int    $audio_length         音声ファイルの長さ（ミリ秒）
	 * @return bool                         成功ならtrue 失敗ならfalse
	 *
	 * 必須条件
	 * HTTPS で始まるurl
	 *
	 * 音声ファイル
	 * 最大長：1分
	 * 最大ファイルサイズ：10MB
	 * 拡張子:m4a
	 */
	public function add_audeo_builder($original_content_url,$audio_length,$actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($actions)) {
			foreach ($actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// 空チェック
		if (!empty($original_content_url) && !empty($audio_length)) {
			$this->builder_stok[] = new AudioMessageBuilder($original_content_url, $audio_length,$quick_replys);
			return true;
		}else{
			$this->set_error("音声ファイルurlと音声ファイルの長さは必須です");
			return false;
		}
	}

	/**
	 * ボタンテンプレートのビルダーを追加
	 * @param  string $alternative_text       代替テキスト
	 * @param  string $text                   本文
	 * @param  array  $action_buttons         アクションボタン (create_action_builder()のアクションビルダーの配列 ４つまで)
	 * @param  string $title                  タイトル
	 * @param  string $image_url              画像url
	 * @param  class  $default_action_builder デフォルトアクション(create_action_builder()のアクションビルダー)
	 * @return bool                           成功ならtrue 失敗ならfalse
	 */
	public function add_button_template_builder($alternative_text,$text,$action_buttons,$title="",$image_url="",$default_action_builder="",$quick_reply_actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// 本文が空ならエラー
		if (empty($text)) {
			$this->set_error("本文は必須です");
			return false;
		}

		// エラー対策
		$action_button_array = array();
		foreach ((array)$action_buttons as $key => $value) {
			// アクションクラスじゃなければエラー
			if ($this->check_action_class($value) === false) {
				$this->set_error("アクションビルダーじゃないものが含まれています");
				return false;
			}
			$action_button_array[] = $value;
		}

		// アクションビルダーがないならエラー
		if (count($action_button_array) === 0) {
			$this->set_error("アクションビルダーは必須です");
			return false;
		}

		// アクションビルダーが４つより多いならエラー
		if (count($action_button_array) > 4) {
			$this->set_error("アクションビルダーは4個までです");
			return false;
		}

		// デフォルトアクションが空じゃなくアクションクラスじゃなければエラー
		if (!empty($default_action_builder) && $this->check_action_class($default_action_builder) === false) {
			$this->set_error("アクションビルダーではありません");
			return false;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($quick_reply_actions)) {
			foreach ($quick_reply_actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// ビルダーを追加
		$this->builder_stok[] = new TemplateMessageBuilder($alternative_text,new ButtonTemplateBuilder($title, $text, $image_url, $action_button_array,$default_action_builder),$quick_replys);
		return true;
	}

	/**
	 * 確認テンプレートのビルダーを追加
	 * @param  string $alternative_text 代替テキスト
	 * @param  string $text             本文
	 * @param  array  $action_buttons   アクションボタン (create_action_builder()のアクションビルダーの配列 ２つ)
	 * @return bool                     成功ならtrue 失敗ならfalse
	 */
	public function add_confirm_template_builder($alternative_text,$text,$action_buttons,$quick_reply_actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// 本文が空ならエラー
		if (empty($text)) {
			$this->set_error("本文は必須です");
			return false;
		}
		// ボタンアクション
		$action_button_array = array();
		foreach ($action_buttons as $key => $value) {
			// アクションクラスじゃなければエラー
			if ($this->check_action_class($value) === false) {
				$this->set_error("アクションクラスじゃないものが含まれています");
				return false;
			}
			$action_button_array[] = $value;
		}

		// ボタンアクションが２つじゃなければエラー
		if (count($action_button_array) !== 2) {
			$this->set_error("ボタンアクションは2個でなくてはいけない");
			return false;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($quick_reply_actions)) {
			foreach ($quick_reply_actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// ビルダーを追加
		$this->builder_stok[] = new TemplateMessageBuilder($alternative_text,new ConfirmTemplateBuilder($text, $action_button_array),$quick_replys);
		return true;
	}

	/**
	 * カルーセルテンプレートのカラムビルダーを作成
	 * @param  string $text                   本文
	 * @param  array  $action_buttons         アクションボタン(create_action_builder()のアクションビルダーの配列 1~3つ)
	 * @param  string $title                  タイトル
	 * @param  string $image_url              画像url
	 * @param  class  $default_action_builder デフォルトアクション(create_action_builder()のアクションビルダー)
	 * @return bool                           成功ならカラムビルダーのインスタンス 失敗ならfalse
	 */
	public function create_carousel_column_template_builder($text,$action_buttons,$title="",$image_url="",$default_action_builder="")
	{
		// 本文が空ならエラー
		if (empty($text)) {
			$this->set_error("本文は必須です");
			return false;
		}

		// ボタンアクション
		$action_button_array = array();
		foreach ($action_buttons as $key => $value) {
			// アクションクラスじゃなければエラー
			if ($this->check_action_class($value) === false) {
				$this->set_error("アクションビルダーじゃないものが含まれています");
				return false;
			}
			$action_button_array[] = $value;
		}

		// アクションビルダーがないならエラー
		if (count($action_button_array) === 0) {
			$this->set_error("アクションビルダーは必須です");
			return false;
		}

		// アクションビルダーが3つより多いならエラー
		if (count($action_button_array) > 3) {
			$this->set_error("アクションビルダーは3個までです");
			return false;
		}

		// デフォルトアクションが空じゃなくアクションクラスじゃなければエラー
		if (!empty($default_action_builder) && $this->check_action_class($default_action_builder) === false) {
			$this->set_error("アクションビルダーではありません");
			return false;
		}

		return new CarouselColumnTemplateBuilder($title,$text,$image_url,$action_button_array,$default_action_builder);
	}

	/**
	 * カルーセルテンプレートを追加
	 * @param string $alternative_text         代替テキスト
	 * @param array  $column_template_builders カラムビルダー (create_carousel_column_template_builder()の配列 1~10つまで)
	 */
	public function add_carousel_template_builder($alternative_text,$column_template_builders,$quick_reply_actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// カラムビルダーチェック
		$column_template_builder_array = array();
		foreach ((array)$column_template_builders as $key => $value) {
			if ($value instanceof CarouselColumnTemplateBuilder) {
				$column_template_builder_array[] = $value;
			}else{
				$this->set_error("カラムビルダーじゃないものが含まれています");
				return false;
			}
		}

		// カラムビルダーがないならエラー
		if (count($column_template_builder_array) === 0) {
			$this->set_error("カラムビルダーは必須です");
			return false;
		}
		// カラムビルダーが10個より多いならエラー
		if (count($column_template_builder_array) > 10) {
			$this->set_error("カラムビルダーは10個までです");
			return false;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($quick_reply_actions)) {
			foreach ($quick_reply_actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// ビルダーを追加
		$this->builder_stok[] = new TemplateMessageBuilder($alternative_text,new CarouselTemplateBuilder($column_template_builder_array),$quick_replys);
		return true;
	}

	/**
	 * イメージカルーセルテンプレートのカラムビルダー作成
	 * @param  string $image_url      画像url
	 * @param  class  $action_builder アクション (create_action_builder()のアクションビルダー)
	 * @return                        成功ならカラムビルダーのインスタンス 失敗ならfalse
	 */
	public function create_image_column_template_builder($image_url,$action_builder)
	{
		// 空ならエラー
		if (empty($image_url)) {
			$this->set_error("画像rulは必須です");
			return false;
		}

		// 空ならエラー
		if (empty($action_builder)) {
			$this->set_error("アクションビルダーは必須です");
			return false;
		}

		// アクションクラスじゃなければエラー
		if ($this->check_action_class($action_builder) === false) {
			$this->set_error("アクションクラスじゃありません");
			return false;
		}

		// イメージカラム作成
		return new ImageCarouselColumnTemplateBuilder($image_url,$action_builder);
	}

	/**
	 * イメージカルーセルテンプレートの追加
	 * @param string $alternative_text      代替テキスト
	 * @param array  $image_column_builders カラムビルダー (create_image_column_template_builder()の配列 1~10個まで)
	 */
	public function add_image_carousel_template_builder($alternative_text,$image_column_builders,$quick_reply_actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// イメージカラムビルダーチェック
		$image_column_builder_array = array();
		foreach ((array)$image_column_builders as $key => $value) {
			// イメージカラムビルダーのクラスかチェック
			if ($value instanceof ImageCarouselColumnTemplateBuilder) {
				$image_column_builder_array[] = $value;
			}else{
				$this->set_error("イメージカラムビルダーじゃないものが含まれています");
				return false;
			}
		}

		// イメージカラムがないならエラー
		if (count($image_column_builder_array) === 0) {
			$this->set_error("イメージカラムは必須です");
			return false;
		}

		// イメージカラムが10個より多いならエラー
		if (count($image_column_builder_array) > 10) {
			$this->set_error("イメージカラムは10個までです");
			return false;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($quick_reply_actions)) {
			foreach ($quick_reply_actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// ビルダーを追加
		$this->builder_stok[] = new TemplateMessageBuilder($alternative_text,new ImageCarouselTemplateBuilder($image_column_builder_array),$quick_replys);
		return true;
	}

	/**
	 * イメージマップのアクションエリアビルダーの作成
	 * @param  int    $x           アクションエリア支点のx座標
	 * @param  int    $y           アクションエリア支点のｙ座標
	 * @param  int    $width       アクションエリアの幅
	 * @param  int    $height      アクションエリアの高さ
	 * @param  string $action_type アクションタイプ text url
	 * @param  string $content     アクションした時に使用するデータ
	 * @return                     成功ならアクションエリアビルダーのインスタンス 失敗ならfalse
	 */
	public function create_imagemap_action_area_builder(int $x,int $y,int $width,int $height,$action_type,$content)
	{
		// エリアビルダーを作成
		$area_builder = new AreaBuilder($x,$y,$width,$height);

		// アクションタイプをチェック
		if ($action_type !== "text" && $action_type !== "url") {
			$this->set_error("存在しないアクションタイプです");
			return false;
		}

		// アクションタイプを判別
		switch ($action_type) {
			case 'text':
				return new ImagemapMessageActionBuilder($content,$area_builder);
				break;
			case 'url':
				return new ImagemapUriActionBuilder($content,$area_builder);
				break;
			
			default:
				$this->set_error("存在しないアクションタイプです");
				return false;
				break;
		}
	}

	/**
	 * イメージマップのビルダーを追加
	 * @param string $alternative_text     代替テキスト
	 * @param string $image_base_url       画像ベースurl
	 * @param int    $width                画像の高さ
	 * @param array  $action_area_builders アクションエリアビルダー (create_imagemap_action_area_builder()の配列 50個まで)
	 */
	public function add_imagemap_buildr($alternative_text,$image_base_url,int $width,$action_area_builders,$quick_reply_actions=array())
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// 画像ベースurlが空ならエラー
		if (empty($image_base_url)) {
			$this->set_error("画像ベースurlは必須です");
			return false;
		}

		// アクションエリアビルダーのチェック
		$action_area_builder_array = array();
		foreach ($action_area_builders as $key => $value) {
			if ($this->check_action_area_class($value)) {
				$action_area_builder_array[] = $value;
			}else{
				$this->set_error("アクションエリアビルダーではないものが含まれています");
				return false;
			}
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($quick_reply_actions)) {
			foreach ($quick_reply_actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// ベースサイズビルダーを作成
		$base_size_builder = new BaseSizeBuilder(1040,$width);

		// ビルダーを追加
		$this->builder_stok[] = new ImagemapMessageBuilder($image_base_url,$alternative_text,$base_size_builder,$action_area_builder_array,$quick_replys);
		return true;
	}

	/**
	 * flexのテキストコンポーネントを作成
	 * @param  [type]  $text    テキスト
	 * @param  [array] $options オプション
	 * @param  [key]    flex    親ボックス内での、このコンポーネントの幅または高さの比率。
	 *                         水平ボックス内でのデフォルト値は1、垂直ボックス内でのデフォルト値は0
	 *
	 * @param  [key]    margin  親ボックス内での、このコンポーネントと前のコンポーネントの間の最小スペース。
	 *                         1~7のいずれかの値を指定できます。
	 *                         1ではスペースが設定されず、それ以外は列挙した順にサイズが大きくなります。
	 *                         デフォルト値は親ボックスのspacingプロパティの値です。
	 *                         このコンポーネントが先頭である場合、marginプロパティを設定しても無視されます。
	 *
	 * @param  [key]    size    フォントサイズ。
	 *                         1~10のいずれかの値を指定できます。
	 *                         列挙した順にサイズが大きくなります。デフォルト値は4です。
	 *
	 * @param  [key]    align   水平方向の配置スタイル。以下のいずれかの値を指定します。
	 *                          start：左揃え
	 *                          end：右揃え
	 *                          center：中央揃え
	 *                         デフォルト値はstartです。
	 *
	 * @param  [key]    gravity 垂直方向の配置スタイル。以下のいずれかの値を指定します。
	 *                          top：上揃え
	 *                          bottom：下揃え
	 *                          center：中央揃え
	 *                          デフォルト値はtopです。
	 *                         親ボックスのlayoutプロパティがbaselineの場合、gravityプロパティを設定しても無視されます。
	 *
	 * @param  [key]    wrap    文字列を折り返すかどうかを指定します。
	 *                          デフォルト値はfalseです。trueに設定した場合、改行文字（\n）を使って改行できます。
	 *
	 * @param  [key]    weight  フォントの太さ。regular、boldのいずれかの値を指定できます
	 *                          boldを指定すると太字になります。デフォルト値はregularです。
	 *
	 * @param  [key]    color   フォントの色。16進数カラーコードで設定します。
	 * @param  [key]    action  タップされたときのアクション。アクションオブジェクトを指定します。
	 * @return [type]          TextBuilder
	 */
	public function create_text_component($text,$options=array())
	{
		$flex    = !empty($options['flex'])    ? $options['flex'] : null;
		$margin  = !empty($options['margin'])  ? $options['margin'] : null;
		$size    = !empty($options['size'])    ? $options['size'] : null;
		$align   = !empty($options['align'])   ? $options['align'] : null;
		$gravity = !empty($options['gravity']) ? $options['gravity'] : null;
		$wrap    = !empty($options['wrap'])    ? $options['wrap'] : null;
		$weight  = !empty($options['weight'])  ? $options['weight'] : null;
		$color   = !empty($options['color'])   ? $options['color'] : null;
		$action  = !empty($options['action'])  ? $options['action'] : null;

		return new TextBuilder($text,$flex,$margin,$size,$align,$gravity,$wrap,$weight,$color,$action);
	}

	/**
	 * flexのボタンコンポーネントを作成
	 * @param  [array] $action タップされたときのアクション。アクションオブジェクトを指定します
	 * @param  [key]   flex    親ボックス内での、このコンポーネントの幅または高さの比率
	 *                         水平ボックス内でのデフォルト値は1、垂直ボックス内でのデフォルト値は0です
	 *
	 * @param  [key]   margin  親ボックス内での、このコンポーネントと前のコンポーネントの間の最小スペース
	 *                         1~7のいずれかの値を指定できます
	 *                         7ではスペースが設定されず、それ以外は列挙した順にサイズが大きくなります
	 *                         デフォルト値は親ボックスのspacingプロパティの値です
	 *                         このコンポーネントが先頭である場合、marginプロパティを設定しても無視されます
	 *
	 * @param  [key]   height  ボタンの高さ。smまたはmdのいずれかの値を指定できます デフォルト値はmdです
	 * 
	 * @param  [key]   style   ボタンの表示形式。以下のいずれかの値を指定します
	 *                         link：HTMLのリンクのスタイル
	 *                         primary：濃色のボタン向けのスタイル
	 *                         secondary：淡色のボタン向けのスタイル
	 *                         デフォルト値はlinkです
	 *
	 * @param  [key]   color   styleプロパティがlinkの場合は文字の色、primaryまたはsecondaryの場合は背景色です
	 *                         16進数カラーコードで設定します
	 *
	 * @param  [key]   gravity 垂直方向の配置スタイル。以下のいずれかの値を指定します。
	 *                         top：上揃え
	 *                         bottom：下揃え
	 *                         center：中央揃え
	 *                         デフォルト値はtopです。
	 *                         親ボックスのlayoutプロパティがbaselineの場合、gravityプロパティを設定しても無視されます
	 *                         
	 * @return [type]          ButtonBuilder
	 */
	public function create_button_component($action,$options=array())
	{
		$flex    = !empty($options['flex'])    ? $options['flex'] : null;
		$margin  = !empty($options['margin'])  ? $options['margin'] : null;
		$height  = !empty($options['height'])  ? $options['height'] : null;
		$style   = !empty($options['style'])   ? $options['style'] : null;
		$color   = !empty($options['color'])   ? $options['color'] : null;
		$gravity = !empty($options['gravity']) ? $options['gravity'] : null;

		return new ButtonBuilder($action,$flex,$margin,$height,$style,$color,$gravity);
	}

	/**
	 * flexのボックスコンポーネントを作成
	 * @param  [type] $layout   このボックス内のコンポーネントの配置スタイル。以下のいずれかの値を指定します。
	 *                          horizontal：コンポーネントを水平に配置します。
	 *                          並び順はバブルコンテナのdirectionプロパティで指定します。
	 *                          vertical：コンポーネントを上から下に向かって垂直に配置します。
	 *                          baseline：horizontalを指定した場合と同様にコンポーネントを配置します。
	 *                          ただし、各コンポーネントのベースラインを揃えて配置する点が異なります。
	 *
	 * @param  [type] $contents このボックス内のコンポーネント。以下のコンポーネントを指定できます。
	 *                          layoutプロパティがhorizontalまたはverticalの場合：
	 *                            ボックス、ボタン、フィラー、画像、セパレータ、およびテキストコンポーネント
	 *                          layoutプロパティがbaselineの場合：
	 *                            フィラー、アイコン、およびテキストコンポーネント
	 *
	 * @param  [array] $options オプション
	 * @param  [key]    flex    親ボックス内での、このボックスの幅または高さの比率。
	 *                          水平ボックス内でのデフォルト値は1、垂直ボックス内でのデフォルト値は0です
	 *
	 * @param  [key]    spacing このボックス内のコンポーネントの間の最小スペース。
	 *                          1~7のいずれかの値を指定できます。
	 *                          1ではスペースが設定されず、それ以外は列挙した順にサイズが大きくなります。
	 *                          デフォルト値は1です。
	 *                          特定のコンポーネントについてこの設定を上書きするには、そのコンポーネントでmarginプロパティを設定します
	 *
	 * @param  [key]    margin  親ボックス内での、このボックスと前のコンポーネントの間の最小スペース。
	 *                          1~7のいずれかの値を指定できます。1ではスペースが設定されず、それ以外は列挙した順にサイズが大きくなります。
	 *                          デフォルト値は親ボックスのspacingプロパティの値です。
	 *                          このボックスが先頭である場合、marginプロパティを設定しても無視されます
	 *
	 * @return [type]           BoxBuilder
	 */
	public function create_box_component($layout,$contents,$options=array())
	{
		$flex    = !empty($options['flex'])    ? $options['flex'] : null;
		$spacing = !empty($options['spacing']) ? $options['spacing'] : null;
		$margin  = !empty($options['margin'])  ? $options['margin'] : null;

		return new BoxBuilder($layout,$contents,$flex,$spacing,$margin);
	}

	/**
	 * 画像コンポーネントを作成
	 * @param  [type]  $url             画像のURL
	 *                                  プロトコル：HTTPS
	 *                                  画像フォーマット：JPEGまたはPNG
	 *                                  最大画像サイズ：1024×1024px
	 *                                  最大データサイズ：1MB
	 *                                  
	 * @param  [array] $options         オプション
	 * @param   [key]   margin          親ボックス内での、このボックスと前のコンポーネントの間の最小スペース。
	 *                                  1~7のいずれかの値を指定できます
	 *                                  1ではスペースが設定されず、それ以外は列挙した順にサイズが大きくなります。
	 *                                  デフォルト値は親ボックスのspacingプロパティの値です。
	 *                                  このボックスが先頭である場合、marginプロパティを設定しても無視されます
	 *                                  
	 * @param   [key]   flex            親ボックス内での、このコンポーネントの幅または高さの比率
	 *                                  水平ボックス内でのデフォルト値は1、垂直ボックス内でのデフォルト値は0です
	 *                                  
	 * @param   [key]   align           水平方向の配置スタイル。以下のいずれかの値を指定します。
	 *                                  start：左揃え
	 *                                  end：右揃え
	 *                                  center：中央揃え
	 *                                  デフォルト値はcenterです
	 *                                  
	 * @param   [key]   gravity         垂直方向の配置スタイル。以下のいずれかの値を指定します
	 *                                  top：上揃え
	 *                                  bottom：下揃え
	 *                                  center：中央揃え
	 *                                  デフォルト値はtopです
	 *                                  親ボックスのlayoutプロパティがbaselineの場合、gravityプロパティを設定しても無視されます
	 *                                  
	 * @param   [key]   size            画像の幅の最大サイズ
	 *                                  1~11のいずれかの値を指定できます
	 *                                  列挙した順にサイズが大きくなります。デフォルト値は1です
	 *                                  
	 * @param   [key]   aspectRatio     画像のアスペクト比
	 *                                  1:1
	 *                                  1.51:1
	 *                                  1.91:1
	 *                                  4:3
	 *                                  16:9
	 *                                  20:13
	 *                                  2:1
	 *                                  3:1
	 *                                  3:4
	 *                                  9:16
	 *                                  1:2
	 *                                  1:3
	 *                                  のいずれかの値を指定できます。デフォルト値は1:1です
	 *                                  
	 * @param   [key]   aspectMode      画像の表示形式。以下のいずれかの値を指定します。
	 *                                  cover：描画領域全体に画像を表示します
	 *                                  描画領域に収まらない部分は切り詰められます
	 *                                  fit：描画領域に画像全体を表示します
	 *                                  縦長の画像では左右に、横長の画像では上下に余白が表示されます
	 *                                  デフォルト値はfitです
	 *                                  
	 * @param   [key]   backgroundColor 画像の背景色。16進数カラーコードで設定します
	 * @param   [key]   action          タップされたときのアクション。アクションオブジェクトを指定します
	 * @return 
	 */
	public function create_image_component($url,$options=array())
	{
		$margin          = !empty($options['margin'])          ? $options['margin'] : null;
		$flex            = !empty($options['flex'])            ? $options['flex'] : null;
		$align           = !empty($options['align'])           ? $options['align'] : null;
		$gravity         = !empty($options['gravity'])         ? $options['gravity'] : null;
		$size            = !empty($options['size'])            ? $options['size'] : null;
		$aspectRatio     = !empty($options['aspectRatio'])     ? $options['aspectRatio'] : null;
		$aspectMode      = !empty($options['aspectMode'])      ? $options['aspectMode'] : null;
		$backgroundColor = !empty($options['backgroundColor']) ? $options['backgroundColor'] : null;
		$action          = !empty($options['action'])          ? $options['action'] : null;

		return new ImageBuilder($url,$margin,$flex,$align,$gravity,$size,$aspectRatio,$aspectMode,$backgroundColor,$action);
	}

	/**
	 * セパレータコンポーネント
	 * 親ボックス内のコンポーネントの間に境界線を描画するコンポーネントです
	 * @param  [type] $margin 親ボックス内での、このコンポーネントと前のコンポーネントの間の最小スペース。
	 *                        1~7のいずれかの値を指定できます
	 *                        1ではスペースが設定されず、それ以外は列挙した順にサイズが大きくなります
	 *                        デフォルト値は親ボックスのspacingプロパティの値です。
	 *                        このコンポーネントが先頭である場合、marginプロパティを設定しても無視されます
	 *
	 * @param  [type] $color  セパレータの色。16進数カラーコードで設定します
	 * @return [type]         SeparatorBuilder
	 */
	public function create_separator_container($options=array())
	{
		$margin = !empty($options['margin']) ? $options['margin'] : null;
		$color  = !empty($options['color'])  ? $options['color'] : null;
		return new SeparatorBuilder($margin,$color);
	}

	/**
	 * バブルコンテナ
	 * 1つのメッセージバブルを構成するコンテナです。ヘッダー、ヒーロー、ボディ、およびフッターの4つのブロックを含めることができます
	 *                           
	 * @param  [array] $blocks    各要素のブロック
	 * @param  [key]    header    ヘッダーブロック。ボックスコンポーネントを指定します。
	 * @param  [key]    hero      ヒーローブロック。画像コンポーネントを指定します。
	 * @param  [key]    body      ボディブロック。ボックスコンポーネントを指定します。
	 * @param  [key]    footer    フッターブロック。ボックスコンポーネントを指定します
	 * @param  [key]    styles    各ブロックのスタイル。バブルスタイルオブジェクトを指定します
	 * @param  [key]    direction テキストの書字方向および水平ボックス内のコンポーネントの並び順。
	 *                            以下のいずれかの値を指定します。
	 *                            ltr：左から右
	 *                            rtl：右から左
	 *                           デフォルト値はltrです
	 * @return [type]            BubbleBuilder
	 */
	public function create_bubble_container($blocks,$direction=null)
	{
		$header = !empty($blocks['header']) ? $blocks['header'] : null;
		$hero   = !empty($blocks['hero'])   ? $blocks['hero'] : null;
		$body   = !empty($blocks['body'])   ? $blocks['body'] : null;
		$footer = !empty($blocks['footer']) ? $blocks['footer'] : null;
		$styles = !empty($blocks['styles']) ? $blocks['styles'] : null;

		return new BubbleBuilder($direction,$header,$hero,$body,$footer,$styles);
	}

	/**
	 * Flex Messageは、複数の要素を組み合わせてレイアウトを自由にカスタマイズできるメッセージです
	 * @param [type] $altText 代替テキスト 最大文字数：400
	 * @param [type] $bubbles Flex Messageのコンテナオブジェクト
	 */
	public function add_flex_builder($altText,$bubbles,$quick_reply_actions=array())
	{
		$bubbles_array = array();
		foreach ((array)$bubbles as $key => $value) {
			$bubbles_array[] = $value;
		}

		// クイックリプライのアクション
		$quick_replys = array();
		if (!empty($quick_reply_actions)) {
			foreach ($quick_reply_actions as $key => $value) {
				if ($this->check_action_class($value["action"])) {
					$quick_replys[] = $value;
				}else{
					$this->set_error("アクションビルダーじゃないものが含まれています");
					return false;
				}
			}
		}

		// ビルダーを追加
		$this->builder_stok[] = new FlexMessageBuilder($altText,new ContentsBuilder($bubbles_array),$quick_replys);
	}

	/**
	 * テキストアクションを作成
	 * @param  string $label ラベル
	 * @param  string $text  テキスト
	 * @return class         テキストアクション
	 */
	public function create_text_action_builder($label,$text)
	{
		// 空チェック
		if (empty($text)) {
			$this->set_error("テキストは必須です");
			return false;
		}
		// ラベルが空なら
		if (empty($label)) {
			$label = null;
		}
		// テキストアクションを返す
		return $this->create_action_builder("text",$label,["text"=>$text]);
	}

	/**
	 * postアクションを作成
	 * @param  string $label ラベル
	 * @param  string $post  postする値
	 * @param  string $text  アクションしたときに表示するテキスト
	 * @return class         postアクション
	 */
	public function create_post_action_builder($label,$post,$text=null)
	{
		// 空チェック
		if (empty($post)) {
			$this->set_error("postは必須です");
			return false;
		}
		// ラベルが空なら
		if (empty($label)) {
			$label = null;
		}
		return $this->create_action_builder("post",$label,["post"=>$post,"text"=>$text]);
	}

	/**
	 * urlアクションを作成
	 * @param  string  $label                 ラベル
	 * @param  string  $url                   url
	 * @param  boolean $external_browser_flag trueなら外部ブラウザで開く falseならlineブラウザで開く
	 * @return class                          urlアクション
	 */
	public function create_url_action_builder($label,$url="",$external_browser_flag=false)
	{
		// 空チェック
		if (empty($url)) {
			$this->set_error("urlは必須です");
			return false;
		}
		// ラベルが空なら
		if (empty($label)) {
			$label = null;
		}
		// 外部ブラウザで開くか
		if ($external_browser_flag) {
			$url .= strpos($url,'?') !== false ? "&" : "?";
			$url .= "openExternalBrowser=1";
		}
		return $this->create_action_builder("url",$label,["url"=>$url]);
	}

	/**
	 * dateアクションを作成
	 * @param  string $label     ラベル
	 * @param  string $post      postするあたい
	 * @param  string $date_mode dateのタイプ date time datetime
	 * @param  array  $options   オプションの連想配列
	 * @return [type]            dateアクション
	 */
	public function create_date_action_builder($label,$post,$date_mode,$options=array())
	{
		// 空チェック
		if (empty($post)) {
			$this->set_error("postは必須です");
			return false;
		}
		if (empty($date_mode)) {
			$this->set_error("date_modeは必須です");
			return false;
		}
		// ラベルが空なら
		if (empty($label)) {
			$label = null;
		}
		// オプションのチェック
		$initial   = !empty($options['initial'])   ? $initial : null;
		$limit_max = !empty($options['limit_max']) ? $limit_max : null;
		$limit_min = !empty($options['limit_min']) ? $limit_min : null;

		return $this->create_action_builder("date",$label,["post"=>$post],"datetime",$initial,$limit_max,$limit_min);
	}

	/**
	 * クイックリプライのテキストアクションを作成
	 * @param  string $label ラベル
	 * @param  string $text  テキスト
	 * @return class         テキストアクション
	 */
	public function create_quick_text_action($label,$text,$icon_url="")
	{
		// 空チェック
		if (empty($text)) {
			$this->set_error("テキストは必須です");
			return false;
		}
		// ラベルが空なら
		if (empty($label)) {
			$this->set_error("ラベルは必須です");
			return false;
		}
		// テキストアクションを返す
		return ["action" => $this->create_action_builder("text",$label,["text"=>$text]),"icon" => $icon_url];
	}

	/**
	 * クイックリプライのpostアクションを作成
	 * @param  string $label ラベル
	 * @param  string $post  postする値
	 * @param  string $text  アクションしたときに表示するテキスト
	 * @return class         postアクション
	 */
	public function create_quick_post_action($label,$post,$text=null,$icon_url="")
	{
		// 空チェック
		if (empty($post)) {
			$this->set_error("postは必須です");
			return false;
		}
		// ラベルが空なら
		if (empty($label)) {
			$this->set_error("ラベルは必須です");
			return false;
		}
		return ["action" => $this->create_action_builder("post",$label,["post"=>$post,"text"=>$text]),"icon" => $icon_url];
	}

	/**
	 * クイックリプライのdateアクションを作成
	 * @param  string $label     ラベル
	 * @param  string $post      postするあたい
	 * @param  string $date_mode dateのタイプ date time datetime
	 * @param  array  $options   オプションの連想配列
	 * @return [type]            dateアクション
	 */
	public function create_quick_date_action($label,$post,$date_mode,$icon_url="",$options=array())
	{
		// 空チェック
		if (empty($post)) {
			$this->set_error("postは必須です");
			return false;
		}
		if (empty($date_mode)) {
			$this->set_error("date_modeは必須です");
			return false;
		}
		// ラベルが空なら
		if (empty($label)) {
			$this->set_error("ラベルは必須です");
			return false;
		}
		// オプションのチェック
		$initial   = !empty($options['initial'])   ? $initial : null;
		$limit_max = !empty($options['limit_max']) ? $limit_max : null;
		$limit_min = !empty($options['limit_min']) ? $limit_min : null;

		return ["action" => $this->create_action_builder("date",$label,["post"=>$post],"datetime",$initial,$limit_max,$limit_min),"icon" => $icon_url];
	}

	/**
	 * クイックリプライのカメラアクションを作成
	 * @param  string $label ラベル
	 * @param  string $text  テキスト
	 * @return class         テキストアクション
	 */
	public function create_quick_camera_action($label,$icon_url="")
	{
		// ラベルが空なら
		if (empty($label)) {
			$this->set_error("ラベルは必須です");
			return false;
		}
		// テキストアクションを返す
		return ["action" => new CameraTemplateActionBuilder($label),"icon" => $icon_url];
	}

	/**
	 * クイックリプライのカメラロールアクションを作成
	 * @param  string $label ラベル
	 * @param  string $text  テキスト
	 * @return class         テキストアクション
	 */
	public function create_quick_camera_roll_action($label,$icon_url="")
	{
		// ラベルが空なら
		if (empty($label)) {
			$this->set_error("ラベルは必須です");
			return false;
		}
		// テキストアクションを返す
		return ["action" => new CameraRollTemplateActionBuilder($label),"icon" => $icon_url];
	}

	/**
	 * クイックリプライの位置情報アクションを作成
	 * @param  string $label ラベル
	 * @param  string $text  テキスト
	 * @return class         テキストアクション
	 */
	public function create_quick_location_action($label,$icon_url="")
	{
		// ラベルが空なら
		if (empty($label)) {
			$this->set_error("ラベルは必須です");
			return false;
		}
		// テキストアクションを返す
		return ["action" => new LocationTemplateActionBuilder($label),"icon" => $icon_url];
	}

	/**
	 * アクションのビルダーを作成
	 * @param  string $action_type アクションタイプ  text post url date
	 * @param  string $label       表示するテキスト
	 * @param  array  $content     アクションした時に使用するデータ
	 * @param  string $date_mode   アクションタイプがdateの時、必須 date time datetime
	 * @param  string $initial     アクションタイプがdateの時 日時の初期値
	 * @param  string $limit_max   アクションタイプがdateの時 日時の上限
	 * @param  string $limit_min   アクションタイプがdateの時 日時の下限
	 * @return                     各アクションタイプのビルダークラス 失敗時はfalse
	 */
	public function create_action_builder($action_type,$label,$content,$date_mode="",$initial="",$limit_max="",$limit_min="")
	{
		// アクションタイプが空ならエラー
		if (empty($action_type)) {
			$this->set_error("アクションタイプは必須です");
			return false;
		}

		// ラベルが空なら
		if (empty($label)) {
			$label = null;
		}

		// 空ならnull
		$post = !empty($content['post']) ? $content['post'] : null;
		$text = !empty($content['text']) ? $content['text'] : null;
		$url  = !empty($content['url']) ? $content['url'] : null;

		// アクションタイプ判別
		switch ($action_type) {
			case 'text':
				return new MessageTemplateActionBuilder($label,$text);
				break;
			case 'post':
				return new PostbackTemplateActionBuilder($label,$post,$text);
				break;
			case 'url':
				return new UriTemplateActionBuilder($label,$url);
				break;
			case 'date':
				if (empty($date_mode)) {
					$this->set_error("アクションタイプがdateの時、date_modeは必須です");
					return false;
				}
				// date_modeが正しいかチェック
				if ($date_mode !== "date" && $date_mode !== "time" && $date_mode !== "datetime") {
					$this->set_error("存在しないdate_modeです");
					return false;
				}
				return new DatetimePickerTemplateActionBuilder($label,$post,$date_mode,$initial,$limit_max,$limit_min);
				break;
			default:
				$this->set_error("存在しないアクションタイプです");
				return false;
				break;
		}
	}

	/**
	 * 返信を実行
	 * @return  成功ならtrue 失敗ならfalse
	 *
	 * 返信は一度しか行えません
	 */
	public function reply()
	{
		if (count($this->builder_stok) > 0) {
			$builder = new MultiMessageBuilder();
			foreach ($this->builder_stok as $key => $row) {
				$builder -> add($row);
			}
			$response = $this -> replyMessage($this->reply_token, $builder);
			if ($response -> isSucceeded() == false) {
				error_log("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				$this->set_error("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				return false;
			}else{
				return true;
			}
		}else{
			$this->set_error("返信するビルダーがありません");
			return false;
		}
	}

	/**
	 * 送信先を複数指定して個人にメッセージを送信
	 * @param  array  $ids 送信先idの配列
	 * @return bool        成功ならtrue 失敗ならfalse
	 *
	 * イベントオブジェクトで返される、userId、groupId、またはroomIdの値を使用して下さい
	 * LINEアプリに表示されるLINE IDは使用しないでください
	 *
	 * 送信先を複数指定出来ますがその場合、groupIｄまたはroomIdを指定しないで下さい
	 */
	public function push_user_multi($ids)
	{
		$id_array = array();
		foreach ((array)$ids as $key => $value) {
			if (!empty($value)) {
				$id_array[] = $value;
			}
		}

		// 送信先がないならエラー
		if (empty($id_array)) {
			$this->set_error("送信先は必須です");
			return false;
		}

		if (count($this->builder_stok) > 0) {
			$builder = new MultiMessageBuilder();
			foreach ($this->builder_stok as $key => $row) {
				$builder -> add($row);
			}
			$response = $this -> multicast($id_array, $builder);
			if ($response -> isSucceeded() == false) {
				error_log("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				$this->set_error("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				return false;
			}else{
				return true;
			}
		}else{
			$this->set_error("送信するビルダーがありません");
			return false;
		}
	}

	/**
	 * 送信先を指定してメッセージを送る
	 * @param  [type] $to_id 送信先id
	 * @return [type]        [description]
	 */
	public function push($to_id)
	{
		// 送信先がなければエラー
		if (empty($to_id)) {
			$this->set_error("送信先は必須です");
			return false;
		}

		if (count($this->builder_stok) > 0) {
			$builder = new MultiMessageBuilder();
			foreach ($this->builder_stok as $key => $row) {
				$builder -> add($row);
			}
			$response = $this -> pushMessage($to_id, $builder);
			if ($response -> isSucceeded() == false) {
				error_log("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				$this->set_error("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				return false;
			}else{
				return true;
			}
		}else{
			$this->set_error("送信するビルダーがありません");
			return false;
		}
	}

	/**
	 * ビルダーストックを削除(初期化)
	 * @param  string $delete_type 何も指定しなければ全て削除 lastを指定すれば最後の要素を削除
	 * @return 
	 */
	public function delete_builder_stok($delete_type="default")
	{
		if ($delete_type === "default") {
			$this->builder_stok = array();
		}

		if ($delete_type === "last") {
			array_pop($this->builder_stok);
		}
	}

	/**
	 * 返信するjsonオブジェクトを取得
	 * @return json 
	 */
	public function get_reply_json_data()
	{
		$builder = new MultiMessageBuilder();
		foreach ($this->builder_stok as $key => $row) {
			$builder -> add($row);
		}

		$data = array();
		$data['replyToken'] = $this->reply_token;
		$data['messages'] = $builder->buildMessage();
		$json_data = json_encode($data);
		
		return $json_data;
	}

	/**
	 * テキストメッセージを返信
	 * @param  string $text 返信するテキスト
	 * @return bool         成功ならtrue 失敗ならfalse
	 *
	 * 返信は一度しか行えません
	 */
	public function reply_text($text)
	{
		// 返信とそのレスポンス
		$response = $this -> replyMessage($this->reply_token, new TextMessageBuilder($text));
		if ($response -> isSucceeded() == false) {
			error_log("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
			$this->set_error("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
			return false;
		}else{
			return true;
		}
	}

	/**
	 * create_action_builder()で作成されるアクションクラスかチェック
	 * @param         $action_class チェックする変数
	 * @return bool   アクションクラスならtrue 違うならfalse
	 */
	public function check_action_class($action_class)
	{
		// textactionのclass
		if ($action_class instanceof MessageTemplateActionBuilder) {
			return true;
		}
		// postactionのclass
		if ($action_class instanceof PostbackTemplateActionBuilder) {
			return true;
		}
		// urlactionのclass
		if ($action_class instanceof UriTemplateActionBuilder) {
			return true;
		}
		// urlactionのclass
		if ($action_class instanceof DatetimePickerTemplateActionBuilder) {
			return true;
		}
		// cameraactionのclass
		if ($action_class instanceof CameraTemplateActionBuilder) {
			return true;
		}
		// camera_rollactionのclass
		if ($action_class instanceof CameraRollTemplateActionBuilder) {
			return true;
		}
		// locationactionのclass
		if ($action_class instanceof LocationTemplateActionBuilder) {
			return true;
		}
		// どれにも当てはまらないならfalse
		return false;
	}

	/**
	 * アクションエリアビルダーのクラスかチェック
	 * @param       $action_class アクションクラス
	 * @return bool               アクションエリアビルダーのクラスならtrue 違うならfalse
	 */
	public function check_action_area_class($action_class)
	{
		// textactionのclass
		if ($action_class instanceof ImagemapMessageActionBuilder) {
			return true;
		}
		// postactionのclass
		if ($action_class instanceof ImagemapUriActionBuilder) {
			return true;
		}
		
		// どれにも当てはまらないならfalse
		return false;
	}

	/**
	 * エラーメッセージを追加する
	 * @param string $error_message エラーメッセージ
	 */
	private function set_error($error_message)
	{
		$this->error_stok[] = $error_message;
	}

	/**
	 * エラーメッセージを取得
	 * @return array エラーメッセージの配列
	 */
	public function get_error()
	{
		return $this->error_stok;
	}



	public function upload_image_richmenu($rich_menu_id,$file_path)
	{
		
	}

	public function get_rich_menu_list()
	{
		$result = $this->getRichMenuList();
		if ($result->isSucceeded()) {
			return $result->getJSONDecodedBody();
		}else{
			$this->set_error("取得できません");
			return false;
		}
	}


	public function join_rich_menu($rich_menu_id,$user_id)
	{
		return $this->joinRichmenu($rich_menu_id,$user_id);
	}
}
?>