<?php
class User {
	public $user_id;
	public $displayname;
	public $now;
	public $do;
	public $quiz;
	public $quiz_answer_no;
	public $created_at;
	public $updated_at;
	public function __construct($user_id, $displayname, $now, $do, $quiz, $quiz_answer_no) {
		error_log ( "User construct" );

		$this->user_id = $user_id;
		$this->displayname = $displayname;
		$this->now = $now;
		$this->do = $do;
		$this->quiz = $quiz;
		$this->quiz_answer_no = $quiz_answer_no;
	}
}
