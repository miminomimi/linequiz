<?php
require_once ("GetGenres.php");
require_once ("GetQuizzes.php");

spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
function other_quiz($user, $in_text, $bot, $reply_token) {
	error_log ( "*other_quiz*now:" . $user->now . " do:" . $user->do );

	$userDao = new UserDao ();
	$userDao->clearUser ( $user->user_id );

	$text = "はぁ？やりたいことはメニューから選んでね";
	$bot->replyText ( $reply_token, $text );

	return $user;
}
