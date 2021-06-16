<?php 
ini_set('display_errors', 1);
require_once "funcs.php";
require_once "config.php";
// echo 1;
require_once "message.php";


// define ( "ACCOUNT", "api@manager245" );
// define ( "PASSWORD", "api1111" );
// define ("URL_HOOK","https://readytodirt.ru/vm_ms/order.php");
// define ("ACTION", "UPDATE");

// // define ( "STATUS_NAME", "Отгружен" );

// $hook = file_get_contents("php://input");

// // получение данных от вебхука при событии в мс 
// $getEvents = json_decode($hook);
// if (empty($getEvents->events))                  // декодируем в json пришедший ответ при изменении в заказе
// {
//     file_put_contents('errors.txt',$hook);
// }
// // dd($getEvents);
// file_put_contents("hook.txt",$hook."\n",FILE_APPEND);

// $url_event = (string)$getEvents->events[0]->meta->href;
// file_put_contents('hook.txt',date('H:i:s').' url event '.$url_event."\n",FILE_APPEND);
// // exit;
// $id_url = get_id_from_href($url_event);
// $orderUrl = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/"."$id_url";
// // $dataTemplate = getTemplateDemand( ACCOUNT, PASSWORD, $url_event); //для шаблона отгрузки
// // $dataTemplatePaymentin = getTemplatePaymentin( ACCOUNT, PASSWORD, $url_event);
// // dd($orderUrl);die();

// file_put_contents('hook.txt',date('H:i:s').' id-url '.$id_url ."\n",FILE_APPEND);
// file_put_contents('hook.txt',date('H:i:s').' url '.$orderUrl ."\n",FILE_APPEND);

// $data = getCurlData($id_url, ACCOUNT, PASSWORD);
// // dd($data);die();  
// $urlStateData = $data->state->meta->href;                   // url статуса заказа
// $numberOrder = $data->name;                                 // номер заказа
// $organizationOrder = $data->organization;                   // ссылка на ваше юрлицо
// $agentOrder = $data->agent;                                 // ссылка на контрагента (покупателя)
// $storeOrder = $data->store;                                 // ссылка на склад 
// // dd($urlStateData);

// $stateData = getCurlStateData($urlStateData, ACCOUNT, PASSWORD);
// $stateName = $stateData->name;                              // имя статуса заказа
// // dd($stateName);die();
// // $bodyDemand = $dataTemplate;                                // тело шаблона статуса заказа
// // $bodyPaymentin = $dataTemplatePaymentin;        
// file_put_contents('hook.txt',date('H:i:s').' state data'.json_encode($data) ."\n",FILE_APPEND);
// file_put_contents('hook.txt',date('H:i:s').' state '.json_encode($stateData) ."\n",FILE_APPEND);
// file_put_contents('hook.txt',date('H:i:s').' state '.$stateName ."\n",FILE_APPEND);
// file_put_contents('hook.txt',date('H:i:s').' state '.$numberOrder ."\n",FILE_APPEND);
// echo 1;
// // создание вебхука
// $createHook = createHook( ACCOUNT, PASSWORD, URL_HOOK, ACTION, "customerorder" );
// exit;


/**Получение статусов в мой складе */
$getStatusOrder = getStatusOrder();
// dd($getStatusOrder);
foreach($getStatusOrder as $metaStatus){
    // dd($metaStatus);
    $status['name'] = $metaStatus->name;
    $status['meta'] = $metaStatus->meta;
    $ordStatus[] = $status;
}
// dd($ordStatus);
// exit;
// dd($ordStatus[7]['meta']);



//папка для заказов
$orders_dir = __DIR__.'/ordersVM/';       
// dd($orders_dir);exit;

date_default_timezone_set('Europe/London');
// получение заказов за день 
// $today = date("H:i:s",strtotime(date("H:i:s")." - 15 minutes"));
// $s = date("Y-m-d");
// $data = $s." ".$today;
// dd($data );exit;
// $s = date("Y-m-d");
$today = date("Y-m-d",strtotime(date("Y-m-d")." - 3 day"));
// $s = date("Y-m-d");
$data = $today;




$getOrdersVM = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `created_on` > '$data' "); // получений заказов за день
// $i = 0;
// dd($getOrdersVM->num_rows);
file_put_contents('logger.log',date('Y-m-d H:i:s').' количество заказов за последние 15 мин '.$getOrdersVM->num_rows ." ". $today."\n",FILE_APPEND);
    foreach ($getOrdersVM as $orderVM ) {
        $body = array();
        $customer_note = $shipment_name  = ''; 
        // dd($orderVM);   exit; 
        if (!file_exists($orders_dir.$orderVM['order_number'])) {            // проверка создан или нет заказ уже
            file_put_contents($orders_dir.$orderVM['order_number'], '');
            // dd($orderVM);     
            // dd($orderVM['virtuemart_paymentmethod_id']);exit;
            if($orderVM['virtuemart_paymentmethod_id'] == 2) {
                $virtuemart_paymentmethod = "Перевод на карту ВТБ";
            }elseif($orderVM['virtuemart_paymentmethod_id'] == 1) {
                $virtuemart_paymentmethod = "Перевод карту Сбербанк (Карта Сбербанка)";

            }elseif($orderVM['virtuemart_paymentmethod_id'] == 4) {
                $virtuemart_paymentmethod = "Банковские карты VISA и MasterCard";

            }elseif($orderVM['virtuemart_paymentmethod_id'] == 6) {
                $virtuemart_paymentmethod = "Перевод на карту Тинькофф";

            }elseif($orderVM['virtuemart_paymentmethod_id'] == 8) {
                $virtuemart_paymentmethod = "Перевод на карту Альфа Банк";

            }elseif($orderVM['virtuemart_paymentmethod_id'] == 9) {
                $virtuemart_paymentmethod = "ЮMoney (ЯндексДеньги)";

            }
            /**получение способа доставки */
            $shipment_name = mysqli_fetch_row(mysqli_query($db,"SELECT `shipment_name` FROM `rtd_virtuemart_shipmentmethods_ru_ru` WHERE `virtuemart_shipmentmethod_id` = '{$orderVM['virtuemart_shipmentmethod_id']}'"))[0]; 
            // dd($shipment_name);
            // echo $shipment_name; exit;
            // $orderVM['virtuemart_order_id'] = 25088;                    //  id заказа
            $getUserVM = getUserVM($db,$orderVM['virtuemart_order_id']);    // получение данных из VM

            // dd($getUserVM );
            $customer_note = (string)$getUserVM['customer_note'];
            // dd($customer_note);exit;
            if(!empty($getUserVM['phone_2']))   {    
                $getAgent = getAgent($getUserVM['phone_2'])->meta->size;    // получение данных агента
                if( $getAgent!=0 ) {                                        // проверка если нет данных, то создаем
                    $body['agent']['meta'] = (array)getAgent($getUserVM['phone_2'])->rows[0]->meta;
                }else{
                    $nameAgent = $getUserVM['last_name']." ".$getUserVM['first_name'];
                    $address = $getUserVM['city']." ".$getUserVM['address_1'];
                    $createAgent = createAgent($nameAgent, $getUserVM['phone_2'], $getUserVM['email'], $address); //создание агента

                    $body['agent']['meta'] = (array)$createAgent->meta;
                    file_put_contents('logger.log',date('Y-m-d H:i:s').' агент создан '.$body['agent'] ."\n",FILE_APPEND);
                    // dd($body['agent']);
                    // exit;
                }
                
                
            }
            else{
                file_put_contents('logger.log',date('Y-m-d H:i:s').' телефона нет для создания заказа '.$orderVM['virtuemart_order_id'] ."\n",FILE_APPEND);
            }
            // dd($getUserVM['phone_2']);
            if (!empty($customer_note)){
                $body['description'] = $customer_note;
            }
            
            // dd($body);exit;
            // тело для заказа
            $body['name'] = $orderVM['order_number']; 
            // dd($body['name']);exit;
            // $body['name'] = "test2";  // тестовое имя
            $body['organization'] = [
                "meta" => array(
                    "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/organization/ee5c966e-ba7f-11e9-9109-f8fc0002e16e",
                    "type"      => "organization",
                    "mediaType" => "application/json"
                    )
            ];
        
            // dd($orderVM['virtuemart_order_id']);exit;

            // получение позиций заказа
            $getPositionOrder = getPositionOrder($db, $orderVM['virtuemart_order_id']);
            foreach ($getPositionOrder as $item) {
                // dd($item);
                $order['quantity'] = (float)$item['product_quantity'];
                $order['price'] = (float)$item['product_final_price']*100;
                $order['assortment']['meta'] = (array)getItemMS($item['virtuemart_product_id']);
                
                $body["positions"][] = $order;
            }

            $order['price'] = (float)$orderVM['order_shipment']*100;
            $order['quantity'] = 1.0;
            $order['assortment']['meta'] = 
                array(
                            "href" => "https://online.moysklad.ru/api/remap/1.2/entity/service/42c535e3-7765-11eb-0a80-065d000c5a46",
                            "metadataHref"=> "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata",
                            "type"=> "service",
                            "mediaType"=> "application/json"
                           
                );
            
            $body["positions"][] = $order;
            $body['store'] = [
                "meta" => array(
                    "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/store/3b37d20e-b1cd-11e9-912f-f3d4001e97db",
                    "type"      => "store",
                    "mediaType" => "application/json"
                    )
            ];
            $body["attributes"][] = 
                [
                  "meta" => array(
                    "href" => "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes/de96af10-8306-11eb-0a80-005a0005a2c7",
                    "type" => "attributemetadata",
                    "mediaType" => "application/json"
                  ),
                  "value"=> $virtuemart_paymentmethod,
                ];
            
            $body["attributes"][] = 
            [
                "meta" => array(
                "href" => "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes/de96b4fb-8306-11eb-0a80-005a0005a2c8",
                "type" => "attributemetadata",
                "mediaType" => "application/json"
                ),
                "value"=> $shipment_name,
            ];
              
            
            
            
            // dd($body);exit;
            $createOrder = createOrder($body);
            // exit;
            // dd($createOrder);
            // file_put_contents('logger.log',date('Y-m-d H:i:s').'заказ--'.$body['order_number']."\n",FILE_APPEND);
            file_put_contents('logger.log',date('Y-m-d H:i:s').'заказ--'.$orderVM['order_number'].'-- заказ создан его тело '.$createOrder ."\n",FILE_APPEND);
                
            

        }else {
            file_put_contents('logger.log',date('Y-m-d H:i:s').' заказ уже создан '.$orderVM['order_number'] ."\n",FILE_APPEND);
            echo "заказ уже создан";
        }
        // dd($body);
    }


/**Проверка статусов заказа из мс с помощью хука */

// if ($stateName == "Подтвержден Ирина") {
//     file_put_contents('hook.txt',date('Y-m-d H:i:s').' заказ уже создан Подтвержден Ирина'."\n",FILE_APPEND);

//         $OrderMs['name'] = $numberOrder;
//         // $OrderMs['description'] = $orderMs->description;
    
//     // dd( $orderMs);exit;
//     // $OrderMs['name'] = "FIZV020652";
//     // exit;
//         $getIdVmOrder =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_order_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//         $OrderMs['description'] =  mysqli_fetch_row(mysqli_query($db,"SELECT customer_note FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder'"))[0];


//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'M' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         // dd($IdHistoryOrders);
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'M') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'M', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

//              //получение сум

//              $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              //получения доставки и оплаты
//              $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              // получение данных для заказа отправки
 
//              if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                  $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                  // dd($name_ship);exit;
//              }else {
//                  $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_ship = $getTextShip[4];
//                  // $sum_ship =  $getTextShip[7];
             
//              }
             
             
//                  // dd($name_ship);exit;
//              if ($getIdpaymethod == 4) {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[4];
//                  $pay_sum = $getTextPay[5];
//              }else {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[5];
//                  $pay_sum = $getTextPay[6];
//              }
             
//              $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//              foreach ($getItems as $item){
//                  // dd($item);
//                  $it = $items1 = array();
//                  $it['order_item_name'] = $item['order_item_name'];
//                  $it['order_item_sku'] = $item['order_item_sku'];
//                  $it['product_item_price'] = $item['product_item_price'];
//                  $it['product_final_price'] = $item['product_final_price'];
//                  $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                  $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                  $it['product_quantity'] = $item['product_quantity'];
//                  // $item1['product_quantity'] = 2;
             
//                  $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
//                  $items1[] = $it;
//              }



//           /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'M'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];
//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];
//           $num_delivery = " ";

//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];

//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);

// }





// /**Получение заказов за день из мс  для статусов*/
// $getOrderMs = getOrderMs();
// // dd($getOrderMs);exit;

// $orderMs = array();
// foreach ($getOrderMs as $orderMs){
//     $getStat = " ";
//     $OrderMs['name'] = $orderMs->name;
//     $OrderMs['state'] = $orderMs->state->meta->href;
//     $OrderMs['description'] = $orderMs->description;
//     dd($OrderMs['description']);
//     dd($OrderMs['name']);
//     dd($OrderMs['state']);
//     // dd( $orderMs);exit;
//     // $OrderMs['name'] = "FIZV020652";
//     // exit;
//     $getIdVmOrder =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_order_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
    
//     // $get_user_info =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder'")); 

//     // dd($get_user_info);exit;
//     // $email = $get_user_info['19'];
//     // dd(  $email)
//     // $OrderMs['name'] = "17NU020633";
//     // $IdHistoryOrders = 26647;
//     $s = date("Y-m-d H:i:s");
//     // // $data = $s." ".$today;
//     // // dd($s);
//     // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'O', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // exit;
  
//     // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//     // $OrderMs['state'] = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/3b4a36de-b1cd-11e9-912f-f3d4001e97fc";
//     // $UpdateHistoryOrders = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC");
//     // $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$IdHistoryOrders'");
//     // dd($UpdatePayNote);
//     // exit;
//     // foreach ($UpdateHistoryOrders as $val){
//     //     dd($val);
//     // }
//     // dd($UpdateHistoryOrders);
//     // exit;
   
//     if($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/06bfc5d1-b696-11e9-912f-f3d400060ba6"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'O' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
        
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         // dd($UpdatePayNote);
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
        
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//     if ($getStat != 'O') { 
//         // echo "вставляем новый статус";
//         $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'O', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");



//          //получение сум

//          $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//          $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//          $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

//          //получения доставки и оплаты
//          $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//          $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

//          // получение данных для заказа отправки

//          if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//              $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              // dd($name_ship);exit;
//          }else {
//              $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//              $name_ship = $getTextShip[4];
//              // $sum_ship =  $getTextShip[7];
         
//          }
         
         
//              // dd($name_ship);exit;
//          if ($getIdpaymethod == 4) {
//              $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//              $name_pay = $getTextPay[4];
//              $pay_sum = $getTextPay[5];
//          }else {
//              $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//              $name_pay = $getTextPay[5];
//              $pay_sum = $getTextPay[6];
//          }
         
//          $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
         
//          foreach ($getItems as $item){
//              // dd($item);
//              $it = $items1 =array();
//              $it['order_item_name'] = $item['order_item_name'];
//              $it['order_item_sku'] = $item['order_item_sku'];
//              $it['product_item_price'] = $item['product_item_price'];
//              $it['product_final_price'] = $item['product_final_price'];
//              $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//              $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//              $it['product_quantity'] = $item['product_quantity'];
//              // $item1['product_quantity'] = 2;
         
//              $it['sum'] = $it['product_quantity']*$it['product_final_price'];
             
             
//              $items1[] = $it;
//          }



//          /**Для смс уведовмления клиенту */
         
//          $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//          $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//          /**получение имени статуса и описание статуса */
//          $status_code = 'O'; //тинькоф
//          $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//          $order_status_name = $getStatusDesc[3];
//          $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//          $email = $getDataUser['19'];
//          $to = $email;
//          $name = $getDataUser['28'];
//          $pass = $getTotalSumm['6'];
//          $sum_order = $getTotalSumm['8'];
//          $total_sum_order = $getTotalSumm['7'];
//          $num_delivery = " ";

//          $last_name = $getDataUser['7'];
//          $first_name = $getDataUser['8'];
//          $city = $getDataUser['15'];
//          $address = $getDataUser['13'];
//          $phone = $getDataUser['11'];

//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//          $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//              $headers .= "<order2@readytodirt.ru>\r\n";
//              $headers .= "MIME-Version: 1.0\r\n";
//              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //  dd($headers);

//          $subject = subjectText($OrderMs['name'], $total_sum_order);



//          $success = mail($to, $subject, $text, $headers);
//         //  dd($success);
        
//     }else {
//         echo "не вставляем новый статус";
//     }
//     // dd($getStat);



//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'O', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     //    dd($insertHistoryOrders);
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'O' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Заказ подтвержден. Ожидаем оплату (Яндекс)"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);

//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/3b4a36de-b1cd-11e9-912f-f3d4001e97fc"){
//         echo "подтверждение ирина";
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'M' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         // dd($IdHistoryOrders);
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'M') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'M', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

//              //получение сум

//              $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              //получения доставки и оплаты
//              $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              // получение данных для заказа отправки
 
//              if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                  $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                  // dd($name_ship);exit;
//              }else {
//                  $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_ship = $getTextShip[4];
//                  // $sum_ship =  $getTextShip[7];
             
//              }
             
             
//                  // dd($name_ship);exit;
//              if ($getIdpaymethod == 4) {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[4];
//                  $pay_sum = $getTextPay[5];
//              }else {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[5];
//                  $pay_sum = $getTextPay[6];
//              }
             
//              $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//              foreach ($getItems as $item){
//                  // dd($item);
//                  $it = $items1 = array();
//                  $it['order_item_name'] = $item['order_item_name'];
//                  $it['order_item_sku'] = $item['order_item_sku'];
//                  $it['product_item_price'] = $item['product_item_price'];
//                  $it['product_final_price'] = $item['product_final_price'];
//                  $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                  $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                  $it['product_quantity'] = $item['product_quantity'];
//                  // $item1['product_quantity'] = 2;
             
//                  $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
//                  $items1[] = $it;
//              }



//           /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'M'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];
//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];
//           $num_delivery = " ";

//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];

//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);
//         //   dd($success);
            
//         }else {
//             echo "не вставляем новый статус";
//         }
    
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'M', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd("INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'M', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'M' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Заказ подтвержден. Ожидаем оплату (Сбербанк)"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // dd($UpdatePayNote);exit;
//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/3b4a381f-b1cd-11e9-912f-f3d4001e97ff"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'C' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         // dd($insertHistoryOrders);
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'C') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'C', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

//              //получение сум

//              $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              //получения доставки и оплаты
//              $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              // получение данных для заказа отправки
 
//              if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                  $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                  // dd($name_ship);exit;
//              }else {
//                  $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_ship = $getTextShip[4];
//                  // $sum_ship =  $getTextShip[7];
             
//              }
             
             
//                  // dd($name_ship);exit;
//              if ($getIdpaymethod == 4) {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[4];
//                  $pay_sum = $getTextPay[5];
//              }else {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[5];
//                  $pay_sum = $getTextPay[6];
//              }
             
//              $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//              foreach ($getItems as $item){
//                  // dd($item);
//                  $it = $items1 = array();
//                  $it['order_item_name'] = $item['order_item_name'];
//                  $it['order_item_sku'] = $item['order_item_sku'];
//                  $it['product_item_price'] = $item['product_item_price'];
//                  $it['product_final_price'] = $item['product_final_price'];
//                  $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                  $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                  $it['product_quantity'] = $item['product_quantity'];
//                  // $item1['product_quantity'] = 2;
             
//                  $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
//                  $items1[] = $it;
//              }


//               /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'C'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];
//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];
//           $num_delivery = " ";

//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];

//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);

//         //   dd($success);
//         }else {
//             echo "не вставляем новый статус";
//         }
    
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'C', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd($insertHistoryOrders);
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'C' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Оплачен"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // echo $OrderMs['state']."-Оплачен-".$OrderMs['name'];
//         // dd($UpdatePayNote);
//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/06bfbfde-b696-11e9-912f-f3d400060ba5"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'K' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         // dd($insertHistoryOrders);
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'K') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'K', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

//              //получение сум

//              $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              //получения доставки и оплаты
//              $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              // получение данных для заказа отправки
 
//              if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                  $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                  // dd($name_ship);exit;
//              }else {
//                  $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_ship = $getTextShip[4];
//                  // $sum_ship =  $getTextShip[7];
             
//              }
             
             
//                  // dd($name_ship);exit;
//              if ($getIdpaymethod == 4) {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[4];
//                  $pay_sum = $getTextPay[5];
//              }else {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[5];
//                  $pay_sum = $getTextPay[6];
//              }
             
//              $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//              foreach ($getItems as $item){
//                  // dd($item);
//                  $it = $items1 = array();
//                  $it['order_item_name'] = $item['order_item_name'];
//                  $it['order_item_sku'] = $item['order_item_sku'];
//                  $it['product_item_price'] = $item['product_item_price'];
//                  $it['product_final_price'] = $item['product_final_price'];
//                  $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                  $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                  $it['product_quantity'] = $item['product_quantity'];
//                  // $item1['product_quantity'] = 2;
             
//                  $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
//                  $items1[] = $it;
//              }


//                  /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'K'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];
//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];
//           $num_delivery = " ";

//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];

//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);
          
//         //   dd($success);
            
//         }else {
//             echo "не вставляем новый статус";
//         }
    
    
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'K', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd($insertHistoryOrders);
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'K' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Заказ подтвержден. Ожидаем оплату (Сбер)"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // dd($UpdatePayNote);
//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/f07d97a3-c250-11e9-9107-50480014df81"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'U' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'U') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'U', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
            

//             //получение сум

//             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

//             //получения доставки и оплаты
//             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

//             // получение данных для заказа отправки

//             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                 // dd($name_ship);exit;
//             }else {
//                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                 $name_ship = $getTextShip[4];
//                 // $sum_ship =  $getTextShip[7];
            
//             }
            
            
//                 // dd($name_ship);exit;
//             if ($getIdpaymethod == 4) {
//                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                 $name_pay = $getTextPay[4];
//                 $pay_sum = $getTextPay[5];
//             }else {
//                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                 $name_pay = $getTextPay[5];
//                 $pay_sum = $getTextPay[6];
//             }
            
//             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//             foreach ($getItems as $item){
//                 // dd($item);
//                 $it = $items1 = array();
//                 $it['order_item_name'] = $item['order_item_name'];
//                 $it['order_item_sku'] = $item['order_item_sku'];
//                 $it['product_item_price'] = $item['product_item_price'];
//                 $it['product_final_price'] = $item['product_final_price'];
//                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                 $it['product_quantity'] = $item['product_quantity'];
//                 // $item1['product_quantity'] = 2;
            
//                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                
                
//                 $items1[] = $it;
//             }

//             // dd($items1);
//             // dd( $getIdshipmethod);
//             // dd($getIdpaymethod);
//             // dd($name_pay);
//             // dd($pay_sum);
//             // dd($getSumshipment);
//             // dd($get_order_salesPrice);
//             // dd($get_order_total);
            
            
//             // exit;


//             /**Для смс уведовмления клиенту */
//             $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//             $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//             /**получение имени статуса и описание статуса */
//             $status_code = 'U'; //тинькоф
//             $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//             $order_status_name = $getStatusDesc[3];
//             $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//             $email = $getDataUser['19'];
//             $to = $email;
//             $name = $getDataUser['28'];
//             $pass = $getTotalSumm['6'];
//             $sum_order = $getTotalSumm['8'];
//             $total_sum_order = $getTotalSumm['7'];
//             $num_delivery = " ";

//             $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];

//             $text = textMessage($name,$OrderMs['name'],$pass ,(float)$sum_order,(float)$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//             $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//             $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//                 $headers .= "<order2@readytodirt.ru>\r\n";
//                 $headers .= "MIME-Version: 1.0\r\n";
//                 $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//             // dd($headers);

//             $subject = subjectText($OrderMs['name'], $total_sum_order);



//             $success = mail($to, $subject, $text, $headers);
//             // dd($success);

            
//         }else {
//             echo "не вставляем новый статус";
//         }
    
    
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'U', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd($insertHistoryOrders);
        
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'U' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Заказ подтвержден. Ожидаем оплату (Тинькофф)"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // dd($UpdatePayNote);
//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/aac65d4a-b8d3-11e9-9ff4-34e800011efc"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'F' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'F') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'F', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

//             //получение сум

//             $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//             $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//             $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

//             //получения доставки и оплаты
//             $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//             $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

//             // получение данных для заказа отправки

//             if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                 $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                 // dd($name_ship);exit;
//             }else {
//                 $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                 $name_ship = $getTextShip[4];
//                 // $sum_ship =  $getTextShip[7];
            
//             }
            
            
//                 // dd($name_ship);exit;
//             if ($getIdpaymethod == 4) {
//                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                 $name_pay = $getTextPay[4];
//                 $pay_sum = $getTextPay[5];
//             }else {
//                 $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                 $name_pay = $getTextPay[5];
//                 $pay_sum = $getTextPay[6];
//             }
            
//             $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//             foreach ($getItems as $item){
//                 // dd($item);
//                 $it = $items1 = array();
//                 $it['order_item_name'] = $item['order_item_name'];
//                 $it['order_item_sku'] = $item['order_item_sku'];
//                 $it['product_item_price'] = $item['product_item_price'];
//                 $it['product_final_price'] = $item['product_final_price'];
//                 $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                 $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                 $it['product_quantity'] = $item['product_quantity'];
//                 // $item1['product_quantity'] = 2;
            
//                 $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                
                
//                 $items1[] = $it;
//             }

//                    /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'F'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];
//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];
//           $num_delivery = " ";

//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];

//         //   $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//         //   $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);
          
//         //   dd($success);
            
//         }else {
//             echo "не вставляем новый статус";
//         }
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'F', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd($insertHistoryOrders);
        
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'F' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Заказ подтвержден. Ожидаем оплату (ВТБ)"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // dd($UpdatePayNote);
//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/48dcc5be-c56b-11e9-9109-f8fc0001b362c"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'A' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'A') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'A', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    
//              //получение сум

//              $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              //получения доставки и оплаты
//              $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              // получение данных для заказа отправки
 
//              if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                  $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                  // dd($name_ship);exit;
//              }else {
//                  $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_ship = $getTextShip[4];
//                  // $sum_ship =  $getTextShip[7];
             
//              }
             
             
//                  // dd($name_ship);exit;
//              if ($getIdpaymethod == 4) {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[4];
//                  $pay_sum = $getTextPay[5];
//              }else {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[5];
//                  $pay_sum = $getTextPay[6];
//              }
             
//              $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//              foreach ($getItems as $item){
//                  // dd($item);
//                  $it = $items1 = array();
//                  $it['order_item_name'] = $item['order_item_name'];
//                  $it['order_item_sku'] = $item['order_item_sku'];
//                  $it['product_item_price'] = $item['product_item_price'];
//                  $it['product_final_price'] = $item['product_final_price'];
//                  $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                  $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                  $it['product_quantity'] = $item['product_quantity'];
//                  // $item1['product_quantity'] = 2;
             
//                  $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
//                  $items1[] = $it;
//              }




//                            /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'A'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];
//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];
//           $num_delivery = " ";

        
//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];

//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);
          
//         //   dd($success);
            
//         }else {
//             echo "не вставляем новый статус";
//         }
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'A', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd($insertHistoryOrders);
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'A' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Заказ предоплачен"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // dd($UpdatePayNote);
//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/c3ab782d-b842-11ea-0a80-027f00017fa5"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'R' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'R') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'R', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

//              //получение сум

//              $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              //получения доставки и оплаты
//              $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              // получение данных для заказа отправки
 
//              if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                  $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                  // dd($name_ship);exit;
//              }else {
//                  $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_ship = $getTextShip[4];
//                  // $sum_ship =  $getTextShip[7];
             
//              }
             
             
//                  // dd($name_ship);exit;
//              if ($getIdpaymethod == 4) {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[4];
//                  $pay_sum = $getTextPay[5];
//              }else {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[5];
//                  $pay_sum = $getTextPay[6];
//              }
             
//              $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//              foreach ($getItems as $item){
//                  // dd($item);
//                  $it = $items1 = array();
//                  $it['order_item_name'] = $item['order_item_name'];
//                  $it['order_item_sku'] = $item['order_item_sku'];
//                  $it['product_item_price'] = $item['product_item_price'];
//                  $it['product_final_price'] = $item['product_final_price'];
//                  $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                  $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                  $it['product_quantity'] = $item['product_quantity'];
//                  // $item1['product_quantity'] = 2;
             
//                  $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
//                  $items1[] = $it;
//              }






//                             /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'R'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];
//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];

//           $num_delivery = " ";

//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];

//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);
          
//         //   dd($success);
            
//         }else {
//             echo "не вставляем новый статус";
//         }
    
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'R', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd($insertHistoryOrders);
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'R' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Подтвержден с изменениями"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // dd($UpdatePayNote);
//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/3b4a37ba-b1cd-11e9-912f-f3d4001e97fe"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'S' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'S') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'S', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    


//              //получение сум

//              $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              //получения доставки и оплаты
//              $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              // получение данных для заказа отправки
 
//              if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                  $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                  // dd($name_ship);exit;
//              }else {
//                  $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_ship = $getTextShip[4];
//                  // $sum_ship =  $getTextShip[7];
             
//              }
             
             
//                  // dd($name_ship);exit;
//              if ($getIdpaymethod == 4) {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[4];
//                  $pay_sum = $getTextPay[5];
//              }else {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[5];
//                  $pay_sum = $getTextPay[6];
//              }
             
//              $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//              foreach ($getItems as $item){
//                  // dd($item);
//                  $it = $items1 = array();
//                  $it['order_item_name'] = $item['order_item_name'];
//                  $it['order_item_sku'] = $item['order_item_sku'];
//                  $it['product_item_price'] = $item['product_item_price'];
//                  $it['product_final_price'] = $item['product_final_price'];
//                  $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                  $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                  $it['product_quantity'] = $item['product_quantity'];
//                  // $item1['product_quantity'] = 2;
             
//                  $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
//                  $items1[] = $it;
//              }




//                               /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'S'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];
//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];
//           $num_delivery = $getDataUser['30'];

//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];


//         //   dd($num_delivery);
//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//         //   $text .= "<br>".$getDataUser['30'];
//         //   dd($text);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);
          
//         //   dd($success);
            
//         }else {
//             echo "не вставляем новый статус";
//         }
    
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'S', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd($insertHistoryOrders);
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'S' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Отгружен"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // dd($UpdatePayNote);
//     }elseif($OrderMs['state'] == "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/3b4a38e8-b1cd-11e9-912f-f3d4001e9801"){
//         $UpdateOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `order_status` = 'X' WHERE `order_number`= '{$OrderMs['name']}'");
//         $UpdateOrdersNote = mysqli_query($db,"UPDATE `rtd_virtuemart_orders` SET `customer_note` = '{$OrderMs['description']}' WHERE `order_number`= '{$OrderMs['name']}'");
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];
//         $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`virtuemart_order_history_id` DESC"))[0];
//         $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$getIdVmOrder'");
//         $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
    
//         $getStat = mysqli_fetch_row(mysqli_query($db,"SELECT order_status_code FROM `rtd_virtuemart_order_histories` WHERE `virtuemart_order_id` = '$getIdVmOrder' ORDER BY `virtuemart_order_history_id` DESC"))[0];
//         if ($getStat != 'X') { 
//             // echo "вставляем новый статус";
//             $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'X', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
    

//              //получение сум

//              $getSumshipment =  mysqli_fetch_row(mysqli_query($db,"SELECT order_shipment FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_salesPrice =  mysqli_fetch_row(mysqli_query($db,"SELECT order_salesPrice FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $get_order_total =  mysqli_fetch_row(mysqli_query($db,"SELECT order_total FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              //получения доставки и оплаты
//              $getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//              $getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
 
//              // получение данных для заказа отправки
 
//              if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
//                  $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
//                  // dd($name_ship);exit;
//              }else {
//                  $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_ship = $getTextShip[4];
//                  // $sum_ship =  $getTextShip[7];
             
//              }
             
             
//                  // dd($name_ship);exit;
//              if ($getIdpaymethod == 4) {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[4];
//                  $pay_sum = $getTextPay[5];
//              }else {
//                  $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
//                  $name_pay = $getTextPay[5];
//                  $pay_sum = $getTextPay[6];
//              }
             
//              $getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
//              foreach ($getItems as $item){
//                  // dd($item);
//                  $it = $items1 = array();
//                  $it['order_item_name'] = $item['order_item_name'];
//                  $it['order_item_sku'] = $item['order_item_sku'];
//                  $it['product_item_price'] = $item['product_item_price'];
//                  $it['product_final_price'] = $item['product_final_price'];
//                  $it['product_subtotal_discount'] = $item['product_subtotal_discount'];
//                  $it['virtuemart_product_id'] = $item['virtuemart_product_id'];
//                  $it['product_quantity'] = $item['product_quantity'];
//                  // $item1['product_quantity'] = 2;
             
//                  $it['sum'] = $it['product_quantity']*$it['product_final_price'];
                 
                 
//                  $items1[] = $it;
//              }



//                                 /**Для смс уведовмления клиенту */
//           $getDataUser =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
//           $getTotalSumm = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `virtuemart_order_id` = '$getIdVmOrder'"));
//           /**получение имени статуса и описание статуса */
//           $status_code = 'X'; //тинькоф
//           $getStatusDesc = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orderstates` WHERE `order_status_code` = '$status_code'"));
//           $order_status_name = $getStatusDesc[3];
//           $order_status_desc = htmlspecialchars_decode($getStatusDesc[5]);
//           $email = $getDataUser['19'];
//           $to = $email;
//           $name = $getDataUser['28'];

//           $last_name = $getDataUser['7'];
//           $first_name = $getDataUser['8'];
//           $city = $getDataUser['15'];
//           $address = $getDataUser['13'];
//           $phone = $getDataUser['11'];



//           $pass = $getTotalSumm['6'];
//           $sum_order = $getTotalSumm['8'];
//           $total_sum_order = $getTotalSumm['7'];
//           $num_delivery = " ";
//           $text = textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
//           $name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone);
//           $headers = "From: Ready to Dirt! Запчасти и экипировка для мотоциклов ";
//               $headers .= "<order2@readytodirt.ru>\r\n";
//               $headers .= "MIME-Version: 1.0\r\n";
//               $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
//         //   dd($headers);
 
//           $subject = subjectText($OrderMs['name'], $total_sum_order);
//           $success = mail($to, $subject, $text, $headers);
          
//         //   dd($success);
            
//         }else {
//             echo "не вставляем новый статус";
//         }
    
    
//         // $insertHistoryOrders = mysqli_query($db,"INSERT INTO `rtd_virtuemart_order_histories` (`virtuemart_order_history_id`, `virtuemart_order_id`, `order_status_code`, `customer_notified`, `comments`, `paid`, `o_hash`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES (NULL, '$getIdVmOrder', 'X', '1', NULL, '0.00000', NULL, '1', '$s', '0', '0000-00-00 00:00:00.000000', '0', '0000-00-00 00:00:00.000000', '0')");
//     // dd($insertHistoryOrders);
//         // $UpdateHistoryOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `order_status_code` = 'X' WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "Отменен"."$UpdateOrders"."$UpdateHistoryOrders"."\n",FILE_APPEND);
//         // dd($UpdatePayNote);
//     }else {
//         // $IdHistoryOrders =  mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_histories`  WHERE `virtuemart_order_id`= '$getIdVmOrder' ORDER BY `rtd_virtuemart_order_histories`.`modified_on` DESC"))[0];

//         // $UpdatePayNote = mysqli_query($db,"UPDATE `rtd_virtuemart_order_userinfos` SET `customer_note` = '{$OrderMs['description']}' WHERE `virtuemart_order_id`= '$IdHistoryOrders'");
//         // $UpdateCustomerOrders = mysqli_query($db,"UPDATE `rtd_virtuemart_order_histories` SET `customer_notified` = 1 WHERE `virtuemart_order_history_id`= '$IdHistoryOrders'");
//         echo "статус не изменен".$OrderMs['state'];
//         file_put_contents('logger.log',date('Y-m-d H:i:s').' обновлен заказ  '.$OrderMs['name'] ." ". "статус не изменен".$OrderMs['state']."\n",FILE_APPEND);
//     }
//     // изменение комментария в 
//     // exit;

// }
// // dd($ordsMS);
// // exit;

