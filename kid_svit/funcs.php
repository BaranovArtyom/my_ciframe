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

// Из кириллицы в латиницу
function transliterateen($input){
    $gost = array(
    "a"=>"а","b"=>"б","v"=>"в","g"=>"г","d"=>"д","e"=>"е","yo"=>"ё",
    "j"=>"ж","z"=>"з","i"=>"и","i"=>"й","k"=>"к",
    "l"=>"л","m"=>"м","n"=>"н","o"=>"о","p"=>"п","r"=>"р","s"=>"с","t"=>"т",
    "y"=>"у","f"=>"ф","h"=>"х","c"=>"ц",
    "ch"=>"ч","sh"=>"ш","sh"=>"щ","i"=>"ы","e"=>"е","u"=>"у","ya"=>"я","A"=>"А","B"=>"Б",
    "V"=>"В","G"=>"Г","D"=>"Д", "E"=>"Е","Yo"=>"Ё","J"=>"Ж","Z"=>"З","I"=>"И","I"=>"Й","K"=>"К","L"=>"Л","M"=>"М",
    "N"=>"Н","O"=>"О","P"=>"П",
    "R"=>"Р","S"=>"С","T"=>"Т","Y"=>"Ю","F"=>"Ф","H"=>"Х","C"=>"Ц","Ch"=>"Ч","Sh"=>"Ш",
    "Sh"=>"Щ","I"=>"Ы","E"=>"Е", "U"=>"У","Ya"=>"Я","'"=>"ь","'"=>"Ь","''"=>"ъ","''"=>"Ъ","j"=>"ї","i"=>"и","g"=>"ґ",
    "ye"=>"є","J"=>"Ї","I"=>"І",
    "G"=>"Ґ","YE"=>"Є"
    );
    return strtr($input, $gost);
    }
    
    // Из латиницы в кириллицу
    function transliterate($input){
    $gost = array(
    "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
    "е"=>"e", "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i",
    "й"=>"i","к"=>"k","л"=>"l", "м"=>"m","н"=>"n",
    "о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t",
    "у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch",
    "ш"=>"sh","щ"=>"sh","ы"=>"i","э"=>"e","ю"=>"u",
    "я"=>"ya",
    "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
    "Е"=>"E","Ё"=>"Yo","Ж"=>"J","З"=>"Z","И"=>"I",
    "Й"=>"I","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
    "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
    "У"=>"Y","Ф"=>"F","Х"=>"H","Ц"=>"C","Ч"=>"Ch",
    "Ш"=>"Sh","Щ"=>"Sh","Ы"=>"I","Э"=>"E","Ю"=>"U",
    "Я"=>"Ya",
    "ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>"",
    "ї"=>"j","і"=>"i","ґ"=>"g","є"=>"ye",
    "Ї"=>"J","І"=>"I","Ґ"=>"G","Є"=>"YE"
    );
    return strtr($input, $gost);
    }