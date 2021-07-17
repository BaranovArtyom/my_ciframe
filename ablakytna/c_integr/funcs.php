<?php 

ini_set('display_errors', 'on');

/**function dd */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

/**атрибут для get_image*/
function get_image($filename_img, $content ){

    $body_image = [
        "filename" => $filename_img,
        "content"=> $content
          
    ];

    return $body_image;

}

/**получение мета для productfolder */
function getProductFolder($nameProductfolder) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/productfolder',
    CURLOPT_USERPWD=> "admin@ablakytna:65146b0861",
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
    // dd($response->rows);
    foreach ($response->rows as $val){
        // dd($val);
        if ($val->name == $nameProductfolder){
            $productFolder['meta'] = $val->meta;
            $productFolder['meta'] = (array)$productFolder['meta'];
        }
    }

    return $productFolder;
}

/**получение цены для товара */
function getPrice($price) {
    $body_Price = [ 
        array(
        "value" => $price,
        "currency" => array(
            "meta" => array(
                "href"=>"https://online.moysklad.ru/api/remap/1.2/entity/currency/c4c73250-de59-11eb-0a80-043d000d5137",
                "metadataHref"=> "https://online.moysklad.ru/api/remap/1.2/entity/currency/metadata",
                "type"=> "currency",
                "mediaType"=>"application/json",
                "uuidHref"=> "https://online.moysklad.ru/app/#currency/edit?id=c4c73250-de59-11eb-0a80-043d000d5137"
            )
            ),
            "priceType"=> array(
                "meta"=> array(
                    "href"=> "https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/c4c7e067-de59-11eb-0a80-043d000d5138",
                    "type"=>"pricetype",
                    "mediaType"=> "application/json"
                ),
                "id"=> "c4c7e067-de59-11eb-0a80-043d000d5138",
                "name"=> "Цена продажи",
                "externalCode"=>"cbcf493b-55bc-11d9-848a-00112f43529a"
            )
        
        )
        

    ];
    
    return $body_Price;

}

// создание товара 
function createProduct($body){
    $curl = curl_init();
    $postData = json_encode($body,256);

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/',
    CURLOPT_USERPWD=> "admin@ablakytna:65146b0861",
    CURLOPT_RETURNTRANSFER => true,
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

/**получение id по sku товара */

function getIdGoods($sku) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product?filter=article='.$sku,
    CURLOPT_USERPWD=> "admin@ablakytna:65146b0861",
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

/**Добавление модификации */

function createMod ($body) {

    $curl = curl_init();
    $postData = json_encode($body,256);
    
    
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/variant',
      CURLOPT_USERPWD=> "admin@ablakytna:65146b0861",
      CURLOPT_RETURNTRANSFER => true,
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

    /**обновление товаров */
function PutGoods($body,$id) {

    $curl = curl_init();
    $postData = json_encode($body,256);

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/'.$id,
    CURLOPT_USERPWD=> "admin@ablakytna:65146b0861",
    CURLOPT_RETURNTRANSFER => true,
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

    curl_close($curl);
    return $response;
}

/**
 * получение агента
 */
function getAgent($phone) {
    $curl = curl_init();
  
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty?search='.urlencode($phone),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD=> "admin@ablakytna:65146b0861", 
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
function createAgent($nameAgent, $phone, $address, $email) {
    $curl = curl_init();
  
    $postData = array();
    $postData['name']=$nameAgent;
    $postData['phone']= $phone;
    // $postData['email']= $email;
    $postData['actualAddress']=$address;
    $postData['email']=$email;
    $postData = json_encode($postData, 256); // 256 - для кодировки русского языка
    // dd($postData);
    // exit;
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD=> "admin@ablakytna:65146b0861", 
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