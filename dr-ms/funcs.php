<?php
/**function dd */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

/**получение атрибутов meta */
function getAttributeMeta(){
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/',
    CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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
    dd($response);
}

/**Получение значение атрибутов в справочнике страна */

function getValueCountry($country) {
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/eb5cf406-4ea6-11eb-0a80-04cb000ab326',
  CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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
    foreach ($response->rows as $v) {
        // dd($v->name);
        if ($v->name == $country) {
            $meta_value['value']['meta'] = (array)$v->meta;
            $meta_value['meta'] = array(
                "href"=> "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/c720c882-4ea7-11eb-0a80-02ed000aef59",
                "type"=> "attributemetadata",
                "mediaType"=>"application/json"
            );
        }
        // dd($v);
    }

    return $meta_value;
}

/**получение meta для единицы измерения */
$body_gramm['uom']  =  [
    "meta" => array(
        "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/uom/8e2eb543-99e9-4077-bc31-93b1359de9c4",
        "type"      => "uom",
        "mediaType" => "application/json"
        )

];
$body_shtuk['uom'] = [
    "meta" => array(
        "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/uom/19f1edc0-fc42-4001-94cb-c9ec9c62ec10",
        "type"      => "uom",
        "mediaType" => "application/json"
        )

];

/**получение цены для товара */
function getPrice( $price, $old_price) {
    $body_Price = [ 
        array(
        "value" => $price,
        "currency" => array(
            "meta" => array(
                "href"=>"https://online.moysklad.ru/api/remap/1.2/entity/currency/97d9629f-3de8-11eb-0a80-044c003a9d4b",
                "metadataHref"=> "https://online.moysklad.ru/api/remap/1.2/entity/currency/metadata",
                "type"=> "currency",
                "mediaType"=>"application/json",
                "uuidHref"=> "https://online.moysklad.ru/app/#currency/edit?id=97d9629f-3de8-11eb-0a80-044c003a9d4b"
            )
            ),
            "priceType"=> array(
                "meta"=> array(
                    "href"=> "https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/97da02db-3de8-11eb-0a80-044c003a9d4c",
                    "type"=>"pricetype",
                    "mediaType"=> "application/json"
                ),
                "id"=> "97da02db-3de8-11eb-0a80-044c003a9d4c",
                "name"=> "Цена продажи",
                "externalCode"=>"cbcf493b-55bc-11d9-848a-00112f43529a"
            )
        
        ),
        array(
            "value"=>$old_price,
            "priceType"=> array(
                "meta"=> array(
                    "href"=> "https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/d2815fea-9791-11eb-0a80-054a001d37a7",
                    "type"=> "pricetype",
                    "mediaType"=>"application/json"
                )
                ),
            "name"=> "Старая цена"
        )

    ];
    
    return $body_Price;

}  


/**получение мета для productfolder */
function getProductFolder($nameProductfolder) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/productfolder',
    CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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

/**атрибут для фасовки */
function get_fasovka($kol_fasovki){

    $body_fasovka = [
        "meta" => array(
            "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/79c4a0a7-9796-11eb-0a80-0d1a001dddfa",
            "type"      => "attributemetadata",
            "mediaType" => "application/json"
        ),
        "value"=>$kol_fasovki,
        "name"=> "Фасовка товара"
    
    ];
    return $body_fasovka;

}

/**атрибут для статуса */
function get_status($val_status){

    $body_status = [
        "meta" => array(
            "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/93746394-9792-11eb-0a80-054a001d6191",
            "type"      => "attributemetadata",
            "mediaType" => "application/json"
        ),
        "value"=>$val_status,
        "name"=> "Статус"
    
    ];
    return $body_status;

}

/**получение value наличие */
function getValueNalichie($val_nalichi) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/e6268a40-92eb-11eb-0a80-0474000f2f30',
    CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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

    curl_close($curl);
    $response = json_decode($response);
    // dd($response);

    foreach ($response->rows as $v) {
        // dd($v->name);
        if ($v->code == $val_nalichi) {
            $meta_value['value']['meta'] = (array)$v->meta;
            $meta_value['meta'] = array(
                "href"=> "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/16d7b90e-9794-11eb-0a80-03cb001da03c",
                "type"=> "attributemetadata",
                "mediaType"=>"application/json"
            );
        }
        // dd($v);
    }
    return $meta_value;
}

/**получение категории meta */
function getCategory($val_category) {
    $body_category = [
        "meta" => array(
            "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/f658b86a-9856-11eb-0a80-07bc000a253c",
            "type"      => "attributemetadata",
            "mediaType" => "application/json"
        ),
        "value"=>$val_category,
        "name"=> "Категория"
    
    ];
    return $body_category;
  
}

/**получение скидки мета */
function getSkidka($val_skidka) {
    $body_skidka = [
        "meta" => array(
            "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/7063f926-979a-11eb-0a80-054a001ebf32",
            "type"      => "attributemetadata",
            "mediaType" => "application/json"
        ),
        "value"=>$val_skidka,
        "name"=> "Скидка"
    
    ];
    return $body_skidka;
  
}

/**получение value свойства товара */
function getValuesvoystva($val_svoystva) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/f4c91b5f-4ea6-11eb-0a80-03f6000caa13',
    CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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

    curl_close($curl);
    $response = json_decode($response);
    // dd($response);

    foreach ($response->rows as $v) {
        // dd($v->name);
        if ($v->name == $val_svoystva) {
            $meta_value['value']['meta'] = (array)$v->meta;
            $meta_value['meta'] = array(
                "href"=> "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/c720ca09-4ea7-11eb-0a80-02ed000aef5a",
                "type"=> "attributemetadata",
                "mediaType"=>"application/json"
            );
        }
        // dd($v);
    }
    return $meta_value;
}

/**получение вкуса атрибута */
function getValueVkus($val_vkus) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/33a42ee8-4ea7-11eb-0a80-0778000aa079',
    CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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

    curl_close($curl);
    $response = json_decode($response);
    // dd($response);

    foreach ($response->rows as $v) {
        // dd($v->name);
        if ($v->name == $val_vkus) {
            $meta_value['value']['meta'] = (array)$v->meta;
            $meta_value['meta'] = array(
                "href"=> "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/c720d7a7-4ea7-11eb-0a80-02ed000aef62",
                "type"=> "attributemetadata",
                "mediaType"=>"application/json"
            );
        }
        // dd($v);
    }
    return $meta_value;
}

/**получение виды мета */
function getVid($val_vid) {
    $body_vid = [
        "meta" => array(
            "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/c7c10a0f-97a8-11eb-0a80-054a00215191",
            "type"      => "attributemetadata",
            "mediaType" => "application/json"
        ),
        "value"=>$val_vid,
        "name"=> "Вид"
    
    ];
    return  $body_vid;
  
}

/**получение форма мета */
function getForma($val_forma) {
    $body_forma = [
        "meta" => array(
            "href"      => "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/cd58eba6-97a9-11eb-0a80-01e90020eb9c",
            "type"      => "attributemetadata",
            "mediaType" => "application/json"
        ),
        "value"=>$val_forma,
        "name"=> "Форма"
    
    ];
    return  $body_forma;
  
}

/**получение компонента товара */
function getComponent($id_prod, $ves) {
    $body_component =  [
            "assortment" => array(
                "meta" => array(
                    "href"=> "https://online.moysklad.ru/api/remap/1.2/entity/product/".$id_prod,
                    "type"=> "product"
                )
            ),
            "quantity"=>$ves
        
    ];

    return  $body_component;
}


/**получение провинции атрибута */
function getValueProvincija($val_provincija) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/fee759a4-4ea6-11eb-0a80-01b2000ae89e',
    CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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

    curl_close($curl);
    $response = json_decode($response);
    // dd($response);

    foreach ($response->rows as $v) {
        // dd($v->name);
        if ($v->name == $val_provincija) {
            $meta_value['value']['meta'] = (array)$v->meta;
            $meta_value['meta'] = array(
                "href"=> "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/c720cbb4-4ea7-11eb-0a80-02ed000aef5b",
                "type"=> "attributemetadata",
                "mediaType"=>"application/json"
            );
        }
        // dd($v);
    }
    return $meta_value;
}

// создание товара 
function createProduct($body){
    $curl = curl_init();
    $postData = json_encode($body,256);

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product',
    CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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

/**создание комплектов */
function createKomplekt($body){
    $curl = curl_init();
    $postData = json_encode($body,256);

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/bundle',
    CURLOPT_USERPWD=> "admin@newtea:12272210a7",
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

