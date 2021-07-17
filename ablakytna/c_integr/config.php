<?php

require_once 'funcs.php';
/**соединения с базой */
define('DB_HOST', 'localhost');
define('DB_USER', 'podarokb_WPDWA');
define('DB_PASSWORD', 'ihRJOiQdEiRyMszu1');
define('DB_NAME', 'podarokb_WPDWA');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("Ошибка " . mysqli_error($db));
mysqli_set_charset($db, 'utf8');