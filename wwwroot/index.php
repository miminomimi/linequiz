<?php
// callback.php
define ( "LINE_MESSAGING_API_CHANNEL_SECRET", '59a9cffd5d1c24b30b3b4c0217bb137d' );
define ( "LINE_MESSAGING_API_CHANNEL_TOKEN", 'KBhKorlCkHfHG8UbWJNZQThpZqDjjce30bsBmpUW/AupyjpN2JGy73g5DkCJxt05LzJjs/E6+t5cKnC+opWA32tT21CWAH7WiqNoa9F5siBzm7s8iAOeWBa2+jpq4rlU9Q01vvwCWDwBEU6NrCe+4gdB04t89/1O/w1cDnyilFU=' );

require __DIR__ . "/../vendor/autoload.php";

require_once ("EnjoyQuiz.php");
require_once ("MakeQuiz.php");
require_once ("UpdateQuiz.php");
require_once ("DeleteQuiz.php");
require_once ("OtherQuiz.php");

spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );

error_log ( "****************************" );
$userDao = new UserDao ();
// $quizDao = new QuizDao ();

// / ボット ///
$bot = new \LINE\LINEBot ( new \LINE\LINEBot\HTTPClient\CurlHTTPClient ( LINE_MESSAGING_API_CHANNEL_TOKEN ), [
		'channelSecret' => LINE_MESSAGING_API_CHANNEL_SECRET
] );

$signature = $_SERVER ["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
$body = file_get_contents ( "php://input" );

$events = $bot->parseEventRequest ( $body, $signature );

// $event = $events [0]; // /////ここはどうする
foreach ( $events as $event ) {

	// ユーザ情報取得
	$reply_token = $event->getReplyToken ();
	$user_id = $event->getUserId ();
	$user = get_user ( $user_id, $bot );
	$user_db = $userDao->findUserByUser_id ( $user_id );

	if ($user_db == null) {
		// 初めてのユーザ
		error_log ( "new user" );

		$userDao->insertUser ( $user );
		$text = "ようこそ、" . $user->displayname . "さん\nメニューからやりたいことを選んでね";
		$bot->replyText ( $reply_token, $text );
	} else {
		// ２度目以上のユーザ
		error_log ( "not new user" );
		$user = $user_db;

		// 現在このユーザが何やっているか$nowに求める
		$now = $user->now;

		if ($isText = $event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
			$in_text = $event->getText ();

			if ($in_text === "あきた") { // あきた　の入力は最優先
				$res = $bot->replyText ( $reply_token, "ばいにゃん" );
				// $userDao->clearUser ( $user->user_id );
				// return;
				$now = "other";
				$user->now = $now;
			} else if ($isText && $in_text === "クイズを楽しみたい") { // メニューから
				$now = "enjoy";
				$user->now = $now;
				$user->do = "genre";
			} else if ($isText && $in_text === "クイズを作りたい") { // メニューから
				$now = "make";
				$user->now = $now;
				$user->do = "genre";
			} else if ($isText && $in_text === "クイズをなおしたい") { // メニューから
				$now = "update";
				$user->now = $now;
				$user->do = "genre";
			} else if ($isText && $in_text === "クイズを消したい") { // メニューから
				$now = "delete";
				$user->now = $now;
				$user->do = "genre";
			}
		} else if ($event instanceof \LINE\LINEBot\Event\PostbackEvent) {
			// $eventがPostbackEvent型だったらデータを取る
			$query = $event->getPostbackData ();
			if ($query) {
				// Querystringをパースして配列に戻す
				parse_str ( $query, $data );
				if (isset ( $data ["reply"] )) {
					$in_text = $data ["reply"];
				}
			}
		}

		if ($now === "enjoy") {
			$user = enjoy_quiz ( $user, $in_text, $bot, $reply_token );
		} else if ($now === "make") {
			$user = make_quiz ( $user, $in_text, $bot, $reply_token );
		} else if ($now === "update") {
			$user = update_quiz ( $user, $in_text, $bot, $reply_token );
		} else if ($now === "delete") {
			$user = delete_quiz ( $user, $in_text, $bot, $reply_token );
		} else if ($now === "other") {
			$user = other_quiz ( $user, $in_text, $bot, $reply_token );
		}

		$userDao->updateUser ( $user );
	}
}





function get_user($user_id, $bot) {
	$response = $bot->getProfile ( $user_id );
	error_log ( "get_user" );
	if ($response->isSucceeded ()) {
		error_log ( "in if" );

		$profile = $response->getJSONDecodedBody ();
		$displayname = $profile ['displayName'];

		// $user = new User ( $user_id, $displayname, "", "", new Quiz ( "", "", null, "", "", "" ), 0 );
		$user = new User ( $user_id, $displayname, "", "", "", 0 );
		// $user = new User();

		error_log ( "new User" );
		return $user;
	}
	return null;
}
