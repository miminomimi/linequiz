<?php
require __DIR__ . "/../vendor/autoload.php";
spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
function get_quizzes($genre) {
	$quizDao = new QuizDao ();
	$lists = (array)($quizDao->findQuizRandom3 ( $genre ));
	$lists [] = new Quiz ( "あきた", "もうやめたい", null, null, null, "https://miminomimi.sakura.ne.jp/linebot/images/quiz/oshimai.png" );
		// $listsの長さが5以上だとButtonTemplateBuilderでどうもエラーになり（エラーメッセージは出ない）表示されない

	$actions = array();
	foreach ( $lists as $list ) {
		error_log ( $list->title . " " . $list->image_link );
		$action = new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder ( $list->title, $list->title );
		$actions [] = $action;
	}

	$button_builder = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder
	( "クイズ選択", "シクラメンのかほり～", "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/description_quiz_select.jpg", $actions );

	// ボタンを追加してメッセージを作る
	$button_message = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder
	( "どのクイズがいいですか？", $button_builder );
	return $button_message;
}