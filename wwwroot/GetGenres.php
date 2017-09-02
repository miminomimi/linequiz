<?php
require __DIR__ . "/../vendor/autoload.php";
spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
function get_genres() {
	$lists = [
			new Genre ( "アニメ・ゲーム", "あ", "https://miminomimi.sakura.ne.jp/linequiz/images/juunishi.png" ),
 			new Genre ( "鉄道", "あ", "https://miminomimi.sakura.ne.jp/linequiz/images/juunishi.png" ),
 			new Genre ( "歴史", "あ", "https://miminomimi.sakura.ne.jp/linequiz/images/juunishi.png" ),
			new Genre ( "その他", "あ", "https://miminomimi.sakura.ne.jp/linequiz/images/juunishi.png" )
	];

	$actions = array();
	foreach ( $lists as $list ) {
		error_log ( $list->title . " " . $list->image_link );
		$action = new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder ( $list->title, $list->title );
		$actions [] = $action;
	}

	$button_builder = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder
	( "ジャンル選択", "ツチノコじゃないわい", "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/description_genre_select.jpg", $actions );

	// ボタンを追加してメッセージを作る
	$carousel_message = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder ( "どのジャンルがいいですか？", $button_builder );
	return $carousel_message;
}