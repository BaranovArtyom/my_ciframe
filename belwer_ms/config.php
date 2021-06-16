<?php

/**для соединения с базой */
define('DB_HOST', 'localhost');
define('DB_USER', 'sasha');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'belwer');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("Ошибка " . mysqli_error($db));
mysqli_set_charset($db, 'utf8');