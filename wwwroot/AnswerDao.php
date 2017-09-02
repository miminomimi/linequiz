<?php
spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
class AnswerDao {
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
	function findAnswersByTitleAndGenre($title, $genre) {
		error_log ( "*QuizAnswerDao findAnswersByTitleAndGenre*title:" . $title . " genre:" . $genre );

		$dbh = $this->open ();

		$sql = "select * from answers where genre=? and title = ? ";

		$stmt = $dbh->prepare ( $sql );

		// bindValueメソッドでパラメータをセット
		$stmt->bindValue ( 1, $genre );
		$stmt->bindValue ( 2, $title );
		// executeでクエリを実行
		$stmt->execute ();
		// 結果を表示
		$results = $stmt->fetchAll ();
		$lists = array ();
		foreach ( ( array ) $results as $r ) {
			error_log ( $r ['answer'] . '++++++++++++' );
			$lists [] = $r ['answer'];
		}
		if ($results != null) {
			return $lists;
		} else {
			return null;
		}
	}
	function insertAnswers($title, $genre, $answers) {
		error_log ( "*QuizAnswerDao insertAnswers*title:" . $title . " genre:" . $genre );

		$dbh = $this->open ();
		$sql = "insert into answers	(title,  genre, answer) values (:title, :genre, :answer)";
		$stmt = $dbh->prepare ( $sql );

		foreach ( ( array ) $answers as $answer ) {
			$params = array (
					':title' => $title,
					':genre' => $genre,
					':answer' => $answer
			);

			// 挿入する値が入った変数をexecuteにセットしてSQLを実行
			$stmt->execute ( $params );
		}

		error_log ( "indertAnswers end" );

		$dbh = null;
	}

	function deleteAnswers($title, $genre) {
		error_log ( "*AnswerDao deleteAnswers*title:" . $title );

		$dbh = $this->open ();

		$sql = "delete from answers where title = :title and genre = :genre";
		$stmt = $dbh->prepare ( $sql );

		$params = array (
				':title' => $title,
				':genre' => $genre
		);

		// executeでクエリを実行
		$stmt->execute ( $params );
		error_log ( "deleteAnswers end" );

		$dbh = null;
	}


}