<?php  

// class M
// {
//     private $auth;

//     public function __construct($auth)
//     {
//         $this->auth = $auth;
//     }
    
//     public function myCurl($url, $method='GET', $body=[], $filter = [])   
//     {

//         if(!empty($filter))
//         $url .= "filter={$filter['name']}={$filter['value']}";
//         // создание нового ресурса cURL
//         $ch = curl_init();
        
//         // установка URL и других необходимых параметров
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         // curl_setopt($ch, CURLOPT_USERPWD, "admin@sashasergienko8385:9b08fa6d7b");

//         curl_setopt($ch, CURLOPT_USERPWD, $this->auth);
//         curl_setopt($ch, CURLOPT_URL, $url);
        
//         // загрузка страницы и выдача её браузеру
//         $out=curl_exec($ch);
        
//         // завершение сеанса и освобождение ресурсов
//         curl_close($ch);
        
//         // $json=json_decode($out, true);
//         $json=json_decode($out);
        
//         return $json;
//     }



// }

/**
 * функция для get запросов 
 */
function myCurl($url, $method='GET', $body=[], $filter = [])   {

$ch = curl_init();                              // создание нового ресурса cURL
// $send_body=json_encode($body);

// добавление фильтра
if(!empty($filter))
$url .= "?filter={$filter['name']}={$filter['value']}";

// установка URL и других необходимых параметров
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "admin@sashasergienko8385:9b08fa6d7b");
curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $send_body);
// // загрузка страницы и выдача её браузеру
$out=curl_exec($ch);

// завершение сеанса и освобождение ресурсов
curl_close($ch);

//$json=json_decode($out, true);
 $json=json_decode($out);

return $json;
}

/**
 * для проверки переменной
 */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}
/**
 * для проверки post запросов
 */
function myCurlPost($url, $method='POST', $body=[], $filter = [])   {
    // создание нового ресурса cURL
    $ch = curl_init();
    $send_body=json_encode($body);
    
   
    // добавление фильтра
    if(!empty($filter))
    $url .= "?filter={$filter['name']}={$filter['value']}";
    // var_dump($url);
    
    // установка URL и других необходимых параметров
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "admin@sashasergienko8385:9b08fa6d7b");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $send_body);
    curl_setopt($ch,CURLOPT_HEADER,false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    'Content-Type: application/json'
	));
    // // загрузка страницы и выдача её браузеру
    $out=curl_exec($ch);
    // var_dump($out);
    echo curl_error($ch);
    
    // завершение сеанса и освобождение ресурсов
    curl_close($ch);
    
    //$json=json_decode($out, true);
    $json=json_decode($out);

    // echo "<pre>";
    // print_r($json);
    // echo "</pre>";
    return $json;
    }