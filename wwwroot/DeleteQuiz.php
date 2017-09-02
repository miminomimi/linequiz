<?php
require_once ("GetGenres.php");
require_once ("GetQuizzes.php");

spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
function delete_quiz($user, $in_text, $bot, $reply_token) {
	error_log ( "*delete_quiz*now:" . $user->now . " do:" . $user->do );
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
		$text = "消したいクイズのタイトルをお願い";
		$bot->replyText ( $reply_token, $text );

		$user->do = "confirm";
		return $user;
	} else if ($do === "confirm") { // タイトル取得、確認入力依頼
		error_log ( "**do=new_title*genre:" . $user->quiz->genre );

		$user->quiz->title = $in_text;

		$quizDao = new QuizDao ();
		$quiz = $quizDao->findQuizByTitleAndGenru ( $in_text, $user->quiz->genre ); // ////////////

		if($quiz === null) {
			$text = "そんなクイズないわ";
			$user->now = "other";
			$bot->replyText ( $reply_token, $text );
		} else if($quiz->user_id != $user->user_id) {
			$text = "自分の作ったクイズでないと消せないの";
			$user->now = "other";
			$bot->replyText ( $reply_token, $text );
		} else {


			// 「はい」ボタン
			$yes_post = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("はい", "reply=1");
			// 「いいえ」ボタン
			$no_post = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("いいえ", "reply=0");
			// Confirmテンプレートを作る
			$text = "ジャンル「".$quiz->genre . "」の「" . $quiz->title . "」を本当に消してもいいかしら？";
			$confirm = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder($text, [$yes_post, $no_post]);
			// Confirmメッセージを作る
			$confirm_message = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("削除の確認", $confirm);

			$user->do = "delete_quiz";
			$bot->replyMessage ( $reply_token, $confirm_message );

		}

		return $user;
	} else if ($do === "delete_quiz") { // 確認入力取得、クイズ削除
		error_log ( "**do=title" );

		if ($in_text === "1") {
			$quizDao = new QuizDao ();
			$quizDao->deleteQuiz ( $user->quiz->title, $user->quiz->genre );

			$answerDao = new AnswerDao ();
			$answerDao->deleteAnswers ( $user->quiz->title, $user->quiz->genre );
		}
		$text = "お疲れ様～～～";

		$user->now = "other";
		$user->do = "";

		$bot->replyText ( $reply_token, $text );

		return $user;
	}
}