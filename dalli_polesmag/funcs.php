<?php
ini_set('display_errors', 'on');

/**function dd */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

/**получение заказов мс со статусом собран */
function getOrders() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    // CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder?filter=state=https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/7ccdb391-9538-11eb-0a80-07a5001e1342',
    /**для тестирования */
    // CURLOPT_URL =>'https://online.moysklad.ru/api/remap/1.2/entity/customerorder?filter=state=https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/7ccdb391-9538-11eb-0a80-07a5001e1342',
    CURLOPT_URL =>'https://online.moysklad.ru/api/remap/1.2/entity/customerorder?filter=state=https://online.moysklad.ru/api/remap/1.3/entity/customerorder/metadata/states/6bdb6315-d3fd-11eb-0a80-083b0006070c',


    CURLOPT_USERPWD=> "admin@poleznmagaz:ec0055d6bf",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response);

    curl_close($curl);
    return $response->rows;
    // return $response;
}

/**
 * функция для get запросов 
 */
function myCurl($url, $method='GET', $body=[], $filter = [])   {

    $ch = curl_init();                              // создание нового ресурса cURL
    // $send_body=json_encode($body);
    
    // добавление фильтра
    if(!empty($filter))
    $url .= "?filter={$filter['name']}={$filter['value']}";
    
    // установка URL и других необходимых параметров
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "admin@poleznmagaz:ec0055d6bf");
    curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $send_body);

    $out=curl_exec($ch);
    // завершение сеанса и освобождение ресурсов
    curl_close($ch);
    $json=json_decode($out);
    
    return $json;
}

/**получение типа доставки*/
function getTypeDelivery($url) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_USERPWD=> "admin@poleznmagaz:ec0055d6bf",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    $response = json_decode($response);
    
    curl_close($curl);
    return $response->code;
}

/**получение кода pvz */
function getCodePvz($url) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_USERPWD=> "admin@poleznmagaz:ec0055d6bf",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    $response = json_decode($response);
    
    curl_close($curl);
    return $response->code;

}

/**получение типа доставки*/
function getTypePay($url) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_USERPWD=> "admin@poleznmagaz:ec0055d6bf",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    $response = json_decode($response);
    
    curl_close($curl);
    return $response->code;
}


  /**
     * создание xml для заказа в dalli
     */

    function createXML ($name, $town, $address, $person, $phone, $date, $time_min, $time_max, $service, $paytype, $price, $inshprice, $its,$pvz=NULL,$email=NULL) {
        // dd($address);exit;
        $xml = new DomDocument('1.0','utf-8');
        $xml->formatOutput = true;

        $basketcreate = $xml->createElement('basketcreate');   // создание тега basketcreate
        $xml->appendChild($basketcreate);

            $auth = $xml->createElement('auth');               // создание тега auth
            $auth->setAttribute("token", "608333adc72f545078ede3aad71bfe74");
            $basketcreate->appendChild($auth);

            $order = $xml->createElement('order');               // создание тега order
            $order->setAttribute("number", $name);
            $basketcreate->appendChild($order);

            $receiver = $xml->createElement('receiver');        // создание тега receiver
            $order->appendChild($receiver);

            // $orders['town'] = 'Москва';
            $town = $xml->createElement('town',$town); // создание тега town
            $receiver->appendChild($town);

            $address = $xml->createElement('address',$address); // создание тега address
            $receiver->appendChild($address);

            $person = $xml->createElement('person',$person); // создание тега person
            $receiver->appendChild($person);

            if (isset($pvz)) {
                $pvz = $xml->createElement('pvzcode',$pvz); // создание тега phone
                $receiver->appendChild($pvz);
            }

            if (isset($phone) or isset($email)) {
                $phone = $xml->createElement('phone',$phone.'   '.$email); // создание тега phone
                $receiver->appendChild($phone);
            }else{
                $phone = $xml->createElement('phone','11111111'); // создание тега phone
                $receiver->appendChild($phone);
            }
            // $phone = $xml->createElement('phone',$phone); // создание тега phone
            // $receiver->appendChild($phone);

            // $orders['date'] = explode (" ", $date);   // дата создания заказа
            // $orders['date'] = $date;
            // $orders['date'] = '2020-12-16';                     // тестовая дата
            $date = $xml->createElement('date',$date); // создание тега data
            $receiver->appendChild($date);

            // $time_min = '09:00';
            $time_min = $xml->createElement('time_min',$time_min); // создание тега time_min
            $receiver->appendChild($time_min);

            // $orders['time_max'] = '15:00';
            $time_max = $xml->createElement('time_max',$time_max); // создание тега time_max
            $receiver->appendChild($time_max);

            $service = $xml->createElement('service', $service);        // создание тега service
            $order->appendChild($service);

            $quantity = $xml->createElement('quantity', 1);        // создание тега quantity
            $order->appendChild($quantity);

            $paytype = $xml->createElement('paytype', $paytype);        // создание тега paytype
            $order->appendChild($paytype);

            // $priced = $xml->createElement('priced', 290);        // создание тега priced
            // $order->appendChild($priced);

            // dd($orders['total_sum']);
            $price = $xml->createElement('price', $price);        // создание тега price
            $order->appendChild($price);

            $inshprice = $xml->createElement('inshprice', $inshprice);        // создание тега service
            $order->appendChild($inshprice);

            $items = $xml->createElement('items');        // создание тега receiver
            $order->appendChild($items);

            foreach($its as $it){
                $num = (int)($it['quantity']);
                $retprice = ($it['price']);
                // dd($it);
                $item = $xml->createElement('item',$it['nameProduct']); // создание тега town
                $item->setAttribute("quantity", $num );
                $item->setAttribute("weight", 0 );
                $item->setAttribute("retprice", $retprice);
                $items->appendChild($item);
               
            }
        //   exit;


        echo '<xmp>'.$xml->saveXML().'</xmp>';
        return $xml->saveXML();
    }


    function createOrdersInDally( $url = 'https://api.dalli-service.com/v1/index.php', $body  ) {

        $curl = curl_init();
    // dd($body);
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $body,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/xml'
            // 'Cookie: PHPSESSID=9oa2l08jlgd3rdr4hl9q1m3gl3'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        // dd($response);
        
        return $response;
        
    
    }

/**
 * получение заказов из корзины dalli
 */
function createXMLGetBasket($number_order) {
    $xml = new DomDocument('1.0','utf-8');
    $xml->formatOutput = true;


    $getbasket = $xml->createElement('getbasket');   // создание тега sendbasketcreate
    $xml->appendChild($getbasket);

        $auth = $xml->createElement('auth');               // создание тега auth
            $auth->setAttribute("token", "608333adc72f545078ede3aad71bfe74");
            $getbasket->appendChild($auth);
        
            $number = $xml->createElement('number', $number_order);               // создание тега orderno
            $getbasket->appendChild($number);

    echo '<xmp>'.$xml->saveXML().'</xmp>';
    // dd($xml);
    return $xml->saveXML();
    
}

/**отправка корзины для получения содержимого */
function getBasket($send_body) {
    $curl = curl_init();

    

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.dalli-service.com/v1/index.php',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>$send_body,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/xml',
        'Cookie: PHPSESSID=hhkl3s69l870hejgsf7kh2hah0'
    ),
    ));

    $response = curl_exec($curl);
    // $response = json_decode($response);

    curl_close($curl);
    return $response;
}

/**отправка в доставку по штрихкоду */
function createXMLAddinAct($barcode) {
    $xml = new DomDocument('1.0','utf-8');
    $xml->formatOutput = true;

    $sendbasket = $xml->createElement('sendbasket');   // создание тега sendbasketcreate
    $xml->appendChild($sendbasket);

        $auth = $xml->createElement('auth');               // создание тега auth
            $auth->setAttribute("token", "608333adc72f545078ede3aad71bfe74");
            $sendbasket->appendChild($auth);

        $barcode = $xml->createElement('barcode', $barcode);               // создание тега orderno
        $sendbasket->appendChild($barcode);

    echo '<xmp>'.$xml->saveXML().'</xmp>';
    // dd($xml);
    return $xml->saveXML();
    
}

/**получения файла PDF для некоторых заказов за сегодня */
function createXMLforPDF($barcode) {
    $xml = new DomDocument('1.0','utf-8');
    $xml->formatOutput = true;

    $getact = $xml->createElement('getact');   // создание тега sendbasketcreate
    $xml->appendChild($getact);

        $auth = $xml->createElement('auth');               // создание тега auth
            $auth->setAttribute("token", "608333adc72f545078ede3aad71bfe74");
            $getact->appendChild($auth);

        $barcode = $xml->createElement('barcode', $barcode);               // создание тега orderno
        $getact->appendChild($barcode);

        $returnas = $xml->createElement('returnas', 'base64');               // создание тега orderno
        $getact->appendChild($returnas);


    echo '<xmp>'.$xml->saveXML().'</xmp>';
    // dd($xml);
    return $xml->saveXML();
    
}

/**получение ПВЗ BOXBERRY */ 
function getPVZ() {
    $xml = new DomDocument('1.0','utf-8');
    $xml->formatOutput = true;

    $pvzlist = $xml->createElement('pvzlist');   // создание тега sendbasketcreate
    $xml->appendChild($pvzlist);

        $auth = $xml->createElement('auth');               // создание тега auth
            $auth->setAttribute("token", "608333adc72f545078ede3aad71bfe74");
            $pvzlist->appendChild($auth);

        $partner = $xml->createElement('partner', 'BOXBERRY');               // создание тега orderno
        $pvzlist->appendChild($partner);


    echo '<xmp>'.$xml->saveXML().'</xmp>';
    // dd($xml);
    return $xml->saveXML();
    
}

/**создание элементов справочника  ПВЗ BOXBERRY*/

function createPVZ($address, $code, $worktime) {
    $curl = curl_init();

    $pvz['name']=(string)$address;
    $pvz['code']=(string)$code;
    $pvz['description']=(string)$worktime;
    $body=$pvz;
    // dd($body);
    $send_body=json_encode($body);
    // dd($send_body);exit;
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/33b30d01-bc93-11eb-0a80-0053002f95ba',
    CURLOPT_USERPWD=> "admin@poleznmagaz:ec0055d6bf",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>$send_body,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response);

    curl_close($curl);
    dd($response);
    return $response;

}

/**запрос на статус заказов */
function createXMLStatusOrders() {
    
        $xml = new DomDocument('1.0','utf-8');
        $xml->formatOutput = true;
    
    
        $statusreq = $xml->createElement('statusreq');   // создание тега sendbasketcreate
        $xml->appendChild($statusreq);
    
            $auth = $xml->createElement('auth');               // создание тега auth
                $auth->setAttribute("token", "608333adc72f545078ede3aad71bfe74");
                $statusreq->appendChild($auth);
            
                $changes = $xml->createElement('changes', 'ONLY_LAST');               // создание тега orderno
                $statusreq->appendChild($changes);
    
        echo '<xmp>'.$xml->saveXML().'</xmp>';
        // dd($xml);
        return $xml->saveXML();
        
}

/**После успешной обработки ответа необходимо отметить полученные статусы успешно полученными, отправив запрос: */
function createXMLcommitlaststatus() {
    
    $xml = new DomDocument('1.0','utf-8');
    $xml->formatOutput = true;


    $commitlaststatus = $xml->createElement('commitlaststatus');   // создание тега sendbasketcreate
    $xml->appendChild($commitlaststatus);

        $auth = $xml->createElement('auth');               // создание тега auth
            $auth->setAttribute("token", "608333adc72f545078ede3aad71bfe74");
            $commitlaststatus->appendChild($auth);
        
           
    echo '<xmp>'.$xml->saveXML().'</xmp>';
    // dd($xml);
    return $xml->saveXML();
    
}


/**получение id закака для изменения доп.поля */

function getIdOrder($number) {
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder?filter=name='.$number,
  CURLOPT_USERPWD=> "admin@poleznmagaz:ec0055d6bf",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
$response = json_decode($response);

curl_close($curl);
return $response->rows[0]->id;
}

/**изменение значения в доп.поле статус заказа */

function PutStatus($status, $id) {
    $curl = curl_init();

    $body_status['attributes'][] = [
        "meta" => array(
            "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes/b04ab49c-c1ee-11eb-0a80-012a00187545",
            "type"      => "attributemetadata",
            "mediaType" => "application/json"
        ),
        "value"=>$status
    
    ];
    
    // dd($body_status);exit;
    $postData = json_encode($body_status,256);
    // dd($postData);exit;

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$id,
    CURLOPT_USERPWD=> "admin@poleznmagaz:ec0055d6bf",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => array(
        
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response);
    // dd($response);exit;
    curl_close($curl);
    return $response;
}