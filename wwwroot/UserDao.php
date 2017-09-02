<?php
// フィールドnowを追加　現在何をやっているかを保存　clever map country　のどれか
spl_autoload_register ( function ($class_name) {
	include $class_name . '.php';
} );
class UserDao {
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
	function insertUser(User $user) {
		//error_log ( "*UserDao insertUser*user_id:" . $user->userId );

		$dbh = $this->open ();

		$sql = "insert into users (user_id, displayname, now, do, quiz)values (:user_id, :displayname, :now, :do, :quiz)";
		$stmt = $dbh->prepare ( $sql );
		$now = "";
		$params = array (
				':user_id' => $user->user_id,
				':displayname' => $user->displayname,
				':now' => $user->now,
				':do' => $user->do,
				':quiz' => serialize ( $user->quiz )
		) // ///////////
;

		// 挿入する値が入った変数をexecuteにセットしてSQLを実行
		$stmt->execute ( $params );

		error_log ( "indertUser end" );

		$dbh = null;
	}
	function findUserByUser_id($user_id) {
		error_log ( "*UserDao findUserByUser_id*user_id:" . $user_id );
		$dbh = $this->open ();

		$sql = "select * from users where user_id = ? ";
		$stmt = $dbh->prepare ( $sql );

		// bindValueメソッドでパラメータをセット
		$stmt->bindValue ( 1, $user_id );
		// executeでクエリを実行
		$stmt->execute ();
		// 結果を表示
		$result = $stmt->fetch ();

		if ($result != null) {

			$user = new User ( $user_id, $result ['displayname'], $result ['now'], $result ['do'], unserialize ( $result ['quiz'] ), $result['quiz_answer_no'] ); // ここでunserialize
			return $user;
		} else {
			return null;
		}
	}
	function findNowByUser_id($user_id) {
		error_log ( "*UserDao findNowByUser_id*user_id:" . $user_id );
		$dbh = $this->open ();

		$sql = "select now from users where user_id = ? ";
		$stmt = $dbh->prepare ( $sql );

		// bindValueメソッドでパラメータをセット
		$stmt->bindValue ( 1, $user_id );
		// executeでクエリを実行
		$stmt->execute ();
		// 結果を表示
		$result = $stmt->fetch ();

		return $result ['now'];
	}
	function updateUser($user) {
		error_log ( "*UserDao updateUser*now:" . $user->now . " do:" . $user->do
			. " user_id:" . $user->user_id. " quiz_answer_no:" . $user->quiz_answer_no );

		$dbh = $this->open ();

		$sql = "update users set now=:now, do=:do, quiz=:quiz ,quiz_answer_no=:quiz_answer_no where user_id = :user_id ";
		$stmt = $dbh->prepare ( $sql );

		$params = array (
				':now' => $user->now,
				':do' => $user->do,
				':quiz' => serialize ( $user->quiz ), // ここでserialize
				':user_id' => $user->user_id,
				':quiz_answer_no' => $user->quiz_answer_no
		);
		// executeでクエリを実行
		$stmt->execute ( $params );
		error_log ( "updateUser end" );

		$dbh = null;
	}
	function findQuizAnswer($user_id) {
		error_log ( "*UserDao findQuizAnswer*user_id:" . $user_id );
		$dbh = $this->open ();

		$sql = "select * from users where user_id = ? ";
		$stmt = $dbh->prepare ( $sql );

		// bindValueメソッドでパラメータをセット
		$stmt->bindValue ( 1, $userId );
		// executeでクエリを実行
		$stmt->execute ();
		// 結果を表示
		$result = $stmt->fetch ();
		$answer = unserialize ( $result ['quiz_answer'] );

		return $answer;
	}

	function clearUser($user_id) {
		error_log ( "UserDao clearUser" );

		$dbh = $this->open ();

		$sql = 'update users set
				now=:now,
				do = :do,
				quiz = :quiz,
				quiz_answer_no = :quiz_answert_no
				where user_id =:user_id';
		$stmt = $dbh->prepare ( $sql );
		$flag = $stmt->execute ( array (
				':now' => '',
				':do' => '',
				':quiz' => '',
				':quiz_answer_no' => 0,
				':user_id' => $user_id
		) );
	}
}