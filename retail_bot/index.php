<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="index.php" method="post">
        <textarea name="msg" placeholder="Сообщение"></textarea>
        <input type="submit" value="Отправить">
    </form>
</body>
</html>

<?php
require_once 'functions.php';

//проверка написано ли сообщение
    if (!empty($_POST['msg'])) {
        var_dump($_POST['msg']);
        sendMessage($_POST['msg']);
    }else {
        echo "not msg";
    }

$hook = file_get_contents("php://input");   
file_put_contents('post.txt', $hook);