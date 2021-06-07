<?php

  $email = 'sales@horoshop.ua';
  $pass = '59399';
  $infotype = 6;


  $data = array("email" => $email, "pass" => $pass, "infotype" => $infotype,'Lang'=>'ua');
  $data_string = json_encode($data);
  $ch = curl_init('https://connect.erc.ua/connectservice/api/specprice/DoExport');
  // $ch = curl_init('https://ciframe.ru/bu.php');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   'Content-Type: application/json',
   'Content-Length: ' . strlen($data_string))
  );
  curl_setopt($ch, CURLOPT_TIMEOUT, 100);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);

  $result = curl_exec($ch);

  curl_close($ch);

  $ff=fopen($_SERVER["DOCUMENT_ROOT"]."/result.ua","w");
  $limit=fwrite($ff,$result);
  fclose($ff);
  




  $data = array("email" => $email, "pass" => $pass, "infotype" => $infotype,'Lang'=>'ru');
  $data_string = json_encode($data);
  $ch = curl_init('https://connect.erc.ua/connectservice/api/specprice/DoExport');
  //$ch = curl_init('https://ciframe.ru/bu.php');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   'Content-Type: application/json',
   'Content-Length: ' . strlen($data_string))
  );
  curl_setopt($ch, CURLOPT_TIMEOUT, 100);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);

  $result = curl_exec($ch);
  $info = curl_getinfo($ch); 
  $cerrorno = curl_errno($ch); 
  $cerrorinfo = curl_error($ch); 

  curl_close($ch);

  $ff=fopen("result_ru","w");
  $limit=fwrite($ff,$result);
  fclose($ff);
  

  ?>