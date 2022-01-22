<?php 
$host = 'localhost';
$username = 'codecamp44695';
$password = 'codecamp44695';
$dbname = 'codecamp44695';
$charset = 'utf8';

session_start();

if(isset($_SESSION['user_id']) === true && isset($_SESSION['user_name']) === true && isset($_SESSION['admin_flag']) === true){
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $admin_flag = $_SESSION['admin_flag'];
    if($admin_flag === 0){
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;
//データベス接続
try {
     $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
     $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
     
    //ユーザ情報の取得
    $sql = 'SELECT * FROM users';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $user = $stmt->fetchAll();
     
    }catch(PDOException $e){
        $err_msg[] = '接続失敗' . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ユーザー管理</title>
    </head>
    <body>
        <h1>ユーザー覧</h1>
        <a href="item_tool.php">商品管理ページへ</a>
        <a href="logout.php">ログアウト</a>
        <div>
            <table border="1">
                <tr>
                    <th>名前</th>
                    <th>登録日時</th>
                </tr>
                <?php foreach($user as $value) { ?>
                <tr>
                    <td><?php print htmlspecialchars($value['user_name'], ENT_QUOTES, 'utf-8')?></td>
                    <td><?php print htmlspecialchars($value['create_datetime'], ENT_QUOTES, 'utf-8')?></td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </body>
</html>