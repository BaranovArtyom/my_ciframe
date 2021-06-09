<?php 
function dd($value) {
    echo '<pre>';
    print_r($value);
    echo '</pre>';
}

function getCurlData($url, $account, $password)   {
    $ch = curl_init();                                                  // создание нового ресурса cURL
       
    // установка URL и других необходимых параметров
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $account.":".$password);
    curl_setopt($ch, CURLOPT_URL, "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/".$url);
    $res = curl_exec($ch);
    
    // завершение сеанса и освобождение ресурсов
    curl_close($ch);
    $json = json_decode($res);
    
    return $json;
}

function getCurlStateData($url, $account, $password)   {
  $ch = curl_init();                                                  // создание нового ресурса cURL
     
  // установка URL и других необходимых параметров
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERPWD, $account.":".$password);
  curl_setopt($ch, CURLOPT_URL, $url);
  $res = curl_exec($ch);
  
  // завершение сеанса и освобождение ресурсов
  curl_close($ch);
  $json = json_decode($res);
  
  return $json;
}

function createHook($account, $password, $url, $action, $entityType) {
    $curl = curl_init();
     
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/webhook',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => $account.":".$password,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
      "url": "'.$url.'",
      "action": "'.$action.'",
      "entityType": "'.$entityType.'"
    }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'Accept: application/json;charset=utf-8'
    ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return $response;
}

/**
 *  функция для создания шаблона для отгрузки
 */

function getTemplateDemand($account, $password, $url) {
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/demand/new',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $account.":".$password,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_POSTFIELDS =>'{
        "customerOrder": {
            "meta": {
                "href": "'.$url.'",
                "metadataHref": "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata",
                "type": "customerorder",
                "mediaType": "application/json"
               
            }
        }
    }',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Accept: application/json;charset=utf-8'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    // echo $response;
    return $response;
}

function getTemplatePaymentin($account, $password, $url) {
    $curl = curl_init();

    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/paymentin/new',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => $account.":".$password,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_POSTFIELDS => '{
        "operations": [
          {
            "meta": {
              "href": "'.$url.'",
              "metadataHref": "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata",
              "type": "customerorder",
              "mediaType": "application/json"
            }
          }
        ]
      }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Accept: application/json;charset=utf-8'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    // echo $response;
    return $response;
  }



function createDemand($body, $account, $password) {
    $ch = curl_init();
  
    // file_put_contents("create_demant.json", $body,  FILE_APPEND);
 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $account.":".$password);
    curl_setopt($ch, CURLOPT_URL, 'https://online.moysklad.ru/api/remap/1.2/entity/demand');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch,CURLOPT_HEADER,false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json;charset=utf-8'
    ));   
  
    $response = curl_exec($ch);
    curl_close($ch);
  
    return $response;
}
  
function createPaymentin($body, $account, $password) {
    $ch = curl_init();
  
    // file_put_contents("create_payment.json", $body,  FILE_APPEND);
    // dd($send_body);die();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $account.":".$password);
    curl_setopt($ch, CURLOPT_URL, 'https://online.moysklad.ru/api/remap/1.2/entity/paymentin');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch,CURLOPT_HEADER,false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json;charset=utf-8'
    ));   
  
    $response = curl_exec($ch);
    curl_close($ch);
  
    return $response;
}
  
// function getBodyDemand($numberOrder, $organizationOrder, $agentOrder, $storeOrder) {
//     $orderDemand ['name'] = $numberOrder;
//     $orderDemand ['organization'] = $organizationOrder;
//     $orderDemand ['agent'] = $agentOrder;
//     $orderDemand ['store'] = $storeOrder;
  
//     return $bodyDemand = (object)$orderDemand; 
// }
  
// function getBodyPaymentin($numberOrder, $organizationOrder, $agentOrder, $storeOrder) {
//     $orderPaymentin ['name'] = $numberOrder;
//     $orderPaymentin ['organization'] = $organizationOrder;
//     $orderPaymentin ['agent'] = $agentOrder;
//     $orderPaymentin ['store'] = $storeOrder;
  
//     return $bodyPaymentin = (object)$orderPaymentin; 
// }