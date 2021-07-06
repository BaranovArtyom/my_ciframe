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

function getProduct($KIDD_USER, $KIDD_PASSWORD){
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://bot.kiddisvit.ua/KiddisvitServices/hs/ImportDataProductsFile/?format=xml',
    CURLOPT_USERPWD=> $KIDD_USER.':'.$KIDD_PASSWORD,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    // CURLOPT_HTTPHEADER => array(
    //     'Authorization: Basic SWFtQ2xpZW50OkJ2Z2pobkFmcWtqZEAyMDIw'
    // ),
    ));

    $response = curl_exec($curl);
    // $response = json_decode($response);

    curl_close($curl);
    return $response;
}
/**получение токена */
function getToken($login,$password) {
    $curl = curl_init();

    $postData = array();
    $postData['login'] = $login;
    $postData['password']= $password;

    // dd($postData);exit;
    $postData = json_encode($postData, 256);


    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://monkeyshop.com.ua/api/auth/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
    ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response);

        if ($response->status == "OK") {
            $res = $response->response->token;
        }else {
            $res = $response->response->message;
            file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в доступах хорошоп - '.$response->response->message."\n",FILE_APPEND);
        }

    curl_close($curl);
    return $res;
    }

/**получение товаров по артикулу */

function getGoods(array $sku,$token) {

    $curl = curl_init();
    $post = array();
    $postData['expr'] = $sku;
    $postData['token'] = $token;

    $post = $postData;
    // dd($post);exit;
    $post = json_encode($post, 256);
    // dd($post);exit;


    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://monkeyshop.com.ua/api/catalog/export/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $post,
    CURLOPT_HTTPHEADER => array(
        'Authorization: Basic Y2lmcmFtZTpzeGZ1b2FjZnc=',
        'Content-Type: application/json',
        'Cookie: PHPSESSID=ugqfivn3udeetikjusikk4e3ap; uuid=db7a058e2da4b8c7293b651ccd2805aa'
      ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response);

    if ($response->status == "OK") {
        $res = $response->response->products;
    }else {
        $res = $response->response->message;
        file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка - '.$response->response->message."\n",FILE_APPEND);
    }

    curl_close($curl);
    return $res;
}

/**Обновление товаров по артикулу */

function UpdateGood($body, $token) {
        $curl = curl_init();

        $post = array();
        $post = $body;
        $post['token'] = $token;
        $post = $post;
        // dd($post);exit;
        $post = json_encode($post, 256);
        // dd($post);exit;

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://monkeyshop.com.ua/api/catalog/import/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Cookie: PHPSESSID=ugqfivn3udeetikjusikk4e3ap; 
            uuid=db7a058e2da4b8c7293b651ccd2805aa'
        ),
        ));

    $response = curl_exec($curl);
    $response = json_decode($response);
    // dd($response);exit;
    if ($response->status == "OK") {
        $res = $response->response->log;
        file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  лог - '.json_encode($res, 256)."\n",FILE_APPEND);
    }else {
        $res = $response->response->message;
        file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка - '.$response->response->message."\n",FILE_APPEND);
    }
   

    curl_close($curl);
    return $res;

}

/**получение всех отгрузок */

/**получение товаров по артикулу */

function getAllGoods($offset ,$token,$limit) {

    $curl = curl_init();
    $post = array();
    $post['offset'] = $offset;
    $post['token'] = $token;
    $post['limit'] = $limit;
    $post['includedParams'] =  ["price", "price_old", "title"];
    // $post = $postData;
    // dd($post);exit;
    $post = json_encode($post, 256);
    // dd($post);exit;


    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://monkeyshop.com.ua/api/catalog/export/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $post,
    CURLOPT_HTTPHEADER => array(
        'Authorization: Basic Y2lmcmFtZTpzeGZ1b2FjZnc=',
        'Content-Type: application/json',
        'Cookie: PHPSESSID=ugqfivn3udeetikjusikk4e3ap; uuid=db7a058e2da4b8c7293b651ccd2805aa'
      ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response);

    if ($response->status == "OK") {
        $res = $response->response->products;
    }else {
        $res = $response->response->message;
        file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка - '.$response->response->message."\n",FILE_APPEND);
    }

    curl_close($curl);
    return $res;
}