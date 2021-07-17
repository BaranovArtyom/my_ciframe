<?php 
ini_set('display_errors', 'on');
require_once 'config.php';                                 
require_once 'funcs.php';    

date_default_timezone_set('Europe/Moscow');
// $today = date("Y-m-d H:i:s");
$today = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." - 180 minutes"));

$logger = __DIR__.'/ci_log.log';                                     // создание лога и директории
$size_logger = filesize($logger);
    if ( $size_logger>5462000 ) file_put_contents($logger, '');      // 5mb , проверка на размер лога если более 11mb очистка
// dd($today);exit;

//папка для заказов
$orders_dir = __DIR__.'/ordersDir/';   

/**Получение заказов */
$getOrdersWP = mysqli_query($db,"SELECT * FROM `_DWA_posts` WHERE `post_type` = 'shop_order' AND `post_date` > '$today' ORDER BY `ID` DESC LIMIT 1");

foreach ($getOrdersWP as $order) {
    dd($order);
    if (!file_exists($orders_dir.$order['ID'])) {            // проверка создан или нет заказ уже
        file_put_contents($orders_dir.$order['ID'], '');

    /**получение данных по заказу в таблице _DWA_postmeta */
    /**Получение номера клиента в таблице _DWA_postmeta*/
    $order['agent_phone'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$order['ID']}' and `meta_key` = '_billing_phone'"))['meta_value'];
    // dd($order);
    $phone = '';
    $phone = $order['agent_phone'];
    $body = array();

     /**проверка на существование агента по телефону */
     if(!empty($phone))  { 
        $getAgent = getAgent($phone)->meta->size;                   // получение данных агента
        if( $getAgent!=0 ) {                                        // проверка если нет данных, то создаем
            $body['agent']['meta'] = (array)getAgent($phone)->rows[0]->meta;
            file_put_contents($logger,date('Y-m-d H:i:s').' агент уже создан '.$body['agent']['meta']['href'] ."\n",FILE_APPEND);
            dd($body);
        }else{
            /**получение фамилии в таблице  _DWA_postmeta */  
            $order['lastName'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$order['ID']}' and `meta_key` = '_billing_last_name'"))['meta_value'];
            $order['first_name'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$order['ID']}' and `meta_key` = '_billing_first_name'"))['meta_value'];
            $order['city'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$order['ID']}' and `meta_key` = '_billing_city'"))['meta_value'];
            $order['email'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$order['ID']}' and `meta_key` = '_billing_email'"))['meta_value'];
            $order['second_name'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$order['ID']}' and `meta_key` = '_billing_new_fild7'"))['meta_value'];


            dd($order);
            $nameAgent = $order['lastName'].' '.$order['first_name'].' '.$order['second_name'];
            $address = $order['city'];
            $createAgent = createAgent($nameAgent, $phone, (string)$order['city'],(string)$order['email']); //создание агента
            dd($createAgent);
            if (!empty($getIdGoods->errors[0])) {
                file_put_contents($logger,date('Y-m-d H:i:s').' ошибка при создании агента - '.$getIdGoods->errors[0]->error.' '.$order['ID']."\n",FILE_APPEND);
                echo $getIdGoods->errors[0]->error;
            }
            file_put_contents($logger,date('Y-m-d H:i:s').' агент создан '.$body['agent'] ."\n",FILE_APPEND);
           
        }
    }else{
        file_put_contents($logger,date('Y-m-d H:i:s').' телефона нет для создания заказа '.$order['ID']."\n",FILE_APPEND);
    }

    }else {
        echo 'заказ уже был создан';
        file_put_contents($logger,date('Y-m-d H:i:s').' уже существует заказ - '.$order['ID']."\n",FILE_APPEND);

    }
}
