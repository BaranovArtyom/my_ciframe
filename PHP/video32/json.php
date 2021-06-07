<?php
 echo '<h1> JSON </h1>';

 $arr = [
     'fio' => 'Иванов Степан',
     'age' => '33' ,
     'vk_url' => 'https://vk.com',
     'learn' => ['HTML', 'CSS', 'PHP']
 ];

 echo json_encode($arr, JSON_UNESCAPED_UNICODE,JSON_UNESCAPED_SLASHES);

 //json_decode(json)
 $json = '{"people":"Sodorov","address":"Lenina 236","mob":["092231123","342424"]}';

 $arr2 = json_decode($json, true);

 print_r($arr2);