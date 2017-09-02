<?php
require __DIR__ . "/../vendor/autoload.php";
spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
require_once ("GetGenres.php");
require_once ("GetQuizzes.php");

spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
function update_quiz($user, $in_text, $bot, $reply_token) {
	error_log ( "*update_quiz*now:" . $user->now . " do:" . $user->do );
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
		$user->do = "new_title";

		$user->quiz->genre = $in_text;
		error_log ( "***********" . $user->do . $user->now . $user->quiz->genre );
		$text = "直したいクイズのタイトルをお願い";
		$bot->replyText ( $reply_token, $text );

		$user->do = "new_description";
		return $user;
	} else if ($do === "new_description") { // タイトル取得、新解説入力依頼
		error_log ( "**do=new_title*genre:" . $user->quiz->genre );

		$user->quiz->title = $in_text;

		$quizDao = new QuizDao ();
		$quiz = $quizDao->findQuizByTitleAndGenru ( $in_text, $user->quiz->genre ); // ////////////

		if ($quiz === null) {
			$text = "そんなクイズないわ";
			$user->now = "other";
		} else if ($quiz->user_id != $user->user_id) {
			$text = "自分の作ったクイズでないと中身を変えられないの";
			$user->now = "other";
		} else {
			$answerDao = new AnswerDao ();
			$lists = $answerDao->findAnswersByTitleAndGenre ( $user->quiz->title, $user->quiz->genre );
			$quiz->answers = serialize ( $lists );
			$user->quiz = $quiz;
			$text = "クイズの解説はこれよ\n\n" . $user->quiz->description;
			$text = $text . "\n\n新しい解説を入れてね";

			$user->do = "new_answer_top";
		}
		$bot->replyText ( $reply_token, $text );

		$user->do = "new_answer_top";
		return $user;
	} else if ($do === "new_answer_top") { // 新解説取得、最初の正解表示、新正解入力依頼
		error_log ( "**do=new_answer_top*title:" . $user->quiz->title );
		$user->quiz->description = $in_text;

		$answers = ( array ) (unserialize ( $user->quiz->answers ));

		$image1_builder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder ( "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/update_answer_1.jpg", "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/update_answer_1.jpg" );
		$image2_builder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder ( "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/update_answer_2.jpg", "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/update_answer_2.jpg" );
		$image3_builder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder ( "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/update_answer_3.jpg", "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/update_answer_3.jpg" );
		$text_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder ( $answers [0] );

		$message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder ();
		$message->add ( $image1_builder );
		$message->add ( $image2_builder );
		$message->add ( $image3_builder );
		$message->add ( $text_builder );
		// リプライTokenを付与して返信する
		$res = $bot->replyMessage ( $reply_token, $message );

		/*
		 * $text = "正解を一つずつ言うので、新しい正解を入れてね\nもし消したければ いらない って入れて\n最初は\n";
		 * $answers = ( array ) (unserialize ( $user->quiz->answers ));
		 *
		 * $text = $text . $answers [0];
		 * $bot->replyText ( $reply_token, $text );
		 */

		$user->do = "answer";
		// 正解数を覚えておく（未処理分）
		$user->quiz_answer_no = count ( $answers );
		return $user;
	} else if ($do === "answer") { // 新正解取得、次の正解表示
		error_log ( "**do=answer*title:" . $user->quiz->title . " genre:" . $user->quiz->genre );

		$answers = ( array ) (unserialize ( $user->quiz->answers ));
		$count = count ( $answers );

		$old_answer = $answers [0];
		array_splice ( $answers, 0, 1 );

		if ($in_text === "い") { // 削除の場合何もしない
			$text = "「" . $old_answer . "」を削除するわね\n";
		} else if ($in_text === "あ") { // 変更なしの場合、あらかじめ退避しておいた正解を末尾に追加
			$answers [] = $old_answer;
			$text = "「" . $old_answer . "」のままね\n";
		} else { // 変更有の場合、入力された正解を末尾に追加
			$answers [] = $in_text;
			$text = "「" . $in_text . "」ねっ了解よ！\n";
		}

		// 未処理の正解数を一つ減らす
		$user->quiz_answer_no = $user->quiz_answer_no - 1;
		$user->quiz->answers = serialize ( $answers );

		if ($user->quiz_answer_no > 0) {
			$text = $text . "次は\n" . $answers [0];
			$bot->replyText ( $reply_token, $text );
		} else {
			$text = $text . "これで全部よ\n追加があればこのまま続きを入れてね";
			$text_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder ($text);
			$image4_builder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder ( "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/update_answer_4.jpg", "https://miminomimi.sakura.ne.jp/linequiz/images/quiz/update_answer_4.jpg" );

			$message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder ();
			$message->add ( $text_builder);
			$message->add ( $image4_builder );

			$res = $bot->replyMessage ( $reply_token, $message );
			$user->do = "answer_add";
		}

		return $user;
	} else if ($do === "answer_add") { // 新正解取得、次の正解表示
		$answers = ( array ) (unserialize ( $user->quiz->answers ));
		if ($in_text === "おしまい") {
			$quiz = $user->quiz;

			$quizDao = new QuizDao ();
			$quizDao->updateQuiz ( $quiz );

			$answerDao = new AnswerDao ();
			$answerDao->deleteAnswers ( $quiz->title, $quiz->genre );
			$answerDao->insertAnswers ( $quiz->title, $quiz->genre, unserialize ( $quiz->answers ) );

			$text = "お疲れ様～～～";

			$user->now = "other";
			$user->do = "";
		} else {
			$answers [] = $in_text;
			$user->quiz->answers = serialize ( $answers );
			$text = "「" . $in_text . "」追加ねっ了解よ！最後は おしまい って入れてね";
		}

		$bot->replyText ( $reply_token, $text );

		return $user;
	}
}