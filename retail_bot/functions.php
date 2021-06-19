<?php

function sendMessage($message) {
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://mg-s1.retailcrm.pro/api/bot/v1/messages',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "type": "text",
    "scope": "public",
    "chat_id": 1,
    "content": "'.$message.'"
}',
  CURLOPT_HTTPHEADER => array(
    // 'x-bot-token: 36736c89d535010886cbc39bcf261e3937e13dfefb2433bf3134a79d82353d2bf266',
    'x-bot-token: 36874fdd540cc155f2232e65f60de7f44e3d9e80c222598dce34e94e4a40ec837ca5', //artur.retail.ru
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
// echo $response;

return $response;
}

