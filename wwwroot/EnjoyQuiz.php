<?php
require_once ("GetGenres.php");
require_once ("GetQuizzes.php");

spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
function enjoy_quiz($user, $in_text, $bot, $reply_token) {
	error_log ( "*enjoy_quiz*now:" . $user->now . " do:" . $user->do );
	$do = $user->do;

	if ($do === "genre") { // ジャンル選択表示
		error_log ( "**do=genre" );

		$button_message = get_genres ();
		$res = $bot->replyMessage ( $reply_token, $button_message );
		$user->do = "quizzes";
		return $user;
	} else if ($do === "quizzes") { // ジャンル取得、クイズ選択表示
		error_log ( "**do=quizzes" );

		$user->quiz->genre = $in_text;
		$button_message = get_quizzes ( $in_text );
		$res = $bot->replyMessage ( $reply_token, $button_message );
		$user->do = "description";
		return $user;
	} else if ($do === "description") { // クイズ取得、解説表示
		error_log ( "**do=description*genre:" . $user->quiz->genre );

		$quizDao = new QuizDao ();
		$quiz = $quizDao->findQuizByTitleAndGenru ( $in_text, $user->quiz->genre );
		// クイズ選択ではキー入力できないので、$quizはnullにならない

		$answerDao = new AnswerDao ();
		$lists = $answerDao->findAnswersByTitleAndGenre ( $quiz->title, $quiz->genre );

		$quiz->answers = serialize ( $lists );
		$user->quiz = $quiz;

		$res = $bot->replyText ( $reply_token, $quiz->description . "\n何回も同じ答えを言うと不正解になるわ" );
		$user->do = "answer";
		return $user;
	} else if ($do === "answer") { // 解答取得、正解不正解表示
		error_log ( "**do=answer*title:" . $user->quiz->title . " genre:" . $user->quiz->genre );

		$answers = unserialize ( $user->quiz->answers );
		$count = count ( $answers );
		error_log ( "***count:" . $count );
		$text = "ダメ～";
		foreach ( $answers as $answer ) {
			error_log ( $answer . ' ' );
			if ($in_text === $answer) {
				$text = "ピンポーン！　あと" . ($count - 1) . "個よっ";
				// 削除実行
				$answers = array_diff ( $answers, array (
						$in_text
				) );
				// indexを詰める
				$answers = array_values ( $answers );
				$user->quiz->answers = serialize ( $answers );
				if ($answers == null) {
					$text = "全問正解！すごいわ！！";

					$user->now = "other";
					$user->do = "";
				}
				break;
			}
		}
		$bot->replyText ( $reply_token, $text );
		return $user;
	}
}