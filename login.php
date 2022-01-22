<?php 
$host = 'localhost';
$username = 'codecamp44695';
$userpassword = 'codecamp44695';
$dbname = 'codecamp44695';
$charset = 'utf8';
$user_name = '';
$password = '';
$err_msg = [];
$message = [];
$serch_user_name = '';
$serch_password = '';

$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;

session_start();
try {
     $dbh = new PDO($dsn, $username, $userpassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
     $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
     
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['user_name']) === true) {
            $user_name = $_POST['user_name'];
        }
        if(isset($_POST['password']) === true){
            $password = $_POST['password'];
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
        //データベースの情報と照合
        if(empty($err_msg) === true){
            $sql = 'SELECT * FROM users WHERE user_name = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
            $stmt->execute();
            $user_info = $stmt->fetch();
            if($user_info !== false) {
                if($user_info['password'] === $password){
                    $message[] = 'ログイン成功しました';
                    $_SESSION['user_id'] = $user_info['user_id'];
                    $_SESSION['user_name'] = $user_info['user_name'];
                    $_SESSION['admin_flag'] = $user_info['admin_flag'];
                    if($user_info['admin_flag'] === 1){
                        header('Location: item_tool.php');
                    }else{
                        header('Location: top.php');
                    }
                    exit;
                }else{
                    $err_msg[] = 'ID、パスワードいずれかが間違っています';
                }
            } else {
                $err_msg[] = 'ID、パスワードいずれかが間違っています';
            }
        }
    }
}catch(PDOException $e){
    $err_msg[] = '接続失敗しました' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ログイン</title>
        <link rel="stylesheet" href="https://github.com/csstools/sanitize.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="user_entry.css">
        <link rel="icon" type="image/x-icon" href="img2/favicon.png">
    </head>
    <body>
        <h1>Single-Life.com</h1>
        <div class="background">
            <img src="img2/cover_photo_1.png">
        </div>
        <div class="entry"> 
            <p>ログイン</p>
            <?php foreach($err_msg as $value) {?>
            <p class="err_msg"><?php print $value; ?></p>
            <?php } ?>
            <?php foreach($message as $value) {?>
            <p class="message"><?php print $value; ?></p>
            <?php } ?>
            <form method="post">
                <p>ユーザー名：<input type="text" name="user_name"></p>
                <p>パスワード：<input type="password" name="password"></p>
                <p><input type="submit" value="ログイン"></p>
            </form>
            <p><a href="user-entry.php">ユーザ登録がお済みでない方はこちら</a></p>
        </div>
    </body>
</html>