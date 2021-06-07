<?php
require_once 'config.php';
require_once 'function.php';
ini_set('display_errors', 'on');


// 2020-12-18T12:50:17
// $ya_orders = myCurl("https://courier.yandex.ru/vrs/api/v1/children?apikey=04f07e56-8028-4d3e-b002-317a86367535&parent_task_id=423d6176-5cf51ec7-6d5fc88-98fe8981");
// $ya_orders = myCurl("https://courier.yandex.ru/vrs/api/v1/result/mvrp/423d6176-5cf51ec7-6d5fc88-98fe8981");
// $ya_orders = myPostCurl('https://courier.yandex.ru/vrs/api/v1/add/mvrp?apikey=04f07e56-8028-4d3e-b002-317a86367535');
// $ya_orders = myPostCurlRoutes('https://courier.yandex.ru/api/v1/companies/20725/routes');
// $ya_orders = myPostCurlDepots('https://courier.yandex.ru/api/v1/companies/20725/depots');
// $ya_orders = myPostCurlCouries('https://courier.yandex.ru/api/v1/companies/20725/couriers') ;
// $today = date("Y-m-d H:i:s");
// $fecha = date("Y-m-d H:i:s", strtotime('+1 hours'));   
// dd($today);
// dd($fecha );
// die();
// $ya_orders = myPostCurlOrders('https://courier.yandex.ru/api/v1/companies/20725/orders');
// $ya_orders = myCurlCreateRoute('https://courier.yandex.ru/vrs/api/v1/add/mvrp?apikey=354889fe-2b48-4559-bffa-4706621ed88b');
// $ya_orders = myCurlgetCreatedRoute('https://courier.yandex.ru/vrs/api/v1/result/mvrp/b4b9e5bc-f18a225f-277a5f61-570ea5f9');
// $ya_orders = myCurlOrdersNote('https://courier.yandex.ru/api/v1/companies/20725/order-notifications?from=%222020-12-22%2010:15:00%22&to=%222020-12-22%2010:25:00%22');
// dd($ya_orders);

// ;die();

$s = '{"events":[{"meta":{"type":"customerorder","href":"https://online.moysklad.ru/api/remap/1.2/entity/82f40024-5b2c-11eb-0a80-023d00102b44"},"action":"CREATE","accountId":"48ba127c-5639-11eb-0a80-06e20000aa7c"}]}';

$url_event = json_decode($s)->events['0']->meta->href;      
// dd($url_event);die();    
$url = explode("/", $url_event);                                // получение url заказа где произошли измененния
dd($url['7']);die();

$id_url = $url['7'];
$orderUrl = " https://online.moysklad.ru/api/remap/1.2/entity/customerorder/"."$id_url";
// $dataTemplate = getTemplateDemand( ACCOUNT, PASSWORD, $url_event); //для шаблона отгрузки
// $dataTemplatePaymentin = getTemplatePaymentin( ACCOUNT, PASSWORD, $url_event);
// dd($dataTemplate);

$data = getCurlData($id_url, ACCOUNT, PASSWORD);
// dd($data);die();  
$urlStateData = $data->state->meta->href;                   // url статуса заказа
$numberOrder = $data->name;                                 // номер заказа
$organizationOrder = $data->organization;                   // ссылка на ваше юрлицо
$agentOrder = $data->agent;                                 // ссылка на контрагента (покупателя)
$storeOrder = $data->store;                                 // ссылка на склад 
dd($numberOrder);
// dd($urlStateData);die();

$stateData = getCurlStateData($urlStateData, ACCOUNT, PASSWORD);
$stateName = $stateData->name;                              // номер статуса заказа
// dd($stateName);die();
// $bodyDemand = $dataTemplate;                                // тело шаблона статуса заказа
// $bodyPaymentin = $dataTemplatePaymentin;        

if ($stateName != STATUS_NAME) {
    
    $dataTemplate = getTemplateDemand(ACCOUNT, PASSWORD, $orderUrl); //для шаблона отгрузки
    // dd($dataTemplate);
    $dataTemplate = json_decode($dataTemplate);
    //получение body для отгрузки
    $body['name'] = $numberOrder;
    $body['moment'] = $dataTemplate->moment;
    $body['applicable'] = $dataTemplate->applicable;
    $body['rate'] = $dataTemplate->rate;
    $body['sum'] = $dataTemplate->sum;
    $body['store'] = $dataTemplate->store;
    $body['agent'] = $dataTemplate->agent;
    $body['organization'] = $dataTemplate->organization;
    $body['positions'] = $dataTemplate->positions;
    $body['vatEnabled'] = $dataTemplate->vatEnabled;
    $body['vatIncluded'] = $dataTemplate->vatIncluded;
    $body['payedSum'] = $dataTemplate->payedSum;
    $body['customerOrder'] = $dataTemplate->customerOrder;
    // dd($dataTemplate);
    // dd((object)$body);
    // $ordBodyDemand = object($body);
    $jsonBodyDemand = json_encode($body);
    // dd($jsonbody);
    // die();
    
    file_put_contents('dataTemplate.json', $dataTemplate , FILE_APPEND);
    $createdDemand = createDemand($jsonBodyDemand, ACCOUNT, PASSWORD);
    // dd($createdDemand);die();
    // $createdDemand = createDemand($dataTemplate, ACCOUNT, PASSWORD);
    // $forPay = $createdDemand->customerOrder;
    // $Paymentin = json_decode($createdDemand)->customerOrder->meta->href;

    // file_put_contents('createdDemand.txt', $Paymentin , FILE_APPEND);
    // echo $Paymentin;

    // dd($Paymentin );die();
    // $createdDemand = createDemand( $bodyDemand, ACCOUNT, PASSWORD);   
    $dataTemplatePaymentin = getTemplatePaymentin( ACCOUNT, PASSWORD, $orderUrl);  
    // dd($dataTemplatePaymentin);die();
    $dataTemplatePaymentin = json_decode($dataTemplatePaymentin);
     //получение body для вход.платеж
     $bodyPay['name'] = $numberOrder;
     $bodyPay['moment'] = $dataTemplatePaymentin->moment;
     $bodyPay['applicable'] = $dataTemplatePaymentin->applicable;
     $bodyPay['rate'] = $dataTemplatePaymentin->rate;
     $bodyPay['sum'] = $dataTemplatePaymentin->sum;
     $bodyPay['agent'] = $dataTemplatePaymentin->agent;
     $bodyPay['organization'] = $dataTemplatePaymentin->organization;
     $bodyPay['paymentPurpose'] = $dataTemplatePaymentin->paymentPurpose;
     $bodyPay['vatSum'] = $dataTemplatePaymentin->vatSum;

     $bodyPay['operations'] = $dataTemplatePaymentin->operations;
     $jsonBodyPay = json_encode($bodyPay);
    // dd($dataTemplatePaymentin);
    // dd($bodyPay);
    // dd($jsonBodyPay);
    // die();
    $createdPaymentin = createPaymentin( $jsonBodyPay, ACCOUNT, PASSWORD);  
    dd($createdPaymentin);die();
    // $createdPaymentin = createPaymentin( $bodyPaymentin, ACCOUNT, PASSWORD);                        // создание документа входящий платеж
    
    if (!empty((object)json_decode($createdDemand) or (object)json_decode($createdPaymentin))) {    // проверка на ошибки
        $errors = json_decode($createdDemand)->errors['0']->error.'---'.date("Y-m-d h:i:s")."\n"; 
        file_put_contents('logger.log', $errors, FILE_APPEND);
        echo "ошибка при создании, смотреть logger.log";
    }else {
        echo "создали документы";
    }
}else {
    echo 'не создаем документы';
}