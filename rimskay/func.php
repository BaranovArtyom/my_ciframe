<?php 

/**для вывода и сохранения ошибок */
ini_set('display_errors', 1);
ini_set('error_log', 'logger.log');
error_reporting(E_ALL);

/**function dd */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

/**получение id продукта */
function getProductId(){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product?limit=1000',
    CURLOPT_USERPWD=> "ciframe@dasharimskaya:ciframe123",
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

    $prod_ms = $prod = array(); 
    foreach ($response->rows as $id) {
        $prod['id'] = $id->id;
        $prod_ms[] = $prod;
    }

    curl_close($curl);
    return $prod_ms;
}

/**получение остатков по id продукта */
function getStockProduct($idProduct) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.1/report/stock/bystore?product.id='.$idProduct,
    CURLOPT_USERPWD=> "ciframe@dasharimskaya:ciframe123",
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
    // dd($response);

    $st['main'] = $response->rows[0]->stockByStore[1]->stock;
    $st['people'] = $response->rows[0]->stockByStore[0]->stock;
    $st['id'] = $idProduct;

    $stor[] = $st;

    // запись в логгер
    file_put_contents('logger.log',date('H:i:s').' основной склад остаток - '.$st['main'].' People склад остаток - '.$st['people'].' id продукта - '.$st['id']."\n",FILE_APPEND);
    

    curl_close($curl);
    return $stor;
}

function getMeta() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes',
    CURLOPT_USERPWD=> "ciframe@dasharimskaya:ciframe123",
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
    $meta['meta'] = $response->rows[9]->meta;

    curl_close($curl);
    // dd($meta);
}

function getStatusAvail($id) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/'.$id,
    CURLOPT_USERPWD=> "ciframe@dasharimskaya:ciframe123",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS =>'{
        "attributes": [
            {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/1547c107-3014-11eb-0a80-053d0000fa3d",
                    "type": "attributemetadata",
                    "mediaType": "application/json"
                },
                
                "value": {
                    "meta": {
                        "href": "https://online.moysklad.ru/api/remap/1.2/entity/customentity/12ec6095-3014-11eb-0a80-020f0000dee6/94d2ea52-3017-11eb-0a80-094f00010bd9",
                        "type": "customentity",
                        "mediaType": "application/json"
                    }
                }
            }
        ]
    
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}

function getStatusAwait($id) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/'.$id,
    CURLOPT_USERPWD=> "ciframe@dasharimskaya:ciframe123",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS =>'{
        "attributes": [
            {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/1547c107-3014-11eb-0a80-053d0000fa3d",
                    "type": "attributemetadata",
                    "mediaType": "application/json"
                },
                
                "value": {
                    "meta": {
                        "href": "https://online.moysklad.ru/api/remap/1.2/entity/customentity/12ec6095-3014-11eb-0a80-020f0000dee6/a5233f0c-3017-11eb-0a80-06e900012b70",
                        "type": "customentity",
                        "mediaType": "application/json"
                    }
                }
            }
        ]
    
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}

function getStatusNotAvail($id) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/'.$id,
    CURLOPT_USERPWD=> "ciframe@dasharimskaya:ciframe123",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS =>'{
        "attributes": [
            {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/1547c107-3014-11eb-0a80-053d0000fa3d",
                    "type": "attributemetadata",
                    "mediaType": "application/json"
                },
                
                "value": {
                    "meta": {
                        "href": "https://online.moysklad.ru/api/remap/1.2/entity/customentity/12ec6095-3014-11eb-0a80-020f0000dee6/9e522b24-3017-11eb-0a80-053d000118ae",
                        "type": "customentity",
                        "mediaType": "application/json"
                    }
                }
            }
        ]
    
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}

function getProductData($id) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/product/'.$id,
    CURLOPT_USERPWD=> "ciframe@dasharimskaya:ciframe123",
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
    // dd($response);
    // Артикул/наименование/доступно
    $product['sku'] = $response->code;
    $product['title'] = $response->name;
    

    $pr = $product;


    return $pr;

}