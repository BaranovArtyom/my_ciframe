<?php

function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

function curlGet($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);         //ссылка покоторой нужно перейти
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //записать ответ в переменную
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $content = curl_exec($ch);                  //записываем результат  в переменную 
    curl_close($ch);  
    $json = json_decode($content);
    return $json;

}

function myCurlPost($url, $body=[])  {
  // создание нового ресурса cURL
  $ch = curl_init();
  $send_body=json_encode($body);
  //var_dump($send_body);
  // установка URL и других необходимых параметров
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // curl_setopt($ch, CURLOPT_USERPWD, "admin@sashasergienko8385:9b08fa6d7b");
  // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $send_body);
  curl_setopt($ch,CURLOPT_HEADER,false);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Accept: application/json'
      
  ));
  // // загрузка страницы и выдача её браузеру
  $out=curl_exec($ch);
  // var_dump($out);
  echo curl_error($ch);
  
  // завершение сеанса и освобождение ресурсов
  curl_close($ch);
  
  // $json=json_decode($out, true);
  $json=json_decode($out);

  // echo "<pre>";
  // print_r($json);
  // echo "</pre>";
  return $json;
  }

  function myCurlPut($url)  {
    // создание нового ресурса cURL
    $ch = curl_init();
    // $send_body=json_encode($body);
        //var_dump($send_body);
    // установка URL и других необходимых параметров
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_USERPWD, "admin@sashasergienko8385:9b08fa6d7b");
    // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{
        "fulfillmentStatus": "PROCESSING"
    }');
    curl_setopt($ch,CURLOPT_HEADER,false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
        
    ));
    // // загрузка страницы и выдача её браузеру
    $out=curl_exec($ch);
    // var_dump($out);
    echo curl_error($ch);
    
    // завершение сеанса и освобождение ресурсов
    curl_close($ch);
    
    // $json=json_decode($out, true);
    $json=json_decode($out);
  
    // echo "<pre>";
    // print_r($json);
    // echo "</pre>";
    return $json;
  }
