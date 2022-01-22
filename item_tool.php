<?php
$host = 'localhost';
$username = 'codecamp44695';
$password = 'codecamp44695';
$dbname = 'codecamp44695';
$charset = 'utf8';
$item_name = '';
$price = '';
$stock = '';
$status = '';
$category = '';
$comment = '';
$new_img_filename = '';
$img_dir = './img/';
$err_msg = [];
$message = [];
$update_stock = '';
$update_item_id = '';
$update_status = '';
$change_item_id = '';
$process_kind = '';
$delete_item_id = '';

session_start();

if(isset($_SESSION['user_id']) === true && isset($_SESSION['user_name']) === true && isset($_SESSION['admin_flag']) === true){
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $admin_flag = $_SESSION['admin_flag'];
    if($admin_flag !== 1){
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}

$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;
//データベース接続
try {
     $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
     $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
     
     //$process_kind 定義
    if(isset($_POST['process_kind']) === true){
        $process_kind = $_POST['process_kind'];
    }
     if($_SERVER['REQUEST_METHOD'] === 'POST' && $process_kind === 'insert_item'){
        if(isset($_POST['item_name']) === true){
            $item_name = $_POST['item_name'];
            $item_name = preg_replace('/(^[ 　]+)|([ 　]+$)/u', '', $item_name);
        }
        if(isset($_POST['price']) === true){
            $price = $_POST['price'];
        }
        if(isset($_POST['stock']) === true){
            $stock = $_POST['stock'];
        }
        if(isset($_POST['category']) === true){
            $category = $_POST['category'];
        }
        if(isset($_POST['status']) === true) {
            $status = $_POST['status'];
        }
        if(isset($_POST['comment']) === true) {
            $comment = $_POST['comment'];
        }
        //エラーメッセージ
        if(mb_strlen($item_name) === 0 ){
            $err_msg[] = '商品名を入力してください';
        }
        if(strlen($price) === 0) {
            $err_msg[] = '値段を入力してください';
        } else if(ctype_digit($price) === false) {
            $err_msg[] = '値段は数字で入力してください';
        } else if($price === '0') {
            $err_msg[] = '1以上の数字を入力してください';
        }
        if(strlen($stock) === 0) {
            $err_msg[] = '在庫数を入力してください';
        } else if(ctype_digit($stock) === false) {
            $err_msg[] = '在庫数は数字で入力してください';
        } else if($stock === '0') {
            $err_msg[] = '1以上の数字を入力してください';
        }
        if($status !== '0' && $status !== '1') {
            $err_msg[] = '不正なアクセスです';
        }
        if($category !== '1' && $category !== '2' && $category !== '3' && $category !== '4') {
            $err_msg[] = '不正なアクセスです';
        }
        if(mb_strlen($comment) === 0 ){
            $err_msg[] = 'コメントを入力してください';
        }
        //画像のアップロード
        if(is_uploaded_file($_FILES['img']['tmp_name']) === true) {
            $extension = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            if($extension === 'jpg' || $extension === 'png' || $extension === 'jpeg'){
                $new_img_filename = sha1(uniqid(mt_rand(), true)) . '.' . $extension;
                if(is_file($img_dir . $new_img_filename) !== TRUE) {
                    if(empty($err_msg) === true) {
                        if(move_uploaded_file($_FILES['img']['tmp_name'], $img_dir . $new_img_filename) !== TRUE) {
                            $err_msg[] = 'ファイルアップロードに失敗しまいした';
                        }
                    }
                } else {
                    $err_msg[] = 'ファイルアップロードに失敗しました';
                }
            } else {
                $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPG,PNG,JPEGのみ利用可能です';
            }
            
        } else {
            $err_msg[] = 'ファイルを選択してください';
        }
        
     //データの書き込み
     if(empty($err_msg) === true) {
         $dbh->beginTransaction();
         try{
             $sql = 'INSERT INTO items(item_name, price, img, status, stock, category, comment, create_datetime, update_datetime) 
                            VALUE(?, ?, ?, ?, ?, ?, ?, NOW(), NOW())';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $item_name, PDO::PARAM_STR);
            $stmt->bindValue(2, $price, PDO::PARAM_INT);
            $stmt->bindValue(3, $new_img_filename, PDO::PARAM_STR);
            $stmt->bindValue(4, $status, PDO::PARAM_INT);
            $stmt->bindValue(5, $stock, PDO::PARAM_INT);
            $stmt->bindValue(6, $category, PDO::PARAM_INT);
            $stmt->bindValue(7, $comment, PDO::PARAM_STR);
            $stmt->execute();
            $dbh->commit();
            $message[] = '登録完了';
         } catch(PDOException $e) {
             $dbh->rollbacl();
             $err_msg[] = '登録失敗' . $e->getMessage();
         }
      }
     }
     //在庫変更
     if($_SERVER['REQUEST_METHOD'] === 'POST' && $process_kind === 'stock'){
         if(isset($_POST['update_stock']) === true){
             $update_stock = $_POST['update_stock'];
         }
         if(isset($_POST['item_id']) === true){
             $update_item_id = $_POST['item_id'];
         }
         //エラーメッセージ
         if(strlen($update_stock) === 0) {
            $err_msg[] = '在庫数を入力してください';
        } else if(ctype_digit($update_stock) === false) {
            $err_msg[] = '在庫数は数字で入力してください';
        } else if($update_stock === '0') {
            $err_msg[] = '1以上の数字を入力してください';
        }
        if(ctype_digit($update_item_id) === false) {
            $err_msg[] = '不正なアクセスです';
        }
        if(empty($err_msg) === true) {
            try{
                $sql = 'UPDATE items SET stock = ?, update_datetime = NOW() WHERE item_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $update_stock, PDO::PARAM_INT);
                $stmt->bindValue(2, $update_item_id, PDO::PARAM_INT);
                $stmt->execute();
                $message[] = '在庫を変更しました';
            }catch(PDOException $e) {
                $err_msg[] = '在庫の変更に失敗しました' . $e->getMessage();
            }
        }
     }
     //ステータスの変更
     if($_SERVER['REQUEST_METHOD'] ==='POST' && $process_kind === 'update_status') {
        if(isset($_POST['update_status']) === true) {
            $update_status = $_POST['update_status'];
        }
        if(isset($_POST['item_id']) === true) {
            $change_item_id = $_POST['item_id'];
        }
        if($update_status !== '0' && $update_status !== '1'){
            $err_msg[] = '不正なアクセスです';
        }
         if(ctype_digit($change_item_id) === false) {
            $err_msg[] = '不正なアクセスです';
        } 
        if(empty($err_msg) === true){
            try{
                if($update_status === '0'){
                    $sql = 'UPDATE items SET status = 1, update_datetime = NOW() WHERE item_id = ?';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1, $change_item_id, PDO::PARAM_INT);
                    $stmt->execute();
                }else {
                    $sql = 'UPDATE items SET status = 0, update_datetime = NOW() WHERE item_id = ?';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1, $change_item_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
                $message[] = 'ステータスを変更しました';
            } catch(PDOException $e){
                $err_msg[] = 'ステータスの変更に失敗しました' . $e->getMessage();
            }
        }
    }
    //カート内の商品の削除
    if($_SERVER['REQUEST_METHOD'] === 'POST' && $process_kind === 'delete'){
        if(isset($_POST['delete_item_id']) === true){
            $delete_item_id = $_POST['delete_item_id'];
        }
        if(ctype_digit($delete_item_id) === false) {
            $err_msg[] = '不正なアクセスです';
        }
        if(empty($err_msg) === true){
            try{
                $sql = 'DELETE FROM items WHERE item_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $delete_item_id, PDO::PARAM_INT);
                $stmt->execute();
                $message[] = '削除しました';
            }catch(PDOException $e){
                $err_msg[] = '削除できませんでした' . $e->getMessage();
            }
        }
    }
     
    //商品情報の取得
    $sql = 'SELECT * FROM items';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $item = $stmt->fetchAll();
     
} catch (PDOException $e) {
    $err_msg[] = '接続失敗' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>商品管理</title>
        <style type="text/css">
            img {
                height: 200px;
                width: 200px;
            }
        </style>
    </head>
    <body>
        <!--メッセージ-->
        <?php foreach($message as $value) { ?>
        <p class="message"><?php print $value; ?></p>
        <?php } ?>
        <!--エラーメッセージ-->
        <?php foreach($err_msg as $value) { ?>
        <p class="err_msg"><?php print $value; ?></p>
        <?php } ?>
        <h1>商品登録</h1>
        <a href="logout.php">ログアウト</a>
        <a href="user_tool.php">ユーザ管理へ</a>
        <form method="post" enctype="multipart/form-data">
            <p>商品名：<input type="text" name="item_name"></p>
            <p>価格：<input type="text" name="price"></p>
            <p>在庫数：<input type="text" name="stock"></p>
            <p>画像<input type="file" name="img"></p>
            <p>商品説明:<textarea name="comment"></textarea></p>
            <select name="category">
                <option value="1">キッチン</option>
                <option value="2">バス</option>
                <option value="3">リビング</option>
                <option value="4">玄関</option>
            </select>
            <select name="status">
                <option value="0">公開</option>
                <option value="1">非公開</option>
            </select>
            <input type="hidden" name="process_kind" value="insert_item">
            <p><input type="submit" value="登録"></p>
        </form>
        <div>
            <table border="1">
                <tr>
                    <th>商品画像</th>
                    <th>商品名</th>
                    <th>価格</th>
                    <th>在庫</th>
                    <th>カテゴリ</th>
                    <th>ステータス</th>
                    <th>商品説明</th>
                    <th>削除</th>
                </tr>
                <?php foreach($item as $value) { ?>
                <tr>
                    <td><img src="<?php print $img_dir . $value['img']; ?>"></td>
                    <td><?php print htmlspecialchars($value['item_name'], ENT_QUOTES, 'utf-8'); ?></td>
                    <td><?php print htmlspecialchars($value['price'], ENT_QUOTES, 'utf-8'); ?></td>
                    <td>
                        <form method="post">
                            <input type="text" name="update_stock" value="<?php print $value['stock'] ?>">
                            <input type="hidden" name="process_kind" value="stock">
                            <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
                            <input type="submit" value="変更">
                        </form>
                    </td>
                    <td>
                        <?php if($value['category'] === 1) { ?>
                        <p>キッチン</p>
                        <?php } ?>
                        <?php if($value['category'] === 2) { ?>
                        <p>バスルーム</p>
                        <?php } ?>
                        <?php if($value['category'] === 3) { ?>
                        <p>リビング</p>
                        <?php } ?>
                        <?php if($value['category'] === 4) { ?>
                        <p>玄関</p>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if($value['status'] === 0) { ?>
                        <form method="post">
                            <input type="submit" value="公開→非公開">
                            <input type="hidden" name="update_status" value="0">
                            <input type="hidden" name="process_kind" value="update_status">
                            <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
                        </form>
                        <?php } ?>
                        <?php if($value['status'] === 1) { ?>
                        <form method="post">
                            <input type="submit" value="非公開→公開">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="process_kind" value="update_status">
                            <input type="hidden" name="item_id" value="<?php print $value['item_id']; ?>">
                        </form>
                        <?php } ?>
                    </td>
                    <td><?php print htmlspecialchars($value['comment'], ENT_QUOTES, 'utf-8'); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="process_kind" value="delete">
                            <input type="hidden" name="delete_item_id" value="<?php print $value['item_id']; ?>">
                            <input type="submit" value="削除">
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </body>
</html>