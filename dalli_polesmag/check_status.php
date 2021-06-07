<?php

ini_set('display_errors', 'on');
require_once "funcs.php";

/**получение статусов заказов которые обновили */
$createXMLStatusOrders = createXMLStatusOrders(); // создание Xml файла для запроса статусов
dd($createXMLStatusOrders);

/**получение информации статусов заказов */
$getOrderStatus = createOrdersInDally('https://api.dalli-service.com/v1/index.php', $createXMLStatusOrders); 
dd($getOrderStatus);

    $xml = simplexml_load_string($getOrderStatus);
// dd($xml);exit;
    foreach ($xml->order as $order) {
        // dd($order);exit;
        dd($order['orderno']);
        $status = (string)$order->status['title'];
        $num_order = $order['orderno'];
        echo $num_order .'  '.$status;
        // $num_order = '00068';
         /**получение ид для изменение доп.поля в заказе */
        $getIdOrder = getIdOrder($num_order);
        // dd($getIdOrder);exit;
       
        /**изменение доп.поля статуса заказа в заказе */
        $PutStatus = PutStatus($status, $getIdOrder);
        dd($PutStatus);
        // exit;

        
        // exit;
        
    }
    /**После успешной обработки ответа необходимо отметить полученные статусы успешно полученными, отправив запрос: */
    $createXMLcommitlaststatus = createXMLcommitlaststatus(); // формирование тела
    $commitlaststatus = createOrdersInDally('https://api.dalli-service.com/v1/index.php', $createXMLcommitlaststatus); 
    dd($commitlaststatus);
    
