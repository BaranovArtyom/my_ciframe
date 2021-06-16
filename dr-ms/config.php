<?php

/**для соединения с базой */
define('DB_HOST', 'newtea.mysql.tools');
define('DB_USER', 'newtea_db');
define('DB_PASSWORD', 'b4Lmj2SNn7Y2');
define('DB_NAME', 'newtea_db');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("Ошибка " . mysqli_error($db));
mysqli_set_charset($db, 'utf8');
