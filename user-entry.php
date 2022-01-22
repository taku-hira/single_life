<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ユーザー新規登録</title>
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
            <p>新規登録</p>
            <form action="user_complate.php" method="post">
                <p>ユーザー名：<input type="text" name="user_name"></p>
                <p>パスワード：<input type="password" name="password"></p>
                <p>メールアドレス：<input type="text" name="email"></p>
                性別：
                男<input type="radio" name="sex" value="1">
                女<input type="radio" name="sex" value="2">
                <p>生年月日：<input type="date" name="birth"></p>
                <p><input type="submit" value="新規登録"></p>
            </form>
            <p><a href="login.php">ログインページへ</a></p>
        </div>
    </body>
</html>