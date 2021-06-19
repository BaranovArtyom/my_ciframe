<?php
// require_once "func.php";

ini_set('display_errors', 1);

function dd($value) {
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}
define('DB_HOST', 'localhost');
define('DB_USER', 'sasha');
define('DB_PASSWORD', 'пароль');
define('DB_NAME', 'u1048374_mealjoy_db');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) ;
$item_row = mysqli_query($db,"SELECT site_id FROM `ms_goods` ");
echo 1;
dd($item_row);


// $getOrdersVM = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `created_on` > '$data' "); // получений заказов за день
// dd($getOrdersVM);
echo 1;