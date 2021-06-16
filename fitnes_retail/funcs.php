<?php

function dd($value) {
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

function changePhone($idOrder,$phone){
    $curl = curl_init();
    $postData = array();
    $postData['phone']= $phone;
    $postData = json_encode($postData, 256);
    // dd($postData);exit;
    // $send_data=http_build_query($postData);
    $send_data = urlencode($postData);
    // dd($send_data);
    $url = 'https://barfits.retailcrm.ru/api/v5/orders/'.$idOrder.'/edit?apiKey=2CLOgjO6HtAdaIjqZx6S7ryE5ERDnygP&by=id';
    
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'order='.$send_data,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/x-www-form-urlencoded'
    ),
  ));
  
  $response = curl_exec($curl);
  
  curl_close($curl);
  return $response;
  }