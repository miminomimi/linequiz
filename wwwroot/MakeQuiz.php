<?php
require_once ("GetGenres.php");
require_once ("GetQuizzes.php");

spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
function make_quiz($user, $in_text, $bot, $reply_token) {
	error_log ( "*make_quiz*now:" . $user->now . " do:" . $user->do );
	$do = $user->do;
	error_log ( "++++++++++++++++++++++++" . $user->do );

	if ($do === "genre") { // ジャンル選択表示
		error_log ( "**do=genre" );

		$quiz = new Quiz ( "a", "b", serialize ( array () ), "d", "e", "https://miminomimi.sakura.ne.jp/linequiz/images/juunishi.png" );
		$user->quiz = $quiz;

		$carousel_message = get_genres ();

		error_log ( "genre carousel get" );

		$res = $bot->replyMessage ( $reply_token, $carousel_message );
		error_log ( "enjoy_quiz " . $user->now . $user->do );

		$user->do = "title";
		return $user;
	} else if ($do === "title") { // ジャンル取得、タイトル入力依頼
		error_log ( "**do=title" );

		$user->quiz->genre = $in_text;
		error_log ( "***********" . $user->do . $user->now . $user->quiz->genre );
		$text = "クイズのタイトルを短めにお願い（40文字以内）\n補足は次の注意事項でできるわ";
		$bot->replyText ( $reply_token, $text );

		$user->do = "desription";
		return $user;
	} else if ($do === "desription") { // タイトル取得、解説入力依頼
		error_log ( "**do=description*genre:" . $user->quiz->genre );

		$quizDao = new QuizDao ();
		$quiz = $quizDao->findQuizByTitleAndGenru ( $in_text, $user->quiz->genre );
		if ($quiz != null) {
			$text = "そのタイトルのクイズはもうあるわよ";
			$user->now = "other";
		} else {

			$user->quiz->title = $in_text;
			// $user->quiz->answers = "";
			$user->quiz->user_id = $user->user_id;

			$text = "解答の注意を短めにお願い\n「何回も同じ答えを言うと不正解になるわ」が追加されるの";
		}
		$bot->replyText ( $reply_token, $text );

		$user->do = "answer_top";
		return $user;
	} else if ($do === "answer_top") { // 解説取得、正解入力依頼、
		error_log ( "**do=answer_top*genre:" . $user->quiz->genre );

		$user->quiz->description = $in_text;
		$text = "正解を一つずつお願い\n最後は　おしまい　って入れてね";
		$bot->replyText ( $reply_token, $text );

		$user->do = "answer";
		return $user;
	} else if ($do === "answer") { // 正解取得、確認表示
		error_log ( "**do=answer*title:" . $user->quiz->title . " genre:" . $user->quiz->genre );

		if ($in_text === "おしまい") {
			$quiz = $user->quiz;

			$quizDao = new QuizDao ();
			$quizDao->insertQuiz ( $quiz );

			$answerDao = new AnswerDao ();
			$answerDao->insertAnswers ( $quiz->title, $quiz->genre, unserialize ( $quiz->answers ) );

			$text = "お疲れ様～～～";

			$user->now = "other";
			$user->do = "";
		} else {
			$answers = ( array ) (unserialize ( $user->quiz->answers ));

			foreach ( $answers as $answer ) {
				error_log ( "||||||||||||||||||" . $answer );
			}

			$count = count ( $answers );
			error_log ( "***count:" . $count );

			$answers [] = $in_text;
			$user->quiz->answers = serialize ( $answers );

			$text = $in_text . "　ねっ了解よ！";
		}
		$bot->replyText ( $reply_token, $text );
		return $user;
	}
}