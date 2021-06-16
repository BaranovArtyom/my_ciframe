<?php
define('DB_HOST', 'u511541.mysql.masterhost.ru');
define('DB_USER', 'u511541_2');
define('DB_PASSWORD', 'AN.LIEsSES3Id');
define('DB_NAME', 'u511541');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)   // установка соединений с бд
or die("Ошибка " . mysqli_error($db));
mysqli_set_charset($db, 'utf8');

// $db = mysqli_connect('localhost','sasha', 'пароль', 'u511541') or die("Ошибка " . mysqli_error($db));
// mysqli_set_charset($db, 'utf8');
dd($db);