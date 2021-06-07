<?php
// ini_set('display_errors', 'on');

/**function dd */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

/**получение отгрузок по дате */
function getSizeDemand($date_from, $data_to){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?filter=moment%3E='.$date_from.';moment%3C='.$data_to,
    CURLOPT_USERPWD=> "admin@belwer312:c1d4d7c3a8",
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

/**получение всех отгрузок по дате */
function getDemand($date_from, $data_to, $offset){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?filter=moment%3E='.$date_from.';moment%3C='.$data_to.'&limit=1000&offset='.$offset.'&order=moment,asc',
    CURLOPT_USERPWD=> "admin@belwer312:c1d4d7c3a8",
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
}


/**Получение продавцов */
function GetSeller() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/employee/',
    CURLOPT_USERPWD=> "admin@belwer312:c1d4d7c3a8",
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
    // $response->rows;
    $sellers = array();
    foreach ($response->rows as $seller) {
        $sellers[] = $seller->name;
    }

    return $sellers;
}

/**получение продавца по id */ 
function getSellerByid($href) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $href,
    CURLOPT_USERPWD=> "admin@belwer312:c1d4d7c3a8",
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
    return $response;
}

/**получение позиций */
function getPositions($href) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $href,
    CURLOPT_USERPWD=> "admin@belwer312:c1d4d7c3a8",
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
}

/**получение продукта для проверки и получение скидки на товар */
function getProduct($href) {
        $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $href,
    CURLOPT_USERPWD=> "admin@belwer312:c1d4d7c3a8",
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
    // $sum = '';
    if (isset($response->attributes)){
        foreach ($response->attributes as $attribute) {
            if ($attribute->name == 'Продавец %') {
                $procent_skidka = (int)$attribute->value;
                $sum = $response->salePrices[0]->value/10000*$procent_skidka;
            }
        }
        
    }
   
    // dd($response->salePrices[0]->value/10000*$procent_skidka);exit;

    return $sum;
}

/**получение для варианта продукта */
function getProductVariant($href) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $href,
    CURLOPT_USERPWD=> "admin@belwer312:c1d4d7c3a8",
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
    return $response->product->meta->href;
}