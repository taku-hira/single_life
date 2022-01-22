<?php 
$host = 'localhost';
$username = 'codecamp44695';
$userpassword = 'codecamp44695';
$dbname = 'codecamp44695';
$charset = 'utf8';
$err_msg = [];
$message = [];
$user_name = '';
$password = '';
$email = '';
$sex = '';
$birth = '';
$email_regex = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/iD';

$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;
//データベース接続
try{
    $dbh = new PDO($dsn, $username, $userpassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //POSTデータ取得
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['user_name']) === true){
            $user_name = $_POST['user_name'];
            $user_name = preg_replace('/(^[ 　]+)|([ 　]+$)/u', '', $user_name);
        }
        if(isset($_POST['password']) === true){
            $password = $_POST['password'];
        }
        if(isset($_POST['email']) === true){
            $email = $_POST['email'];
        }
        if(isset($_POST['sex']) === true){
            $sex = $_POST['sex'];
        }
        if(isset($_POST['birth']) === true){
            $birth = $_POST['birth'];
        }
        //エラーメッセージ
        if($user_name === ''){
            $err_msg[] = 'ユーザ名を入力してください';
        }else if(preg_match('/^[0-9a-zA-Z]{6,20}$/', $user_name) !== 1){
            $err_msg[] = 'ユーザー名半角英数字6~20文字で入力してください' ;
        }
        if($password === ''){
            $err_msg[] = 'パスワードを入力してください';
        }else if(preg_match('/^([a-zA-Z0-9]{6,20})$/', $password) !== 1){
            $err_msg[] = 'パスワードは6〜２０文字の半角英数字で入力してください';
        }
        if(empty($email) === true){
            $err_msg[] = 'メールアドレスを入力してください';
        }else if(preg_match($email_regex, $email) !== 1){
            $err_msg[] = '正しくないメールアドレスです';
        }
        if($sex !== '1' && $sex !== '2'){
            $err_msg[] = '性別を選択してください';
        }
        if($birth === ''){
            $err_msg[] = '誕生日を入力してください';
        }
        if(preg_match('/\A[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\z/', $birth) !== 1){
            $err_msg[] = '誕生日の形式が間違っています';
        }
        if(empty($err_msg) === true){
            $sql = 'SELECT user_name FROM users WHERE user_name = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
            $stmt->execute();
            $user_serch = $stmt->fetch();
            if($user_serch !== false) {
                $err_msg[] = 'このユーザ名はすでに登録しています';
            }
        }
        //データベースに格納
        if(empty($err_msg) === true){
            try{
                $sql = 'INSERT INTO users(user_name, password, mail, sex, birthday, create_datetime, update_datetime)
                                    VALUE (?, ?, ?, ?, ?, NOW(), NOW())';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_name, PDO::PARAM_INT);
                $stmt->bindValue(2, $password, PDO::PARAM_STR);
                $stmt->bindValue(3, $email, PDO::PARAM_STR);
                $stmt->bindValue(4, $sex, PDO::PARAM_INT);
                $stmt->bindValue(5, $birth, PDO::PARAM_INT);
                $stmt->execute();
                $message[] = '登録完了しました！';
            }catch(PDOException $e){
                $err_msg[] = '登録失敗です'. $e->getMessage();
            }
        }
    }
}catch(PDOException $e){
    $err_msg[] = '接続失敗' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="ja">
        <title>ユーザ登録画面</title>
        <link rel="stylesheet" href="https://github.com/csstools/sanitize.css">
        <link rel="stylesheet" href="user_complate.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="img2/favicon.png">
    </head>
    <body>
        <h1>Single-Life.com</h1>
        <div class="complate">
            <?php foreach($err_msg as $value) { ?>
            <p class="err_msg"><?php print $value; ?></p>
            <?php } ?>
            <?php foreach($message as $value) { ?>
            <p class="message"><?php print $value; ?></p>
            <?php } ?>
            <?php if(empty($err_msg) === true) { ?>
            <p><a href="login.php">ログインページへ</a></p>
            <?php } else { ?>
            <p><a href="user-entry.php">登録画面に戻る</a></p>
            <?php } ?>
        </div>
    </body>
</html>