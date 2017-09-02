<?php
spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
class QuizDao {
	function open() {
		// // データーベース ///
		// データベースに接続するために必要なデータソースを変数に格納
		// mysql:host=ホスト名;dbname=データベース名;charset=文字エンコード
		$dsn = 'mysql:host=mysql495.db.sakura.ne.jp;dbname=miminomimi_mimi_quiz;charset=utf8mb4';

		// データベースのユーザー名
		$user = 'miminomimi';

		// データベースのパスワード
		$password = '7301224nagidb';

		try {
			// PDOインスタンスを生成
			$dbh = new PDO ( $dsn, $user, $password );
		} catch ( PDOException $e ) {
			// エラーメッセージを表示させる
			echo 'データベースにアクセスできません！' . $e->getMessage ();
			// 強制終了
			exit ();
		}
		return $dbh;
	}
	function findQuizRandom3($genre) {
		error_log ( "*QuizDao findRandom3*genre:" . $genre );
		$dbh = $this->open ();

		$sql = "select * from quizzes where genre=? order by rand() limit 3;";
		$stmt = $dbh->prepare ( $sql );
		$stmt->bindValue ( 1, $genre );

		// executeでクエリを実行
		$stmt->execute ();
		// 結果を表示
		$results = $stmt->fetchAll (); // fetchではだめ
		$stmt->closeCursor ();

		$lists = array ();
		foreach ( $results as $result ) {
			error_log ( $result ['title'] . '++++++++++++' );
			$lists [] = new Quiz ( $result ['title'], $result ['description'], "a", $result ['genre'], $result ['user_id'], $result ['image_link'] );
		}

		if ($results != null) {
			return $lists;
		} else {
			return null;
		}
	}
	function findQuizByTitleAndGenru($title, $genre) {
		error_log ( "*QuizDao findQuizByTitleAndGenre*title:" . $title . " genre:" . $genre );

		$dbh = $this->open ();

		$sql = "select * from quizzes where genre=? and title = ? ";
		$stmt = $dbh->prepare ( $sql );

		// bindValueメソッドでパラメータをセット
		$stmt->bindValue ( 1, $genre );
		$stmt->bindValue ( 2, $title );
		// executeでクエリを実行
		$stmt->execute ();
		// 結果を表示
		$result = $stmt->fetch ();
		if ($result != null) {
			return new Quiz ( $result ['title'], $result ['description'], "a", $result ['genre'], $result ['user_id'], $result ['image_link'] );
		} else {
			return null;
		}
	}
	function insertQuiz(Quiz $quiz) {
		error_log ( "QuizDao insertQuiz" );
		$dbh = $this->open ();

		$sql = "insert into quizzes	(title, description,  genre, user_id, image_link) values (:title, :description, :genre, :user_id, :image_link)";
		$stmt = $dbh->prepare ( $sql );

		$params = array (
				':title' => $quiz->title,
				':description' => $quiz->description,
				':genre' => $quiz->genre,
				':user_id' => $quiz->user_id,
				':image_link' => $quiz->image_link
		);

		// 挿入する値が入った変数をexecuteにセットしてSQLを実行
		$stmt->execute ( $params );

		error_log ( "indertQuiz end" );

		$dbh = null;
	}

	function updateQuiz(Quiz $quiz) {
		error_log ( "*QuizDao updateQuiz*title:" . $quiz->title );

		$dbh = $this->open ();

		$sql = "update quizzes set description=:description where title = :title and genre=:genre";
		$stmt = $dbh->prepare ( $sql );

		$params = array (
				':description' => $quiz->description,
				':title' => $quiz->title,
				':genre' => $quiz->genre
		);
		// executeでクエリを実行
		$stmt->execute ( $params );
		error_log ( "updateQuiz end" );

		$dbh = null;
	}

	function deleteQuiz($title, $genre) {
		error_log ( "*QuizDao deleteQuiz*title:" . $title );

		$dbh = $this->open ();

		$sql = "delete from quizzes where title = :title and genre = :genre";
		$stmt = $dbh->prepare ( $sql );

		$params = array (
				':title' => $title,
				':genre' => $genre
		);

		// executeでクエリを実行
		$stmt->execute ( $params );
		error_log ( "deleteQuiz end" );

		$dbh = null;
	}
}