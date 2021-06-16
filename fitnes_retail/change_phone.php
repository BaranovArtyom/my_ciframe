<?php
ini_set('display_errors', 'on');
require_once 'funcs.php';

$get_data = file_get_contents("php://input");
file_put_contents('hook.txt',date('H:i:s').$get_data."\n",FILE_APPEND);

// получение данных id и телефон при событии создания заказа 
$get_data_order = explode("&", $get_data);              //получение ответа типа orderId=217604&orderPhone=+7 (950) 403-56-36
$get_id_order = explode("=", $get_data_order[0]);       // $get_data_order[0]= orderId=217604
$idOrder = $get_id_order[1];                            // 217604
$get_phone_order = explode("=", $get_data_order[1]);    // $get_data_order[1]= orderPhone=+7 (950) 403-56-36
// $phone = '89229299999';
$getPhone = urldecode($get_phone_order[1]);                        // +7 (950) 403-56-36

/**запись данных в log */
file_put_contents('hook.txt',date('H:i:s').'заказ - '.$idOrder.' телефон -'.$getPhone."\n",FILE_APPEND);

/**преобразование телефона */
if ($getPhone[0]=='8') {
    $getPhone = str_replace($getPhone[0], "7", $getPhone);
}
$zamena = array("+", "(", ")", "-", " ");
$getPhone = str_replace($zamena, "", $getPhone);
/**запись данных после преобразования в log */
file_put_contents('hook.txt',date('H:i:s').'заказ - '.$idOrder.' телефон -'.$getPhone."\n",FILE_APPEND);

$changePhone = changePhone($idOrder, $getPhone);
dd($changePhone);