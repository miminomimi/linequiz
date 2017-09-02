<?php
class Quiz {
	public $title;
	public $description;
	public $answers;
	public $genre;
	public $user_id;
	public $created_at;
	public $updated_at;
	public $image_link;
	public function __construct($title, $description, $answers, $genre, $user_id, $image_link) {
		error_log ( "Quiz construct " . $image_link);

		$this->title = $title;
		$this->description = $description;
		$this->answers = $answers;
		$this->genre = $genre;
		$this->user_id = $user_id;
		$this->image_link = $image_link;
	}
}
