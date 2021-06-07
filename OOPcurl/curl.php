<?php 

class Curl
{
    private $auth;
    private $method;
    private $url;

    public function __construct($auth)
    {
        $this->auth = $auth;
        // $this->method = $method;
        // $this->url = $url;
        
    }

    public function getCurl($addUrl, $filter = [])
    {
        $fullUrl = 'https://online.moysklad.ru/api/remap/1.1/entity/'.$addUrl;
        $ch = curl_init();   
        
        if(!empty($filter))                                              // добавление фильтра
        $fullUrl .= "?filter={$filter['name']}={$filter['value']}";          // создание нового ресурса cURL
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // установка URL и других необходимых параметров
        curl_setopt($ch, CURLOPT_USERPWD, $this->auth);
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        $out = curl_exec($ch);                          // загрузка страницы и выдача её браузеру
        curl_close($ch);                                // завершение сеанса и освобождение ресурсов
        
        // $json=json_decode($out, true);
        $json = json_decode($out);
        
        return $json;
    }
}