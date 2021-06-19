<?php 

function dd($val){
    echo '<pre>';
    print_r($val);
    echo '</pre>';
}

function curlGet($url) {
    $ch = curl_init();
    // $url = 'http://sdvor.pro/';                 //1.Создаем страницу
    $ch = curl_init();                          //запускаем curl
    curl_setopt($ch, CURLOPT_URL,$url);         //ссылка покоторой нужно перейти
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1); //записать ответ в переменную
    $content = curl_exec($ch);                  //записываем результат  в переменную 
    curl_close($ch);  
    
    return $content;
}
