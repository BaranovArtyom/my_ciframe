<?php
require_once "config.php";

function dd($value) {
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}


function getSizeAssortment() {
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment?limit=1',
  CURLOPT_USERPWD=> "api@manager245:api1111",
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

return $response->meta->size;

}

function getAllassort($mysql, $offset) {
  $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment?limit=1000&offset='.$offset,
  CURLOPT_USERPWD=> "api@manager245:api1111",
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
$prod = $items_ms = array();
  	foreach($response->rows as $product_ms) { 
      // dd($product_ms);die();
      $prod['code'] = $product_ms->code;  // выбираем код из ассортимента в мс и если нет то добавляем в таблицу в бд
      // $prod['code'] = 9496;
      $id = mysqli_fetch_row(@mysqli_query($mysql,"SELECT `id` FROM `rtd_ms_assorts`  WHERE `code_ms`= '{$prod['code']}' "))[0];
      dd($id);
      // dd($product_ms->id);
      // 457432f9-728b-11eb-0a80-07b000072ebc
        if (empty($id)) {
          mysqli_query($mysql,"INSERT INTO `rtd_ms_assorts` (`id`, `id_ms`, `code_ms`) VALUES (NULL, '{$product_ms->id}', '{$product_ms->code}')");
          $prod['id'] = $product_ms->id;
          $prod['code'] = $product_ms->code;
          $items_ms[] = $prod;
        }
        // else{
        //   $s = mysqli_query($mysql,"UPDATE `rtd_ms_assorts` SET `id_ms`= '{$product_ms->id}' , `code_ms`= '{$product_ms->code}' WHERE `id`= '$id'");
        // dd($s);
        // }
        // exit;
    }
  
return $items_ms;
}
 

function findProduct_ms($id_product_vm) {
	$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product?filter=code='.$id_product_vm,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_USERPWD=> "api@manager245:api1111", 
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
}

/**
 * проверка были изменения в ассортименте мс за последние 30 мин
 */
function checkUpdateassort()  {
  $today = date("H:i:s",strtotime(date("H:i:s")." -20 minutes"));
  $s = date("Y-m-d");

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment?filter=updated%3E'.$s.'%20'.$today.'',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "api@manager245:api1111", 
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
  return $response;
}

/**
 * получение типа цены
 */
function priceType(){
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "api@manager245:api1111", 
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

  return $response[0]->meta;
}

/**
 * получения валюты
 */
function currency(){
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/currency',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "api@manager245:api1111", 
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

  return $response->rows[0]->meta;

}

function createProduct($name, $code, $descr, $salePrice, $sku,$productDataAtribute,$volume,$weight ) {
  $curl = curl_init();

$postData = array();
$postData['name']=$name;
$postData['code']=$code;
$postData['descr']=strip_tags($descr);
$postData['salePrices']= $salePrice;
$postData['attributes']=$productDataAtribute;
$postData['article']=$sku;
$postData['weight']=(float)$weight;
$postData['volume']=(float)$volume;
// dd($postData);

$postData = json_encode($postData,256);
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "api@manager245:api1111", 
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>$postData,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));
  $response = curl_exec($curl);
  // print_r($response);die();
  $response = json_decode($response);
  // dd($response );
  curl_close($curl);
  return $response;
}

function updateProduct($name, $code, $descr, $salePrice, $sku,$productDataAtribute,$volume,$weight,$id ) {
  $curl = curl_init();

$postData = array();
$postData['name']=$name;
// $postData['code']=$code;
$postData['descr']=strip_tags($descr);
$postData['salePrices']= $salePrice;
$postData['attributes']=$productDataAtribute;
$postData['article']=$sku;
$postData['weight']=(float)$weight;
$postData['volume']=(float)$volume;
// dd($postData);

$postData = json_encode($postData, 256);

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/'.$id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "api@manager245:api1111", 
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS =>$postData,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));
  $response = curl_exec($curl);
  // print_r($response);die();
  $response = json_decode($response);
  // dd($response );die();
  curl_close($curl);
  return $response;
}

function getUserVM($mysql,$id) {

  $UserVm = mysqli_query($mysql,"SELECT * FROM `rtd_virtuemart_order_userinfos` WHERE `virtuemart_order_id` = '$id' ");
  foreach ($UserVm as $dataUser)   {
   
  }
  return $dataUser;
}

/**
 * получение позиций заказа
 */

function getPositionOrder($mysql, $virtuemart_order_id) {

  $PositionOrder = mysqli_query($mysql,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = $virtuemart_order_id ");
  $items = array();
  foreach ($PositionOrder as $item)   {
   
   $items[]= $item;
  //  dd($item);
  }
  return $items;
}

function getShipOrder($mysql, $virtuemart_order_id) {

  $getShipOrder = mysqli_query($mysql,"SELECT * FROM `rtd_virtuemart_order_calc_rules` WHERE `virtuemart_order_id` = $virtuemart_order_id ");
  $ships = array();
  foreach ($getShipOrder as $ship)   {
   
   $ships[]= $ship;
  //  dd($item);
  }
  return $ships;
}



/**
 * получение агента
 */
function getAgent($phone) {
  $curl = curl_init();

  curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty?search='.urlencode($phone),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_USERPWD=> "api@manager245:api1111", 
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

return $response;
}

/**
 * создание агента
 */
function createAgent($nameAgent, $phone, $email, $address) {
  $curl = curl_init();

  $postData = array();
  $postData['name']=$nameAgent;
  $postData['phone']= $phone;
  $postData['email']= $email;
  $postData['actualAddress']=$address;
  $postData = json_encode($postData, 256); // 256 - для кодировки русского языка
  // dd($postData);
  // exit;

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "api@manager245:api1111", 
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));
  
  $response = curl_exec($curl);
  $response = json_decode($response);
  curl_close($curl);
  
  return $response;
}

/**
 * создание заказа
 */
function createOrder($body) {
  $curl = curl_init();

  $body = json_encode($body, 256);
  
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "api@manager245:api1111", 
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => array(
      
      'Content-Type: application/json'
    ),
  ));

  $response = curl_exec($curl);
  dd($response);
  curl_close($curl);
  return $response;
}

function getItemMS($virtuemart_product_id) {
  $curl = curl_init();
// dd($virtuemart_product_id);
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product?filter=code='.$virtuemart_product_id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "api@manager245:api1111", 
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
// dd($response);
$response = json_decode($response);
// dd($response );
curl_close($curl);
return $response->rows[0]->meta;
}
