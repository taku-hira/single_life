<?php 
$host = 'localhost';
$username = 'codecamp44695';
$password = 'codecamp44695';
$dbname = 'codecamp44695';
$charset = 'utf8';
$item_id = '';
$price = '';
$count = '';
$user_id = '';
$user_name = '';
$message = [];
$err_msg = [];
$new_img_filename = '';
$img_dir = './img/';
$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;
$total = '';
$buy = [];
$new_stock = '';


session_start();

if(isset($_SESSION['user_id']) === true && isset($_SESSION['user_name']) === true){
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
} else {
    header('Location: login.php');
    exit;
}
//データベス接続
try {
     $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
     $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
     if($_SERVER['REQUEST_METHOD'] === 'POST'){
         //カートの商品を表示   
        $sql = 'SELECT * FROM carts JOIN items ON carts.item_id = items.item_id WHERE user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $buy = $stmt->fetchAll();
        //合計
        foreach($buy as $value){
            $total += $value['price'] * $value['amount'];
        }
        foreach($buy as $value){
            if($value['status'] !== 0){
                $err_msg[] = $value['item_name'] . 'は販売していません';
            }else if($value['stock'] < 1){
                $err_msg[] = $value['item_name'] . 'は在庫がありません';
            }else if($value['stock'] < $value['amount']) {
                $err_msg[] = $value['item_name'] . 'は在庫が' . $value['stock'] . '個しかありません';
            }
        }
        if(empty($err_msg) === true){
            
            $dbh->beginTransaction();
            try{
                //在庫から購入数量を引く
                foreach($buy as $value) {
                    $sql = 'UPDATE items SET stock = stock - ?, update_datetime = NOW() WHERE item_id = ? ';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1, $value['amount'], PDO::PARAM_INT);
                    $stmt->bindValue(2, $value['item_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
                //カート情報の削除
                $sql = 'DELETE FROM carts WHERE user_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $dbh->commit();
            }catch(PDOException $e){
                $dbh->rollback();
                $err_msg[] = '購入できませんでした' . $e->getMessage();
            }
        }
    } else {
        $err_msg[] = '不正なアクセスです';
    }
    }catch(PDOException $e){
        $err_msg[] = '接続失敗' . $e->getMessage();
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
                <li><?php print $user_name . 'さん' ?></li>
                <li><a href="top.php">商品一覧へ</a></li>
                <li><a href="logout.php">ログアウト</a></li>
            </ul>
        </div>
        <div class="background">
            <img src="img2/cover_photo_1.png">
        </div>
        <div class="container">
            <div class="main">
                <div class="cart">
                    <h2>購入商品一覧</h2>
                     <!--メッセージ-->
                    <?php foreach($message as $value) { ?>
                    <p class="message"><?php print $value; ?></p>
                    <?php } ?>
                    <!--エラーメッセージ-->
                    <?php foreach($err_msg as $value) { ?>
                    <p class="err_msg"><?php print $value; ?></p>
                    <?php } ?>
                    <?php if(empty($err_msg) === true) { ?>
                    <table border="1">
                        <tr>
                            <th>商品画像</th>
                            <th>商品名</th>
                            <th>購入数</th>
                            <th>小計</th>
                        </tr>
                        <?php foreach($buy as $value) { ?>
                        <tr>
                            <td><img src="<?php print $img_dir . $value['img']; ?>"></td>        
                            <td><?php print htmlspecialchars($value['item_name'], ENT_QUOTES, 'utf-8'); ?></td>
                            <td><?php print htmlspecialchars($value['amount'], ENT_QUOTES, 'utf-8'); ?></td>
                            <td><?php print htmlspecialchars($value['price'] * $value['amount'], ENT_QUOTES, 'utf-8'); ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                    <div class ="total">
                    <p>合計：<?php print $total; ?>円</p>
                    <p>購入完了しました。ご利用ありがとうございました</p>
                    <p><a href="top.php">商品一覧に戻る</a></p>
                    </div>
                    <?php } else { ?>
                    <div class ="total">
                    <p><a href="top.php">商品一覧に戻る</a></p>
                    </div>
                    <?php } ?>
                </div>
                </div>
            </div>
    </body>
</html>