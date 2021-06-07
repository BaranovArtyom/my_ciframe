<?php
ini_set('display_errors', 1);
require_once "funcs.php";
require_once "config.php";

//получение id товаров в vm
$get_products_id = mysqli_query($db,"SELECT virtuemart_product_id FROM `rtd_virtuemart_products`");
$items_vm_full = array();
foreach ($get_products_id as $product_id)   {

    $prod['id_product'] = $product_id['virtuemart_product_id'];         //id  товара
    $prod['product_price'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_price FROM `rtd_virtuemart_product_prices` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0];
    $prod['product_currency'] = intval(mysqli_fetch_row(mysqli_query($db,"SELECT product_currency FROM `rtd_virtuemart_product_prices` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0]);
    
    if( $prod['product_currency'] !== 10 ) {
        $prod['currency_exchange_rate'] = mysqli_fetch_row(mysqli_query($db,"SELECT currency_exchange_rate FROM `rtd_virtuemart_currencies` WHERE `virtuemart_currency_id` = '{$prod['product_currency']}'" ))[0];
            if ($prod['currency_exchange_rate'] !==0 and !is_null($prod['product_price'])) {
            $prod['product_price'] = round($prod['product_price']/floatval($prod['currency_exchange_rate']));
            }else{
                $prod['product_price'] = 0;
            }
    }
    $prod['product_override_price'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_override_price FROM `rtd_virtuemart_product_prices` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0];
    if ( $prod['product_override_price']>0 ) {
        $prod['product_price'] = $prod['product_override_price'];
    }
    $prod['product_tax_id'] = intval(mysqli_fetch_row(mysqli_query($db,"SELECT product_tax_id FROM `rtd_virtuemart_product_prices` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0]);
    $prod['calc_value'] = intval(mysqli_fetch_row(mysqli_query($db,"SELECT calc_value FROM `rtd_virtuemart_calcs` WHERE `virtuemart_calc_id` = '{$prod['product_tax_id']}' "))[0]);
    $prod['calc_value_mathop'] = trim(mysqli_fetch_row(mysqli_query($db,"SELECT calc_value_mathop FROM `rtd_virtuemart_calcs` WHERE `virtuemart_calc_id` = '{$prod['product_tax_id']}' "))[0]);
    $prod['calc_value_mathop'] = str_replace('%','',$prod['calc_value_mathop']);
    if (isset($prod['calc_value'])) {
        $calc = round($prod['product_price']/100*$prod['calc_value']);
        if($prod['calc_value_mathop'] == '+') {
            $prod['product_price'] = $prod['product_price'] + $calc;
        }else {
            $prod['product_price'] = $prod['product_price'] - $calc;
        }
    }
    $prod['product_sku'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_sku FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0];
    $prod['product_weight'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_weight FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0];
    $prod['product_length'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_length FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0];
    $prod['product_width'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_width FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0];
    $prod['product_height'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_height FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0];
    $prod['product_name'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_name FROM `rtd_virtuemart_products_ru_ru` WHERE `virtuemart_product_id`  = '{$product_id['virtuemart_product_id']}' "))[0];
    $prod['product_s_desc'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_s_desc FROM `rtd_virtuemart_products_ru_ru` WHERE `virtuemart_product_id`  = '{$product_id['virtuemart_product_id']}' "))[0];
    $prod['product_weight_type'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_weight_uom FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$product_id['virtuemart_product_id']}' "))[0];
    if ($prod['product_weight_type'] == 'KG' ){
        $prod['product_weight'] = $prod['product_weight']*1000;
    }
    $items_vm_full[] = $prod;   // полный массив товаров из бд vm
    // exit;
}
// dd($items_vm_full);die();
$priceType = priceType();    // для создания товара
// $currency = currency();     // для создания товара

/**
 * проверка товаров из vm c ассортиментом мс в бд
 */

$i=0;
$productDataPrice=$productDataAtribute = $db_prod=array();
$volume='';
foreach ($items_vm_full as $db_prod) {
    $db_code = '';
    $productDataPrice=$productDataAtribute = array();
    $code = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_ms_assorts` WHERE `code_ms` = '{$db_prod['id_product']}' "));
    // dd($type_currency);die();

    $db_code = $code[2];    // получаем id товара из vm для создания или обновления
    if (empty($db_code)) {
        $i++;
        echo $i.'  создать товар c id -  '.$db_prod['id_product']."   ".$db_prod['product_name']."<br>";
        // формирование цены
        
            $productDataPrice[] = [
                'value' => intval($db_prod['product_price'])*100,
                'currency'=> array(
                    "meta" => array(
                    "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/currency/3b37e4e7-b1cd-11e9-912f-f3d4001e97e0",
                    "type"      => "currency",
                    "mediaType" => "application/json"
                    )
                ),
                'priceType'=> array(
                    "meta" => array(
                    "href"      => "https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/3b3a19a2-b1cd-11e9-912f-f3d4001e97e1",
                    "type"      => "pricetype",
                    "mediaType" => "application/json"
                    )
                )
            ];

        
        // формирование атрибутов ( ширина, длина, высота)
        $productDataAtribute[] = [
            "meta" => array(
                "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/37668b52-711e-11eb-0a80-0245000c1b1b",
                "type"      => "attributemetadata",
                "mediaType" => "application/json"
                ),
            "id" => "37668b52-711e-11eb-0a80-0245000c1b1b",
            "value" => intval($db_prod['product_width']),
        ] ;  
        $productDataAtribute[] = [
            "meta" => array(
                "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/920f4e24-711e-11eb-0a80-0613000bafd7",
                "type"      => "attributemetadata",
                "mediaType" => "application/json"
                ),
            "id" => "920f4e24-711e-11eb-0a80-0613000bafd7",
            "value" => intval($db_prod['product_length']),
        ] ;   
        $productDataAtribute[] = [
            "meta" => array(
                "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/0caac865-711f-11eb-0a80-0613000bcbea",
                "type"      => "attributemetadata",
                "mediaType" => "application/json"
                ),
            "id" => "0caac865-711f-11eb-0a80-0613000bcbea",
            "value" => intval($db_prod['product_height']),
        ]; 
                
        $volume = intval($db_prod['product_width']*$db_prod['product_length']*$db_prod['product_height']);
        $createProduct = createProduct($db_prod['product_name'], $db_prod['id_product'], $db_prod['product_s_desc'], $productDataPrice, $db_prod['product_sku'], $productDataAtribute, $volume, $db_prod['product_weight']);
        dd($createProduct);
        // echo "succec";
        
    }else{
       
        // echo 'обновить товар c id -'.$db_prod['id_product']."  id_ms -".$code[1]."<br>";
        // формирование цены
        // $pr['id'] = mysqli_fetch_row(mysqli_query($db,"SELECT id FROM `rtd_virtuemart_products` WHERE `modified_on` = '{$product_id['virtuemart_product_id']}' "))[0];

       
    }

  
}
  //получение id для обновления товаров в vm
  $today = date("H:i:s",strtotime(date("H:i:s")." - 400 minutes"));
  $s = date("Y-m-d");
  $data = $s." ".$today;
  $update_get_products_id = mysqli_query($db,"SELECT virtuemart_product_id FROM `rtd_virtuemart_products` WHERE `modified_on` > '$data' ");
//   dd($update_get_products_id);die();
  $items_vm_full = array();
  foreach ($update_get_products_id as $up_product_id)   {

    $productDataPrice = $productDataAtribute = array();
    $code =  '';
      $code = mysqli_fetch_row(mysqli_query($db,"SELECT id_ms FROM `rtd_ms_assorts` WHERE `code_ms` = '{$up_product_id['virtuemart_product_id']}' "))[0];
    //   dd($up_product_id['virtuemart_product_id']);die();
    //  dd($code);die();
      $up_prod['id_product'] = $up_product_id['virtuemart_product_id'];         //id  товара
      $up_prod['product_price'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_price FROM `rtd_virtuemart_product_prices` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0];
      
      $up_prod['product_currency'] = intval(mysqli_fetch_row(mysqli_query($db,"SELECT product_currency FROM `rtd_virtuemart_product_prices` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0]);
    
    if( $up_prod['product_currency'] !== 10 ) {
        $up_prod['currency_exchange_rate'] = mysqli_fetch_row(mysqli_query($db,"SELECT currency_exchange_rate FROM `rtd_virtuemart_currencies` WHERE `virtuemart_currency_id` = '{$up_prod['product_currency']}'" ))[0];
            if ($up_prod['currency_exchange_rate'] !==0 and !is_null($up_prod['product_price'])) {
            $up_prod['product_price'] = round($up_prod['product_price']/floatval($up_prod['currency_exchange_rate']));
            }else{
                $up_prod['product_price'] = 0;
            }
    }
    $up_prod['product_override_price'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_override_price FROM `rtd_virtuemart_product_prices` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0];
    if ( $up_prod['product_override_price']>0 ) {
        $up_prod['product_price'] = $up_prod['product_override_price'];
    }

    $up_prod['product_tax_id'] = intval(mysqli_fetch_row(mysqli_query($db,"SELECT product_tax_id FROM `rtd_virtuemart_product_prices` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0]);
    $up_prod['calc_value'] = intval(mysqli_fetch_row(mysqli_query($db,"SELECT calc_value FROM `rtd_virtuemart_calcs` WHERE `virtuemart_calc_id` = '{$up_prod['product_tax_id']}' "))[0]);
    $up_prod['calc_value_mathop'] = trim(mysqli_fetch_row(mysqli_query($db,"SELECT calc_value_mathop FROM `rtd_virtuemart_calcs` WHERE `virtuemart_calc_id` = '{$up_prod['product_tax_id']}' "))[0]);
    $up_prod['calc_value_mathop'] = str_replace('%','',$up_prod['calc_value_mathop']);
    if (isset($up_prod['calc_value'])) {
        $calc = round($up_prod['product_price']/100*$up_prod['calc_value']);
        if($up_prod['calc_value_mathop'] == '+') {
            $up_prod['product_price'] = $up_prod['product_price'] + $calc;
        }else {
            $up_prod['product_price'] = $up_prod['product_price'] - $calc;
        }
    }
      
      $up_prod['product_sku'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_sku FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0];
      $up_prod['product_weight'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_weight FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0];
      $up_prod['product_length'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_length FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0];
      $up_prod['product_width'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_width FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0];
      $up_prod['product_height'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_height FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0];
      $up_prod['product_name'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_name FROM `rtd_virtuemart_products_ru_ru` WHERE `virtuemart_product_id`  = '{$up_product_id['virtuemart_product_id']}' "))[0];
      $up_prod['product_s_desc'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_s_desc FROM `rtd_virtuemart_products_ru_ru` WHERE `virtuemart_product_id`  = '{$up_product_id['virtuemart_product_id']}' "))[0];
      $up_prod['product_weight_type'] = mysqli_fetch_row(mysqli_query($db,"SELECT product_weight_uom FROM `rtd_virtuemart_products` WHERE `virtuemart_product_id` = '{$up_product_id['virtuemart_product_id']}' "))[0];
      if ($up_prod['product_weight_type'] == 'KG' ){
          $up_prod['product_weight'] = $up_prod['product_weight']*1000;
      }
      $updated_items_vm_full[] = $prod;   // полный массив товаров из бд vm
      
      $productDataPrice[] = [
          'value' => intval($up_prod['product_price'])*100,
          'currency'=> array(
              "meta" => array(
              "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/currency/3b37e4e7-b1cd-11e9-912f-f3d4001e97e0",
              "type"      => "currency",
              "mediaType" => "application/json"
              )
          ),
          'priceType'=> array(
              "meta" => array(
              "href"      => "https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/3b3a19a2-b1cd-11e9-912f-f3d4001e97e1",
              "type"      => "pricetype",
              "mediaType" => "application/json"
              )
          )
      ];
      // формирование атрибутов ( ширина, длина, высота)
      $productDataAtribute[] = [
          "meta" => array(
              "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/37668b52-711e-11eb-0a80-0245000c1b1b",
              "type"      => "attributemetadata",
              "mediaType" => "application/json"
              ),
          "id" => "37668b52-711e-11eb-0a80-0245000c1b1b",
          "value" => intval($up_prod['product_width']),
      ] ;  
      $productDataAtribute[] = [
          "meta" => array(
              "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/920f4e24-711e-11eb-0a80-0613000bafd7",
              "type"      => "attributemetadata",
              "mediaType" => "application/json"
              ),
          "id" => "920f4e24-711e-11eb-0a80-0613000bafd7",
          "value" => intval($up_prod['product_length']),
      ] ;   
      $productDataAtribute[] = [
          "meta" => array(
              "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/0caac865-711f-11eb-0a80-0613000bcbea",
              "type"      => "attributemetadata",
              "mediaType" => "application/json"
              ),
          "id" => "0caac865-711f-11eb-0a80-0613000bcbea",
          "value" => intval($up_prod['product_height']),
      ]; 
      $volume = intval($up_prod['product_width']*$up_prod['product_length']*$up_prod['product_height']);

      $updateProduct = updateProduct($up_prod['product_name'], $up_prod['id_product'], $up_prod['product_s_desc'], $productDataPrice, $up_prod['product_sku'], $productDataAtribute, $volume, $up_prod['product_weight'],$code);
      dd($updateProduct);
      
    //   echo "succec"."<br>";
  }




