<?php

// date_default_timezone_set('Europe/Kiew');

// $data_t = '2021-05-27';
// $data_from = date($data_t." 00:00:00");                 //дата с 
// // $data_to = date($data_t." 23:59:59");                   //дата до
// // $data_from = urlencode($data_from);
// // echo $data_from;
// // echo time($data_from);
// $data_fr = strtotime($data_from);
// echo $data_fr;
// exit;
$time = date("y-m-d h:i:s",1624050000);
echo $time."<br>";
echo strtotime($time);