<?php 

ini_set('display_errors', 'on');

/**function dd */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

/**получение товаров для переноса в бд */

function getProduct(){
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://bot.kiddisvit.ua/KiddisvitServices/hs/ImportDataProductsFile/?format=xml',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Basic SWFtQ2xpZW50OkJ2Z2pobkFmcWtqZEAyMDIw'
    ),
    ));

    $response = curl_exec($curl);
    // $response = json_decode($response);

    curl_close($curl);
    return $response;
}

