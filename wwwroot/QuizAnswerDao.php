<?php
spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
class QuizAnswerDao {
	function open() {
		// // データーベース ///
		// データベースに接続するために必要なデータソースを変数に格納
		// mysql:host=ホスト名;dbname=データベース名;charset=文字エンコード
		$dsn = 'mysql:host=mysql495.db.sakura.ne.jp;dbname=miminomimi_mimi_quiz;charset=utf8';

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
	function findAnswerByTitleAndGenre($title, $genre) {
		error_log ( "QuizAnswerDao findAnswerByTitleAndGenre" . $title );

		$dbh = $this->open ();

		$sql = "select * from answers where title=? and genre=? ";
		$stmt = $dbh->prepare ( $sql );

		// bindValueメソッドでパラメータをセット
		$stmt->bindValue ( 1, $title );
		$stmt->bindValue ( 2, $genre );
		// executeでクエリを実行
		$stmt->execute ();
		// 結果を表示
		$results = $stmt->fetchAll ();

		$lists = array ();
		foreach ( $results as $result ) {
			error_log ( $result ['answer'] . '++++++++++++' );
			$lists [] = $result ['answer'];
		}
		$stmt->closeCursor ();

		if ($results != null) {
			return $lists;
		} else {
			return null;
		}
	}
}