<?php 

ini_set('display_errors', 1);
require_once "funcs.php";
require_once "config.php";

//папка для заказов
$orders_dir = __DIR__.'/ordersVM/';       
// dd($orders_dir);exit;

// получение заказов за день 
// $today = date("H:i:s",strtotime(date("H:i:s")." - 400 minutes"));
$s = date("Y-m-d");
// $data = $s." ".$today;

// получение доставки
// $virtuemart_order_id = 26276;
// // $getShipmethod = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_calc_rules` WHERE `virtuemart_order_id` = '$numberOrder ' "));
// $getShipOrder = getShipOrder($db, $virtuemart_order_id);
// dd($getShipOrder);exit;

$getOrdersVM = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `created_on` > '$s' "); // получений заказов за день
// $i = 0;
    foreach ($getOrdersVM as $orderVM ) {
        $body = array();
        $customer_note = '';    
        if (!file_exists($orders_dir.$orderVM['order_number'])) {            // проверка создан или нет заказ уже
            file_put_contents($orders_dir.$orderVM['order_number'], '');
            // dd($orderVM);     
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
            $body['description'] = $customer_note;
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
            
            
            

            $createOrder = createOrder($body);
            exit;
            dd($createOrder);
            file_put_contents('logger.log',date('Y-m-d H:i:s').'заказ--'.$orderVM['order_number'].'-- заказ создан его тело '.$createOrder ."\n",FILE_APPEND);
                
            

        }else {
            file_put_contents('logger.log',date('Y-m-d H:i:s').' заказ уже создан '.$orderVM['order_number'] ."\n",FILE_APPEND);
            echo "заказ уже создан";
        }
        // dd($body);
    }