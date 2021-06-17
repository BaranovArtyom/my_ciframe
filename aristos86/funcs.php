<?php 
ini_set('display_errors', 'on');

/**function dd */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

/**получение курса валют базовая доллар */

function getCurrency() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://openexchangerates.org/api/latest.json?app_id=3eebbd52b9e34d389ebda9cee6b24b33',
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
    return $response->rates;
}

/**получение данных валют из мс */
function getCurrencyMC(){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/currency/',
    CURLOPT_USERPWD=> "W24@lhome:support",
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

/**замена rate в валюте мс */
    function changeCurrency($id, $isoCode, $code, $rate, $name) {

        $curl = curl_init();
        $postData = array();
        $postData['name'] = $name;
        $postData['code']= $code;
        $postData['rate']= $rate;
        $postData['isoCode']= $isoCode;
        
        $postData = json_encode($postData, 256);
        // dd($postData);exit;

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/currency/'.$id,
        CURLOPT_USERPWD=> "W24@lhome:support",
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

        curl_close($curl);
        return $response;
}