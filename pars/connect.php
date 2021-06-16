<?php
require_once 'config.php';

$db = mysqli_connect(Host, User, Pass, DB)   // установка соединений с бд
or die("Ошибка " . mysqli_error($db));
mysqli_set_charset($link, 'utf8');