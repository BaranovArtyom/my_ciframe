<?php 
ini_set('display_errors', 1);

require_once "funcs.php";
require_once "config.php";
require_once "message.php";

define ( "ACCOUNT", "api@manager245" );
define ( "PASSWORD", "api1111" );
define ("URL_HOOK","https://readytodirt.ru/vm_ms/order_huk.php");
define ("ACTION", "UPDATE");

date_default_timezone_set('Europe/London');
$today = date("H:i:s");
$s = date("Y-m-d");
$d = $s." ".$today;

//создание вебхука
// $createHook = createHook( ACCOUNT, PASSWORD, URL_HOOK, ACTION, "customerorder" );

$hook = file_get_contents("php://input");

// получение данных от вебхука при событии в мс 
$getEvents = json_decode($hook);
if (empty($getEvents->events))                  // декодируем в json пришедший ответ при изменении в заказе
{
    file_put_contents('errors.txt',$hook);
}
// file_put_contents("hook.txt",$hook."\n",FILE_APPEND);

$url_event = (string)$getEvents->events[0]->meta->href;
// file_put_contents('hook.txt',date('H:i:s').' url event '.$url_event."\n",FILE_APPEND);
// exit;
$id_url = get_id_from_href($url_event);
$orderUrl = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/"."$id_url";

// file_put_contents('hook.txt',date('H:i:s').' id-url '.$id_url ."\n",FILE_APPEND);
// file_put_contents('hook.txt',date('H:i:s').' url '.$orderUrl ."\n",FILE_APPEND);

$data = getCurlData($id_url, ACCOUNT, PASSWORD);
// dd($data);die();  
$urlStateData = $data->state->meta->href;                   // url статуса заказа
$OrderMs['name']= $data->name;                                 // имя заказа
$OrderMs['description'] = $data->description;                           // описание Сдек доставки

$stateData = getCurlStateData($urlStateData, ACCOUNT, PASSWORD);
$stateName = $stateData->name;                              // имя статуса заказа
// dd($stateData);
      
// file_put_contents('hook.txt',date('H:i:s').' state data'.json_encode($data) ."\n",FILE_APPEND);
file_put_contents('hook.txt',date('H:i:s').' state '.json_encode($stateData) ."\n",FILE_APPEND);
file_put_contents('hook.txt',date('H:i:s').' state '.$stateName ."\n",FILE_APPEND);
file_put_contents('hook.txt',date('H:i:s').' state '.$OrderMs['name']."\n",FILE_APPEND);
file_put_contents('hook.txt',date('H:i:s').' state '.$OrderMs['description']."\n",FILE_APPEND);
// exit;
$getIdVmOrder =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_order_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
// $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'O', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");

file_put_contents('hook.txt',date('H:i:s').' state '.$getIdVmOrder ."\n",FILE_APPEND);
// проверка статуса
if ($stateName == "Подтвержден Тинькофф") {
    $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'U' WHERE `order_number`= '{$OrderMs['name']}'");
    $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
    $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
    $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
    $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
    file_put_contents('hook.txt',date('H:i:s').' state '.$UpdateOrders."\n",FILE_APPEND);
    file_put_contents('hook.txt',date('H:i:s').' state '.$UpdateOrdersNote."\n",FILE_APPEND);
    file_put_contents('hook.txt',date('H:i:s').' state '.$IdHistoryOrders."\n",FILE_APPEND);
    file_put_contents('hook.txt',date('H:i:s').' state '.$UpdatePayNote."\n",FILE_APPEND);
    file_put_contents('hook.txt',date('H:i:s').' state '.$UpdateCustomerOrders."\n",FILE_APPEND);
    file_put_contents('hook.txt',date('H:i:s').' state '.$getStat."\n",FILE_APPEND);
    
    if ($getStat != 'U') { 
        // echo "вставляем новый статус";
        $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'U', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
        

        //получение сум

        $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
        $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
        $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

        //получения доставки и оплаты
        $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
        $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

        // получение данных для заказа отправки

        if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
            $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
            // dd($name_ship);exit;
        }else {
            $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
            $name_ship = $getTextShip[4];
            // $sum_ship =  $getTextShip[7];
        
        }
        
            // dd($name_ship);exit;
        if ($getIdpaymethod == 4) {
            $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
            $name_pay = $getTextPay[4];
            $pay_sum = $getTextPay[5];
        }else {
            $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
            $name_pay = $getTextPay[5];
            $pay_sum = $getTextPay[6];
        }
        
        $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
        foreach ($getItems as $item){
            // dd($item);
            // $it = $items1 = array();
            $it['order_item_name'] = $item['order_item_name'];
            $it['order_item_sku'] = $item['order_item_sku'];
            $it['product_item_price'] = $item['product_item_price'];
            $it['product_final_price'] = $item['product_final_price'];
            $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
            $it['virtuemart_product_id'] = $item['virtuemart_product_id']; // вставить поиск атрибутов товара для вставки

            /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);




            $it['product_quantity'] = $item['product_quantity'];
            // $item1['product_quantity'] = 2;
        
            $it['sum'] = $it['product_quantity']*$it['product_final_price'];
            
            
            $items1[] = $it;
        }

        /**Для смс уведовмления клиенту */
        $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
        $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
        /**получение имени статуса и описание статуса */
        $status_code = 'U'; //тинькоф
        $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
        $order_status_name = $getStatusDesc[3];
        $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
        $email = $getDataUser['19'];
        $to = $email;
        $name = $getDataUser['28'];
        $pass = $getTotalSumm['6'];
        $sum_order = $getTotalSumm['8'];
        $total_sum_order = $getTotalSumm['7'];
        $num_delivery = " ";

        $last_name = $getDataUser['7'];
        $first_name = $getDataUser['8'];
        $city = $getDataUser['15'];
        $address = $getDataUser['13'];
        $phone = $getDataUser['11'];

            $text = textMessage($name,$OrderMs['name'],$pass ,(float)$sum_order,(float)$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
            $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
            $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
                $headers .= "<order2@readytodirt.ru>\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            // dd($headers);
            $text_sub = "Ваш заказ подтвержден магазином и отложен до оплаты! Сумма заказа ";
            $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
            dd($text);


            $success = mail($to, $subject, $text, $headers);   
    }
}elseif ($stateName == "Подтвержден Ирина") {
        $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'M' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        // dd($IdHistoryOrders);
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
        file_put_contents('hook.txt',date('H:i:s').' state '.$IdHistoryOrders."\n",FILE_APPEND);
        if ($getStat != 'M') { 
            // echo "вставляем новый статус";
            $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'M', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

             //получение сум

             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             //получения доставки и оплаты
             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             // получение данных для заказа отправки
 
             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
                 // dd($name_ship);exit;
             }else {
                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_ship = $getTextShip[4];
                 // $sum_ship =  $getTextShip[7];
             
             }
             
                 // dd($name_ship);exit;
             if ($getIdpaymethod == 4) {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[4];
                 $pay_sum = $getTextPay[5];
             }else {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[5];
                 $pay_sum = $getTextPay[6];
             }
             
             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
             foreach ($getItems as $item){
                 // dd($item);
                //  $it = $items1 = array();
                 $it['order_item_name'] = $item['order_item_name'];
                 $it['order_item_sku'] = $item['order_item_sku'];
                 $it['product_item_price'] = $item['product_item_price'];
                 $it['product_final_price'] = $item['product_final_price'];
                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];

                /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);




                 $it['product_quantity'] = $item['product_quantity'];
                 // $item1['product_quantity'] = 2;
             
                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
                 $items1[] = $it;
             }

          /**Для смс уведовмления клиенту */
          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
          /**получение имени статуса и описание статуса */
          $status_code = 'M'; //тинькоф
          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
          $order_status_name = $getStatusDesc[3];
          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
          $email = $getDataUser['19'];
          $to = $email;
          $name = $getDataUser['28'];
          $pass = $getTotalSumm['6'];
          $sum_order = $getTotalSumm['8'];
          $total_sum_order = $getTotalSumm['7'];
          $num_delivery = " ";

          $last_name = $getDataUser['7'];
          $first_name = $getDataUser['8'];
          $city = $getDataUser['15'];
          $address = $getDataUser['13'];
          $phone = $getDataUser['11'];

          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
              $headers .= "<order2@readytodirt.ru>\r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
          $text_sub = "Ваш заказ подтвержден магазином и отложен до оплаты! Сумма заказа ";
          $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
          $success = mail($to, $subject, $text, $headers);
        }

}elseif ($stateName == "Подтвержден Яндекс") {
        $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'O' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
        
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
    if ($getStat != 'O') { 
        // echo "вставляем новый статус";
        $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'O', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");



         //получение сум

         $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
         $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
         $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

         //получения доставки и оплаты
         $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
         $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

         // получение данных для заказа отправки

         if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
             $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             // dd($name_ship);exit;
         }else {
             $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
             $name_ship = $getTextShip[4];
             // $sum_ship =  $getTextShip[7];
         
         }
         
         
             // dd($name_ship);exit;
         if ($getIdpaymethod == 4) {
             $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
             $name_pay = $getTextPay[4];
             $pay_sum = $getTextPay[5];
         }else {
             $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
             $name_pay = $getTextPay[5];
             $pay_sum = $getTextPay[6];
         }
         
         $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
         
         foreach ($getItems as $item){
             // dd($item);
            //  $it = $items1 =array();
             $it['order_item_name'] = $item['order_item_name'];
             $it['order_item_sku'] = $item['order_item_sku'];
             $it['product_item_price'] = $item['product_item_price'];
             $it['product_final_price'] = $item['product_final_price'];
             $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
             $it['virtuemart_product_id'] = $item['virtuemart_product_id'];

            /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);



             $it['product_quantity'] = $item['product_quantity'];
             // $item1['product_quantity'] = 2;
         
             $it['sum'] = $it['product_quantity']*$it['product_final_price'];
             
             
             $items1[] = $it;
         }



         /**Для смс уведовмления клиенту */
         
         $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
         $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
         /**получение имени статуса и описание статуса */
         $status_code = 'O'; //тинькоф
         $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
         $order_status_name = $getStatusDesc[3];
         $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
         $email = $getDataUser['19'];
         $to = $email;
         $name = $getDataUser['28'];
         $pass = $getTotalSumm['6'];
         $sum_order = $getTotalSumm['8'];
         $total_sum_order = $getTotalSumm['7'];
         $num_delivery = " ";

         $last_name = $getDataUser['7'];
         $first_name = $getDataUser['8'];
         $city = $getDataUser['15'];
         $address = $getDataUser['13'];
         $phone = $getDataUser['11'];

          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
         $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
             $headers .= "<order2@readytodirt.ru>\r\n";
             $headers .= "MIME-Version: 1.0\r\n";
             $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        //  dd($headers);

        $text_sub = "Ваш заказ подтвержден магазином и отложен до оплаты! Сумма заказа ";
         $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);



         $success = mail($to, $subject, $text, $headers);
    }

}elseif ($stateName == "Оплачен") {
        $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'C' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];

        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        // dd($insertHistoryOrders);
        // $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        // $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
        // $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
        file_put_contents('hook.txt',date('H:i:s').' state '.$IdHistoryOrders."\n",FILE_APPEND);
        if ($getStat != 'C') { 
            // echo "вставляем новый статус";
            $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'C', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

             //получение сум

             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             //получения доставки и оплаты
             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             // получение данных для заказа отправки
 
             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
                 // dd($name_ship);exit;
             }else {
                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_ship = $getTextShip[4];
                 // $sum_ship =  $getTextShip[7];
             
             }
             
             
                 // dd($name_ship);exit;
             if ($getIdpaymethod == 4) {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[4];
                 $pay_sum = $getTextPay[5];
             }else {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[5];
                 $pay_sum = $getTextPay[6];
             }
             
             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
             foreach ($getItems as $item){
                 // dd($item);
                //  $it = $items1 = array();
                 $it['order_item_name'] = $item['order_item_name'];
                 $it['order_item_sku'] = $item['order_item_sku'];
                 $it['product_item_price'] = $item['product_item_price'];
                 $it['product_final_price'] = $item['product_final_price'];
                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];

                /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);


                 $it['product_quantity'] = $item['product_quantity'];
                 // $item1['product_quantity'] = 2;
             
                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
                 $items1[] = $it;
             }


              /**Для смс уведовмления клиенту */
          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
          /**получение имени статуса и описание статуса */
          $status_code = 'C'; //тинькоф
          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
          $order_status_name = $getStatusDesc[3];
          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
          $email = $getDataUser['19'];
          $to = $email;
          $name = $getDataUser['28'];
          $pass = $getTotalSumm['6'];
          $sum_order = $getTotalSumm['8'];
          $total_sum_order = $getTotalSumm['7'];
          $num_delivery = " ";

          $last_name = $getDataUser['7'];
          $first_name = $getDataUser['8'];
          $city = $getDataUser['15'];
          $address = $getDataUser['13'];
          $phone = $getDataUser['11'];

          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
              $headers .= "<order2@readytodirt.ru>\r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        //   dd($headers);
 
        $text_sub = "Ваш заказ оплачен и готовится к отправке. Сумма заказа ";
          $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
          $success = mail($to, $subject, $text, $headers);
        }
}elseif($stateName == "Подтвержден Игорь") {
        $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'K' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        // dd($insertHistoryOrders);
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
        file_put_contents('hook.txt',date('H:i:s').' state '.$IdHistoryOrders."\n",FILE_APPEND);
        if ($getStat != 'K') { 
            // echo "вставляем новый статус";
            $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'K', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

             //получение сум

             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             //получения доставки и оплаты
             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             // получение данных для заказа отправки
 
             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
                 // dd($name_ship);exit;
             }else {
                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_ship = $getTextShip[4];
                 // $sum_ship =  $getTextShip[7];
             
             }
             
             
                 // dd($name_ship);exit;
             if ($getIdpaymethod == 4) {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[4];
                 $pay_sum = $getTextPay[5];
             }else {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[5];
                 $pay_sum = $getTextPay[6];
             }
             
             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
             foreach ($getItems as $item){
                 // dd($item);
                //  $it = $items1 = array();
                 $it['order_item_name'] = $item['order_item_name'];
                 $it['order_item_sku'] = $item['order_item_sku'];
                 $it['product_item_price'] = $item['product_item_price'];
                 $it['product_final_price'] = $item['product_final_price'];
                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];

                 /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);



                 
                 $it['product_quantity'] = $item['product_quantity'];
                 // $item1['product_quantity'] = 2;
             
                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
                 $items1[] = $it;
             }


                 /**Для смс уведовмления клиенту */
          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
          /**получение имени статуса и описание статуса */
          $status_code = 'K'; //тинькоф
          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
          $order_status_name = $getStatusDesc[3];
          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
          $email = $getDataUser['19'];
          $to = $email;
          $name = $getDataUser['28'];
          $pass = $getTotalSumm['6'];
          $sum_order = $getTotalSumm['8'];
          $total_sum_order = $getTotalSumm['7'];
          $num_delivery = " ";

          $last_name = $getDataUser['7'];
          $first_name = $getDataUser['8'];
          $city = $getDataUser['15'];
          $address = $getDataUser['13'];
          $phone = $getDataUser['11'];

          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
              $headers .= "<order2@readytodirt.ru>\r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        //   dd($headers);
 
        $text_sub = "Ваш заказ подтвержден магазином и отложен до оплаты! Сумма заказа ";
          $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
          $success = mail($to, $subject, $text, $headers);
        }
}elseif($stateName == "Подтвержден ВТБ") {
    $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'F' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
        if ($getStat != 'F') { 
            // echo "вставляем новый статус";
            $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'F', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

            //получение сум

            $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
            $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
            $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

            //получения доставки и оплаты
            $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
            $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

            // получение данных для заказа отправки

            if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
                $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
                // dd($name_ship);exit;
            }else {
                $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
                $name_ship = $getTextShip[4];
                // $sum_ship =  $getTextShip[7];
            
            }
            
            
                // dd($name_ship);exit;
            if ($getIdpaymethod == 4) {
                $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
                $name_pay = $getTextPay[4];
                $pay_sum = $getTextPay[5];
            }else {
                $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
                $name_pay = $getTextPay[5];
                $pay_sum = $getTextPay[6];
            }
            
            $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
            foreach ($getItems as $item){
                // dd($item);
                // $it = $items1 = array();
                $it['order_item_name'] = $item['order_item_name'];
                $it['order_item_sku'] = $item['order_item_sku'];
                $it['product_item_price'] = $item['product_item_price'];
                $it['product_final_price'] = $item['product_final_price'];
                $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
                $it['virtuemart_product_id'] = $item['virtuemart_product_id'];

                /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);

                $it['product_quantity'] = $item['product_quantity'];
                // $item1['product_quantity'] = 2;
            
                $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                
                
                $items1[] = $it;
            }

                   /**Для смс уведовмления клиенту */
          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
          /**получение имени статуса и описание статуса */
          $status_code = 'F'; //тинькоф
          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
          $order_status_name = $getStatusDesc[3];
          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
          $email = $getDataUser['19'];
          $to = $email;
          $name = $getDataUser['28'];
          $pass = $getTotalSumm['6'];
          $sum_order = $getTotalSumm['8'];
          $total_sum_order = $getTotalSumm['7'];
          $num_delivery = " ";

          $last_name = $getDataUser['7'];
          $first_name = $getDataUser['8'];
          $city = $getDataUser['15'];
          $address = $getDataUser['13'];
          $phone = $getDataUser['11'];

          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
              $headers .= "<order2@readytodirt.ru>\r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        //   dd($headers);
 
        $text_sub = "Ваш заказ подтвержден магазином и отложен до оплаты! Сумма заказа ";
          $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
          $success = mail($to, $subject, $text, $headers);
        }
}elseif($stateName == "Заказ предоплачен"){
    $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'A' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
        if ($getStat != 'A') { 
            // echo "вставляем новый статус";
            $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'A', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    
             //получение сум

             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             //получения доставки и оплаты
             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             // получение данных для заказа отправки
 
             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
                 // dd($name_ship);exit;
             }else {
                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_ship = $getTextShip[4];
                 // $sum_ship =  $getTextShip[7];
             
             }
             
             
                 // dd($name_ship);exit;
             if ($getIdpaymethod == 4) {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[4];
                 $pay_sum = $getTextPay[5];
             }else {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[5];
                 $pay_sum = $getTextPay[6];
             }
             
             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
             foreach ($getItems as $item){
                 // dd($item);
                //  $it = $items1 = array();
                 $it['order_item_name'] = $item['order_item_name'];
                 $it['order_item_sku'] = $item['order_item_sku'];
                 $it['product_item_price'] = $item['product_item_price'];
                 $it['product_final_price'] = $item['product_final_price'];
                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];

                /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);

                 $it['product_quantity'] = $item['product_quantity'];
                 // $item1['product_quantity'] = 2;
             
                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
                 $items1[] = $it;
             }




                           /**Для смс уведовмления клиенту */
          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
          /**получение имени статуса и описание статуса */
          $status_code = 'A'; //тинькоф
          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
          $order_status_name = $getStatusDesc[3];
          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
          $email = $getDataUser['19'];
          $to = $email;
          $name = $getDataUser['28'];
          $pass = $getTotalSumm['6'];
          $sum_order = $getTotalSumm['8'];
          $total_sum_order = $getTotalSumm['7'];
          $num_delivery = " ";

        
          $last_name = $getDataUser['7'];
          $first_name = $getDataUser['8'];
          $city = $getDataUser['15'];
          $address = $getDataUser['13'];
          $phone = $getDataUser['11'];

          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
              $headers .= "<order2@readytodirt.ru>\r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        //   dd($headers);
 
        $text_sub = "Ваш заказ предоплачен и отправлен в работу. Сумма заказа ";
          $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
          $success = mail($to, $subject, $text, $headers);
        }
}elseif($stateName == "Подтвержден с изменениями") {
        $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'R' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
        if ($getStat != 'R') { 
            // echo "вставляем новый статус";
            $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'R', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

             //получение сум

             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             //получения доставки и оплаты
             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             // получение данных для заказа отправки
 
             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
                 // dd($name_ship);exit;
             }else {
                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_ship = $getTextShip[4];
                 // $sum_ship =  $getTextShip[7];
             
             }
             
             
                 // dd($name_ship);exit;
             if ($getIdpaymethod == 4) {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[4];
                 $pay_sum = $getTextPay[5];
             }else {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[5];
                 $pay_sum = $getTextPay[6];
             }
             
             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
             foreach ($getItems as $item){
                 // dd($item);
                //  $it = $items1 = array();
                 $it['order_item_name'] = $item['order_item_name'];
                 $it['order_item_sku'] = $item['order_item_sku'];
                 $it['product_item_price'] = $item['product_item_price'];
                 $it['product_final_price'] = $item['product_final_price'];
                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];

                /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);

                 $it['product_quantity'] = $item['product_quantity'];
                 // $item1['product_quantity'] = 2;
             
                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
                 $items1[] = $it;
             }

                            /**Для смс уведовмления клиенту */
          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
          /**получение имени статуса и описание статуса */
          $status_code = 'R'; //тинькоф
          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
          $order_status_name = $getStatusDesc[3];
          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
          $email = $getDataUser['19'];
          $to = $email;
          $name = $getDataUser['28'];
          $pass = $getTotalSumm['6'];
          $sum_order = $getTotalSumm['8'];
          $total_sum_order = $getTotalSumm['7'];

          $num_delivery = " ";

          $last_name = $getDataUser['7'];
          $first_name = $getDataUser['8'];
          $city = $getDataUser['15'];
          $address = $getDataUser['13'];
          $phone = $getDataUser['11'];

          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
              $headers .= "<order2@readytodirt.ru>\r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        //   dd($headers);
        // Ваш заказ подтвержден магазином и отложен до оплаты! Сумма заказа
        $text_sub = "Ваш заказ подтвержден с изменениями и отправлен в работу. Сумма заказа ";
          $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
          $success = mail($to, $subject, $text, $headers);
        }
}elseif($stateName == "Отгружен") {
        $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'S' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
        if ($getStat != 'S') { 
            // echo "вставляем новый статус";
            $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'S', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    


             //получение сум

             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             //получения доставки и оплаты
             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             // получение данных для заказа отправки
 
             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
                 // dd($name_ship);exit;
             }else {
                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_ship = $getTextShip[4];
                 // $sum_ship =  $getTextShip[7];
             
             }
             
             
                 // dd($name_ship);exit;
             if ($getIdpaymethod == 4) {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[4];
                 $pay_sum = $getTextPay[5];
             }else {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[5];
                 $pay_sum = $getTextPay[6];
             }
             
             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
             foreach ($getItems as $item){
                 // dd($item);
                //  $it = $items1 = array();
                 $it['order_item_name'] = $item['order_item_name'];
                 $it['order_item_sku'] = $item['order_item_sku'];
                 $it['product_item_price'] = $item['product_item_price'];
                 $it['product_final_price'] = $item['product_final_price'];
                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];

                /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);


                 $it['product_quantity'] = $item['product_quantity'];
                 // $item1['product_quantity'] = 2;
             
                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
                 $items1[] = $it;
             }




                              /**Для смс уведовмления клиенту */
          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
          /**получение имени статуса и описание статуса */
          $status_code = 'S'; //тинькоф
          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
          $order_status_name = $getStatusDesc[3];
          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
          $email = $getDataUser['19'];
          $to = $email;
          $name = $getDataUser['28'];
          $pass = $getTotalSumm['6'];
          $sum_order = $getTotalSumm['8'];
          $total_sum_order = $getTotalSumm['7'];
          $num_delivery = $getDataUser['30'];

          $last_name = $getDataUser['7'];
          $first_name = $getDataUser['8'];
          $city = $getDataUser['15'];
          $address = $getDataUser['13'];
          $phone = $getDataUser['11'];


        //   dd($num_delivery);
          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
        //   $text .= "<br>".$getDataUser['30'];
        //   dd($text);
          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
              $headers .= "<order2@readytodirt.ru>\r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        //   dd($headers);
 
        $text_sub = "Ваш заказ отправлен. Сумма заказа ";
          $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
          $success = mail($to, $subject, $text, $headers);
        }

}elseif($stateName == "Отменен") {
    $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'X' WHERE `order_number`= '{$OrderMs['name']}'");
        $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
        // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
        $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
        $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
        $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
        if ($getStat != 'X') { 
            // echo "вставляем новый статус";
            $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'X', '1', NULL, '0.00000', NULL, '1', '$d', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

             //получение сум

             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             //получения доставки и оплаты
             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
             // получение данных для заказа отправки
 
             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
                 // dd($name_ship);exit;
             }else {
                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_ship = $getTextShip[4];
                 // $sum_ship =  $getTextShip[7];
             
             }
             
             
                 // dd($name_ship);exit;
             if ($getIdpaymethod == 4) {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[4];
                 $pay_sum = $getTextPay[5];
             }else {
                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
                 $name_pay = $getTextPay[5];
                 $pay_sum = $getTextPay[6];
             }
             
             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
             foreach ($getItems as $item){
                 // dd($item);
                //  $it = $items1 = array();
                 $it['order_item_name'] = $item['order_item_name'];
                 $it['order_item_sku'] = $item['order_item_sku'];
                 $it['product_item_price'] = $item['product_item_price'];
                 $it['product_final_price'] = $item['product_final_price'];
                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];


                /**добавление описаний */
            $getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '{$it['virtuemart_product_id']}' and `virtuemart_order_id` = '$getIdVmOrder' "));
            dd($getAttData);
            dd(($getAttData[20]));
            $attr = json_decode($getAttData[20]);
            
            $it['attr'] = array();
                foreach ($attr as $key=>$val) {
                    dd(($val));
                    dd(($key));
                    if (is_numeric($val)){
                        $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
                        $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
                        echo "true";
                        dd($getNameAtr);
                        dd($getAtt);
                        $textAtr = $getNameAtr." ".$getAtt;
                        $it['attr'][] = $textAtr;
                    }else {
                        echo "false";
                        $textAtr = $val;
                        // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
                        // echo  $textAtr;
                        $it['attr'][] = $textAtr;
                    }
                    
                    // dd($getAtt);
                    // dd($getNameAtr);
                  
                }
                // dd($getAtt);
                // dd($getNameAtr);
                // dd($textAtr);
                dd($Atr);

                 $it['product_quantity'] = $item['product_quantity'];
                 // $item1['product_quantity'] = 2;
             
                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
                 $items1[] = $it;
             }



                                /**Для смс уведовмления клиенту */
          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
          /**получение имени статуса и описание статуса */
          $status_code = 'X'; //тинькоф
          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
          $order_status_name = $getStatusDesc[3];
          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
          $email = $getDataUser['19'];
          $to = $email;
          $name = $getDataUser['28'];

          $last_name = $getDataUser['7'];
          $first_name = $getDataUser['8'];
          $city = $getDataUser['15'];
          $address = $getDataUser['13'];
          $phone = $getDataUser['11'];



          $pass = $getTotalSumm['6'];
          $sum_order = $getTotalSumm['8'];
          $total_sum_order = $getTotalSumm['7'];
          $num_delivery = " ";
          $text = textMessage($name,$OrderMs['name'],$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
          $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$OrderMs['description']);
          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
              $headers .= "<order2@readytodirt.ru>\r\n";
              $headers .= "MIME-Version: 1.0\r\n";
              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        //   dd($headers);
 
        $text_sub = "Ваш заказ отменен. Сумма заказа ";
          $subject = subjectText($OrderMs['name'], $total_sum_order,$text_sub);
          $success = mail($to, $subject, $text, $headers);
        }
        
}else {
    echo "не вставляем новый статус";
}