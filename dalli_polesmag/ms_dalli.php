<?php
ini_set('display_errors', 'on');
require_once "funcs.php";

$orders_dir = __DIR__.'/sync_orders/';       
$fecha = date("Y-m-d", strtotime('+1 day'));                        //дата формирование заказа для dalli приложения
// dd($orders_dir);
dd($fecha);
// echo 3;




/**получение заказа со статусом собран */
$ms_orders = getOrders();
// dd($ms_orders);exit;
foreach ($ms_orders as $order) {
    // dd($order);exit;
    if (!empty($order)) {
        // dd($orders_dir.$order->name);
        // if (!file_exists($orders_dir.$order->name)) {
            file_put_contents($orders_dir.$order->name, ''); 
            // dd($order->payments);die();
            // dd($order->positions->meta->href);exit;
            $items = array();
            // для получение товаров
            $ms_orders_positions =  myCurl($order->positions->meta->href);
            foreach ($ms_orders_positions->rows as $assort){
                $item['quantity'] = $assort->quantity;
                $item['price'] = $assort->price/100;
                $ms_orders_positions_assort =  myCurl($assort->assortment->meta->href);
                // dd($ms_orders_positions_assort);die();
                $item['nameProduct'] = $ms_orders_positions_assort->name;
                $items[] = $item;
            }
            // dd($items);exit;
            // echo $quantity;
            // dd($ms_orders_positions->rows);die();
            $orders['name'] = $order->name; // номер заказа 
            // dd($orders['name']);exit;
            $orders['date'] = $fecha;       // дата заказа
            $orders['total_sum'] = substr($order->sum, 0, -2) . '.00';    // привели сумму к нужному формату
            $orders['positions'] = $order->positions->meta->href;
            $orders['organization'] = $order->organization->meta->href;
            $orders['agent'] = $order->agent->meta->href;
            // dd($orders);exit;
            foreach ($order->attributes as $attribut) {
                // dd($attribut);
                if ( $attribut->name == 'Телефон')
                $orders['phone'] = $attribut->value;
                if ( $attribut->name == 'Адрес доставки')
                // $orders['address'] = $attribut->value;
                // dd($orders['address']);
                // if ( $attribut->name == 'Номер заказа ИМ')
                // $orders['nomer_zakaza'] = $attribut->value;

                if ( $attribut->name == 'Время доставки')
                $orders['dostavka'] = $attribut->value;
                // dd($orders['dostavka']);

                if ( $attribut->name == 'Город')
                $orders['town'] = $attribut->value;

                if ( $attribut->name == 'Способ оплаты')
                $orders['type_oplaty'] = $attribut->value->meta->href;

                if ( $attribut->name == 'Тип доставки')
                $orders['type_dostavka'] = $attribut->value->meta->href;

                if ( $attribut->name == 'Отделение BOXBERRY')
                $orders['pvz_code'] = $attribut->value->meta->href;
            }
            $orders['type_dostavka'] = getTypeDelivery($orders['type_dostavka']); // получение номера service
            // dd($orders['type_dostavka']);
            if ($orders['type_dostavka']=='13'){
                $orders['pvz_code'] = getCodePvz($orders['pvz_code']);  // получение кода pvz
            }
            // dd($orders['pvz_code']);
            // exit;
            $orders['payments'] = getTypePay($orders['type_oplaty']);
            // dd($orders['type_oplaty']);
            // <pvzcode>99451</pvzcode>
                // if ( $order->payedSum > 0) {
                //     $orders['payments'] = 'NO';                             //  оплачен
                // }else {
                //     $orders['payments'] = 'CARD';                           // картой при получении ,оплата картой
                // }
                // dd($orders['payments']);
                if ( $orders['payments'] == 'NO' ) {
                    $orders['inshprice'] = (int)$orders['total_sum'];
                    // $orders['total_sum'] = 0;
                }else {
                    $orders['inshprice'] = (int)$orders['total_sum'];
                }
                $adress = '';
                $agentGetData = myCurl($orders['agent']);                   // получение даныых о заказчике ФИО, эмаил
                    $orders['agent_fio'] = $agentGetData->name; 
                    $orders['agent_email'] = $agentGetData->email; 
                    $orders['actualAddress'] = $agentGetData->actualAddress;
                    $address = explode(",",$orders['actualAddress']);
                    $adress = $address[1].$address[2];
                    // dd($adress);
                $orders['address'] = $adress; 

            // dd($agentGetData);
            // dd($orders['agent_email']);exit;
            // $townOrders = explode(",",$orders['address']);              // получение даных о городе
            // $orders['town'] = $townOrders[1];
                                            // получение адреса для доставки
            // dd($townOrders);die();
            $dostavka_time = explode("-",$orders['dostavka']);          // получение времени для доставки
            $time_min = $dostavka_time[0];
            $time_max = $dostavka_time[1];
            // $type_dostavky = explode(".",$orders['type_dostavka']); 
            // $type_dostavky = $type_dostavky[0];
            // dd($type_dostavky);die();
            // dd($time_min);dd($time_max);
            // dd($dostavka_time);
            dd($orders);

            // $orders['name'] = "888887"; //имя для теста
            if (!empty($orders)) {
                $barcode = '';
                //создание xml для отправки
                $createOrders = createXML($orders['name'], $orders['town'], $orders['address'], $orders['agent_fio'], $orders['phone'], $orders['date'], $time_min, $time_max, $orders['type_dostavka'], $orders['payments'], $orders['total_sum'], $orders['inshprice'], $items, $orders['pvz_code'],$orders['agent_email']);
            
                //добавляем заказ в корзину
                $createOrderBasketDalli = createOrdersInDally('https://api.dalli-service.com/v1/index.php', $createOrders); // добавление заказа в корзину 
                $checks = simplexml_load_string($createOrderBasketDalli);
                // dd($checks);
                /**логирование ошибок при переносе в корзину*/
                $number = '';
                foreach ($checks as $check) {
                    // dd($check);
                    // dd($check['number']);
                    if(isset($check->error)){
                        $number = $check['number'];           // запоминаем номер заказа

                        foreach($check->error as $error){
                            dd($error['errorMessage']);
                            file_put_contents('logger.log',date('Y-m-d H:i:s').'  создания заказа - '.$number.' ошибки по заказу '.$error['errorMessage']."\n",FILE_APPEND);
                        }
                    }
                }
                /**получение заказа из корзины по номеру */

                // $send_body = createXMLGetBasket($orders['name']); // получение содержимого корзины по номеру заказа из dalli для barcode
                // $getBasket = getBasket($send_body);
                // $xml = simplexml_load_string($getBasket);
                // $barcode = $xml->order->barcode;             // получение штрих кода для отправки на доставку
                // // var_dump($barcode);exit;
                // if (!empty($barcode))
                // /**отправка на доставку по штрихкоду */
                // $addBasketInAct = createXMLAddinAct($barcode);
                // $addBasketInAct = createOrdersInDally( $url = 'https://api.dalli-service.com/v1/index.php', $addBasketInAct ); // отправка на доставку заказов из корзины
                // // dd($addBasketInAct);exit;
                // /**получение файла pdf для некоторых заказов за сегодня по штрихкоду */
                // $getPDF = createXMLforPDF($barcode);
                // $getPDF = createOrdersInDally( $url = 'https://api.dalli-service.com/v1/index.php', $getPDF ); // получение файла pdf
                // dd($getPDF);
                // $pathFonderAct = __DIR__."/Act/";   // путь где хранится акты
                // $fullPath = $pathFonderAct.$barcode.'.pdf';                          // полный путь картинки для проверки существования в базе сайта
                // exit;
                // <returnas>base64</returnas>

                // $getBarcode = simplexml_load_string($createOrderBasketDalli);                                            // получение обьекта с штрих кодом заказа
                
                // $barcode = ((string)$getBarcode->order->success->attributes()); 
                // $barcodes[] = $barcode;     //  получение штрих кода
            }else {
                    echo "заказов нет";
            }
        // }else {
        //     echo "заказ существует";
        // }
    }else{
        echo "заказов нет вообще";
    }
    
}