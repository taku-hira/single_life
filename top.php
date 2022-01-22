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
$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;
$session_user_id = '';
$cart = [];
$category = '';
$search = [];
$new_img_filename = '';
$img_dir = './img/';

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
try{
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    $sql = 'SELECT * FROM items WHERE status = 0';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $product = $stmt->fetchAll();
    //カートに追加：POST受け取り
    if($_SERVER['REQUEST_METHOD'] === 'POST' && $process_kind === 'cart_in'){
         if(isset($_POST['item_id']) === true){
            $item_id = $_POST['item_id'];
        }
        if(isset($_POST['item_name']) === true){
            $item_name = $_POST['item_name'];
        }
        if(isset($_POST['count']) === true){
            $count = $_POST['count'];
        }
        //エラーメッセージ
        if(strlen($count) === 0){
            $err_msg[] = '個数を入力してください';
        } else if(ctype_digit($count) === false) {
            $err_msg[] = '個数は数字で入力してください';
        } else if($count === '0') {
            $err_msg[] = '1以上の数字を入力してください';
        }
        if($item_id === ''){
            $err_msg[] = '商品を選択してください';
        } else if(ctype_digit($item_id) === false) {
            $err_msg[] = '不正なアクセスです';
        }
        //postデータをカートテーブルに格納
        if(empty($err_msg) === true){
            try{
                $sql = 'SELECT * FROM carts WHERE item_id = ? and user_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $item_id, PDO::PARAM_INT);
                $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $cart = $stmt->fetch();
                if(empty($cart) === true){
                    $sql = 'INSERT INTO carts(user_id, item_id, amount, create_datetime, update_datetime)
                                   VALUES(?, ?, ?, NOW(), NOW())';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
                    $stmt->bindValue(3, $count, PDO::PARAM_INT);
                    $stmt->execute();
                    $message[] = 'カートに入りました';
                } else {
                    $sql = 'UPDATE carts SET amount = amount + ? , update_datetime = NOW() WHERE item_id = ? and user_id = ?';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1, $count, PDO::PARAM_INT);
                    $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
                    $stmt->bindValue(3, $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $message[] = 'カートに追加しました';
                }
            }catch(PDOException $e){
                $err_msg[] = 'カートに入りませんでした' . $e->getMessage();
            }
        }
    }
    if($_SERVER['REQUEST_METHOD'] === 'POST' && $process_kind === 'search') {
        if(isset($_POST['category']) === true) {
            $category = $_POST['category'];
        }
        if($category !== '1' && $category !== '2' && $category !== '3' && $category !== '4'){
            $err_msg[] = '検索失敗しました';
        }
        if(empty($err_msg) === true){
            $sql = 'SELECT * FROM items WHERE category = ? and status = 0';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $category, PDO::PARAM_INT);
            $stmt->execute();
            $search = $stmt->fetchAll();
        }
        
        
    }
    
}catch(PDOException $e) {
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
        <link rel="stylesheet" href="top.css">
        <link rel="icon" type="image/x-icon" href="img2/favicon.png">
    </head>
    <body>
        <div class="header">
            <h1><a href="top.php">Single-Life.com</a></h2>
            <ul class="header-right">
                <li><?php print $user_name . 'さん' ?></li>
                <li><a href="cart.php"><img src="img2/cart.png" alt="カートへ"></a></li>
                <li><a href="logout.php">ログアウト</a></li>
            </ul>
        </div>
        <div class="background">
            <img src="img2/cover_photo_1.png">
        </div>
        <div class="container">
            <div class="search">
                <!--メッセージ-->
                <?php foreach($message as $value) { ?>
                <p class="message"><?php print $value; ?></p>
                <?php } ?>
                <!--エラーメッセージ-->
                <?php foreach($err_msg as $value) { ?>
                <p class="err_msg"><?php print $value; ?></p>
                <?php } ?>
                <div class="main-right">
                    <form method="post">
                        カテゴリー検索：<select name="category">
                            <option value="1">キッチン</option>
                            <option value="2">バス・トイレ</option>
                            <option value="3">リビング</option>
                            <option value="4">玄関</option>
                        </select>
                        <input type="hidden" name="process_kind" value="search">
                        <input type="submit" value="検索">
                    </form>
                </div>
            </div>
            <div class="items">
                    <?php if($category === '') { ?>
                            <ul>
                                <?php foreach($product as $value) { ?>
                                    <li>
                                        <form method='post'>
                                            <form method="post">
                                            <img src="<?php print $img_dir . $value['img']; ?>">
                                            <p>商品名：<?php print htmlspecialchars($value['item_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p>価格：<?php print htmlspecialchars($value['price'], ENT_QUOTES, 'UTF-8'); ?>円</p>
                                            <?php if($value['stock'] === 0){ ?>
                                            <p>売り切れ</p>
                                            <?php } else{ ?>
                                            <p>数量：<input type="text" name="count">個</p>
                                            <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
                                            <input type="submit" name="cart_in" value="カートに追加する">
                                            <?php } ?>
                                            <input type="hidden" name="process_kind" value="cart_in">
                                        </form>
                                        <form method="post" action="item_detail.php">
                                            <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
                                            <input type="hidden" name="process_kind" value="detail">
                                            <input type="submit" value="商品詳細ページ">
                                        </form>
                                     </li>
                                <?php } ?>
                             </ul>
                    <?php } else { ?>
                        <ul>
                            <?php foreach($search as $value) { ?>
                                <li>
                                    <form method='post' >
                                         <p> <img src="<?php print $img_dir . $value['img']; ?>"></p>
                                         <p>商品名：<?php print htmlspecialchars($value['item_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                         <p>価格：<?php print htmlspecialchars($value['price'], ENT_QUOTES, 'UTF-8'); ?>円</p>
                                         <input type="hidden" name="process_kind" value="cart_in">
                                       <?php if($value['stock'] === 0){ ?>
                                       <p>売り切れ</p>
                                       <?php } else{ ?>
                                         <p>数量<input type="text" name="count">個</p>
                                         <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
                                         <input type="submit" name="cart_in" value="カートに追加する">
                                       <?php } ?>
                                    </form>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
            </div>  
        </div>
    </body>
</html>