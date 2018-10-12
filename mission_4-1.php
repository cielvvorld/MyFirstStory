<?php
// データベースに接続
$dsn = 'データベース名';
$user = 'ユーザー名';
$password = 'パスワード';
$driver_options = array (
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
// 例外処理
try {
	// PDOオブジェクトの作成
	$pdo = new PDO (
		$dsn,
		$user,
		$password,
		$driver_options
	);
} catch (PDOException $e) {
	// エラー出力
	echo "Database Error";
	// var_dump ($e -> getMessage()); // エラーの詳細を調べる場合、コメントアウトを外す
	exit;
}

function h($s) {
	return htmlspecialchars ($s, ENT_QUOTES, "utf-8");
}

// テーブル作成
$sql = "CREATE TABLE IF NOT EXISTS tech_bbs"
." ("
. "id INT NOT NULL  PRIMARY KEY AUTO_INCREMENT,"
. "name char(32),"
. "comment TEXT,"
. "date DATETIME,"
. "password char(30)"
.");";
$stmt = $pdo -> query($sql);

// 入力フォーム
// 送信ボタンが押され、名前・コメントフォームに空以外のデータが送信されたら
if (isset($_POST['submit']) && isset($_POST['name']) && $_POST['name'] != "" && isset($_POST['comment']) && $_POST['comment'] != "") {
	// 編集モード
	if (!empty($_POST['editing_num'])) {
		$id = $_POST['editing_num'];
		$name = $_POST['name'];
		$comment = $_POST['comment'];
		$sql = $pdo -> prepare("update tech_bbs set name = '$name', comment = '$comment', date = now() where id = :id");
		$sql = $pdo -> bindValue(':id', $id, PDO::PARAM_INT);
		$sql -> execute();
	} else if (!empty ($_POST['password']) ) {
		// 新規投稿
		$sql = $pdo -> prepare("INSERT INTO tech_bbs (id, name, comment, date, password) VALUES ('0', :name, :comment, now(), :password)");
		$sql -> bindParam(':name', $name, PDO::PARAM_STR);
		$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
		$sql -> bindParam(':password', $password, PDO::PARAM_STR);
		$name = $_POST['name'];
		$comment = $_POST['comment'];
		$password = $_POST['password'];
		$sql -> execute();
		} else {
			echo "パスワードを入力してください。";
		}
}

// 削除機能
// 削除ボタンが押され、削除対象番号フォームに空以外のデータが送信されたら
if (isset($_POST['delete']) && isset($_POST['delete_num']) && $_POST['delete_num'] != "") {
	if (!empty ($_POST['delete_pass']) ) {
		$id = $_POST['delete_num'];
		$sql = $pdo -> prepare("SELECT * FROM tech_bbs where id = :id");
		$sql -> bindValue(':id', $id, PDO::PARAM_INT);
		$sql -> execute();
		$row = $sql -> fetch();
		if ($_POST['delete_pass'] ==  $row['password']) {
			$sql = $pdo -> prepare("delete from tech_bbs where id = :id");
			$sql -> bindValue(':id', $id, PDO::PARAM_INT);
			$sql -> execute();
		} else {
			echo "パスワードが違います。";
		}
	} else {
		echo "パスワードを入力してください。";
	}
}

// 編集機能
// 編集ボタンが押され、編集対象番号フォームに空以外のデータが送信されたら
if (isset($_POST['edit']) && isset($_POST['edit_num']) && $_POST['edit_num'] != "") {
	if (!empty ($_POST['edit_pass']) ) {
		$id = $_POST['edit_num'];
		$sql = $pdo -> prepare("SELECT * FROM tech_bbs where id = :id");
		$sql -> bindValue(':id', $id, PDO::PARAM_INT);
		$sql -> execute();
		$row = $sql -> fetch();
		if ($_POST['edit_pass'] == $row['password']) {
			$editing_num = $row['id'];
			$name = $row['name'];
			$comment = $row['comment'];
			echo $row['id']."番編集中...";
		} else {
			echo "パスワードが違います。";
		}
	} else {
		echo "パスワードを入力してください。";
	}
}
?>

<!DOCTYPE html>
<html>
<html lang = "ja">
	<head>
		<meta charset = "utf-8">
		<title>DB連携掲示板</title>
	</head>
	<body>
	<form action = "mission_4-1.php" method = "post">
	<p>
		<input type = "text" name = "name" value = "<?=h($name)?>" placeholder = "名前"><br>
		<input type = "text" name = "comment" value = "<?=h($comment)?>" placeholder = "コメント"><br>
		<input type = "text" name = "password" placeholder = "パスワード">
		<input type = "hidden" name = "editing_num" value = "<?=h($editing_num)?>">
		<input type = "submit" name ="submit" value = "送信">
	</p>
	<p>
		<input type = "text" name = "delete_num" placeholder = "削除対象番号"><br>
		<input type = "text" name = "delete_pass" placeholder = "パスワード">
		<input type = "submit" name = "delete" value = "削除">
	</p>
	<p>
		<input type = "text" name = "edit_num" placeholder = "編集対象番号"><br>
		<input type = "text" name = "edit_pass" placeholder = "パスワード">
		<input type = "submit" name = "edit" value = "編集">
	</p>
	</form>
	</body>
</html>

<?php
echo "<hr>";

/*
// テーブル一覧を表示
echo "-Table list-";
echo "<br>";
$sql = 'SHOW TABLES';
$result = $pdo -> query($sql);
foreach ($result as $row) {
	echo $row[0];
	echo '<br>';
}
echo "<hr>";

// テーブルの中身を確認
$sql = 'SHOW CREATE TABLE tech_bbs';
$result = $pdo -> query($sql);
foreach ($result as $row) {
	print_r($row);
}
echo "<hr>";
*/

// データ表示
$sql = 'SELECT * FROM tech_bbs order by id asc';
$results = $pdo -> query($sql);
foreach ($results as $row) {
	echo h($row['id']).', ';
	echo h($row['name']).', ';
	echo h($row['comment']).', ';
	echo h($row['date']);
	// echo h($row['password']);		// パスワードを確認する場合、コメントアウトを外す
	echo "<br>";
}
?>
