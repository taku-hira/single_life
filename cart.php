<?php
$host = 'localhost';
$username = 'codecamp44695';
$password = 'codecamp44695';
$dbname = 'codecamp44695';
$charset = 'utf8';
$err_msg = [];
$message = [];
$user_name = '';
$user_id = '';
$cart = '';
$total = '';
$new_img_filename = '';
$img_dir = './img/';
$update_cart_id = '';
$update_amount = '';
$delete_cart_id = '';
$process_kind = '';

$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;
session_start();

if(isset($_SESSION['user_id']) === true && isset($_SESSION['user_name']) === true){
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
} else {
    header('Location: login.php');
    exit;
}
//$process_kind定義
if(isset($_POST['process_kind']) === true){
    $process_kind = $_POST['process_kind'];
}

//データベース接続
try{
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //購入個数の変更
    if($_SERVER['REQUEST_METHOD'] === 'POST' && $process_kind === 'update'){
        if(isset($_POST['update_cart_id']) === true){
            $update_cart_id = $_POST['update_cart_id'];
        }
        if(isset($_POST['update_amount']) === true){
            $update_amount = $_POST['update_amount'];
        }
        if(strlen($update_amount) === 0) {
            $err_msg[] = '購入数量を入力してください';
        } else if(ctype_digit($update_amount) === false) {
            $err_msg[] = '購入数量は数字で入力してください';
        } else if($update_amount === '0') {
            $err_msg[] = '1以上の数字を入力してください';
        }
        if(ctype_digit($update_cart_id) === false) {
            $err_msg[] = '不正なアクセスです';
        }
        if(empty($err_msg) === true){
            try{
                $sql = 'UPDATE carts SET amount = ?, update_datetime = NOW() WHERE user_id = ? AND cart_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $update_amount, PDO::PARAM_INT);
                $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
                $stmt->bindValue(3, $update_cart_id, PDO::PARAM_INT);
                $stmt->execute();
                $message[] = '購入数量を変更しました';
            }catch(PDOException $e){
                $err_msg[] = '購入数変更に失敗しました' . $e->getMessage();
            }
        }
    }
    //カート内の商品の削除
    if($_SERVER['REQUEST_METHOD'] === 'POST' && $process_kind === 'delete'){
        if(isset($_POST['delete_cart_id']) === true){
            $delete_cart_id = $_POST['delete_cart_id'];
        }
        if(ctype_digit($delete_cart_id) === false) {
            $err_msg[] = '不正なアクセスです';
        }
        if(empty($err_msg) === true){
            try{
                $sql = 'DELETE FROM carts WHERE user_id = ? AND cart_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                $stmt->bindValue(2, $delete_cart_id, PDO::PARAM_INT);
                $stmt->execute();
                $message[] = '削除しました';
            }catch(PDOException $e){
                $err_msg[] = '削除できませんでした' . $e->getMessage();
            }
        }
    }
    //カートの商品を表示   
    $sql = 'SELECT * FROM carts JOIN items ON carts.item_id = items.item_id WHERE user_id = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cart = $stmt->fetchAll();
    //合計
    foreach($cart as $value){
        $total += $value['price'] * $value['amount'];
    }
    
    
}catch(PDOException $e){
    $err_msg[] = '接続失敗しました' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="ja">
        <title>Single-Life.com</title>
        <link rel="stylesheet" href="https://github.com/csstools/sanitize.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="cart.css">
        <link rel="icon" type="image/x-icon" href="img2/favicon.png">
    </head>
    <body>
        <div class="header">
            <h1><a href="top.php">Single-Life.com</a></h1>
            <ul class="header-right">
                <li><?php print $user_name ;?>さん</li>
                <li><a href="top.php">商品一覧に戻る</a></li>
                <li><a href="logout.php">ログアウト</a></li>
            </ul>
        </div>
        <div class="background">
            <img src="img2/cover_photo_1.png">
        </div>
        <div class="container">
            <div class="main">
                <div class="cart">
                    <h2>カート一覧</h2>
                     <!--メッセージ-->
                    <?php foreach($message as $value) { ?>
                    <p class="message"><?php print $value; ?></p>
                    <?php } ?>
                    <!--エラーメッセージ-->
                    <?php foreach($err_msg as $value) { ?>
                    <p class="err_msg"><?php print $value; ?></p>
                    <?php } ?>
                    <table border="1">
                        <tr>
                            <th>商品画像</th>
                            <th>商品名</th>
                            <th>購入数</th>
                            <th>小計</th>
                            <th>削除</th>
                        </tr>
                        <?php foreach($cart as $value) { ?>
                        <tr>
                            <td><img src="<?php print $img_dir . $value['img']; ?>"></td>        
                            <td><?php print htmlspecialchars($value['item_name'], ENT_QUOTES, 'utf-8'); ?></td>
                            <td>
                                <form method="post">
                                    <input type="text" name="update_amount" value="<?php print $value['amount']; ?>">個
                                    <input type="hidden" name="update_cart_id" value="<?php print $value['cart_id']; ?>">
                                    <input type="hidden" name="process_kind" value="update">
                                    <input type="submit" value="変更">
                                </form>
                            </td>
                            <td><?php print htmlspecialchars($value['price'] * $value['amount'], ENT_QUOTES, 'utf-8') . '円'; ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="process_kind" value="delete">
                                    <input type="hidden" name="delete_cart_id" value="<?php print $value['cart_id']; ?>">
                                    <input type="submit" value="削除">
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                    <div class ="total">
                        <p>合計：<?php print htmlspecialchars($total, ENT_QUOTES, 'utf-8'); ?>円</p>
                        <form method="post" action="buy_complate.php">
                        <?php if($total !== '') { ?>
                        <input type="submit" value="購入する">
                        <?php }else{ ?>
                        <p>カートに商品が入っていません</p>
                        <?php } ?>
                        </form>
                    </div>
                </div>
                </div>
            </div>
    </body>
</html>