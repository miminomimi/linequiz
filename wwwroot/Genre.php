<?php
class Genre {
	public $title;
	public $description;
	public $created_at;
	public $updated_at;
	public $image_link;
	public function __construct($title, $description, $image_link) {
		error_log ( "Genre construct" );

		$this->title = $title;
		$this->description = $description;
		$this->image_link = $image_link;
	}
}