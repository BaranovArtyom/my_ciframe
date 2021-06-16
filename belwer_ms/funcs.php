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
/**получение отгрузок для бд размер */
function getSizeD(){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/demand/',
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

/**получение продаж */
function getSizeRetailDemand(){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/retaildemand/',
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

/**получение всех продаж по рознице */
function getRDemand($offset){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/retaildemand?limit=1000&offset='.$offset,
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

/**получение всех продаж по рознице */
function getDemand_bd($offset){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/demand?limit=1000&offset='.$offset,
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
function getProduct($href, $discount) {
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
                $sum_zakaza = $response->salePrices[0]->value/10000*$discount;
                $total_sum = $response->salePrices[0]->value/100;
                // dd($sum_zakaza);
                $procent_skidka = (int)$attribute->value;
                // dd($procent_skidka);
                // dd($total_sum);
                $sum = ($total_sum-$sum_zakaza)/100*$procent_skidka;
                // $sum = number_format($sum, 2, ',', '');
                // dd($sum);
                
            }
        }
        
    }
   
    // dd($response->salePrices[0]->value/10000*$procent_skidka);exit;
    // dd($sum);exit;
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

/**получение имя клиента */
function getNameAgent($href) {
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
    return $response->name;
}

/**получение продаж по рознице */

function getRetailDemand($date_from, $data_to) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/retaildemand?filter=moment%3E='.$date_from.';moment%3C='.$data_to.'&order=moment,asc',
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

/**получение продавцов  */
function getSel() {
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
    return $response->rows;

}

/**получение клиентов  */
function getAgent() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty/',
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

/**получение всех агентов */
function getAllagent($offset){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty?limit=1000&offset='.$offset,
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

/**получение всех продуктов */
function getProducts(){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment/',
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

/**получение всех продуктов  для базы*/
function getProductAll($offset){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment?limit=1000&offset='.$offset,
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

/**
 * проверка были изменения в ассортименте мс за последние 30 мин
 */
function checkUpdateassort()  {
    $today = date("H:i:s",strtotime(date("H:i:s")." -10 minutes"));
    $s = date("Y-m-d");
    $s = '2021-06-10';
  
    $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment?filter=updated%3E'.$s.'%20'.$today.'',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD=> "admin@belwer312:c1d4d7c3a8", 
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