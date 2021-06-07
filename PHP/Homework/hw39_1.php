<?php
session_start();
$_SESSION['name'] = $_POST['name'];
// $time = date('H', $_SERVER['REQUEST_TIME']);
$time = 5;
if ( $time>=5 && $time<11 ) {
    echo "Доброе утро, {$_SESSION['name']}";
}elseif ($time>=11 && $time<16) {
    echo "Добрый день, {$_SESSION['name']}";
}elseif ($time>=16 && $time<24) {
    echo "Доброй вечер, {$_SESSION['name']}";
}elseif ($time>=0 && $time<5)
{
    echo "Доброй ночи, {$_SESSION['name']}";
    echo $time;
}