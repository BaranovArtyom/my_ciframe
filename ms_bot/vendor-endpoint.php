<?php

require_once __DIR__ . 'run/lib.php';


$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'];

loginfo("vendor-endpoint", "Received: method=$method, path=$path");

$pp = explode('/', $path);
$n = count($pp);
$appId = $pp[$n - 2];
$accountId = $pp[$n - 1];

$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody);

$appUid = $data->appUid;
$accessToken = $data->access[0]->access_token;

loginfo("vendor-endpoint", "(appId = $appId, accountId = $accountId, appUid = $appUid, access_token = $accessToken)");
loginfo("vendor-endpoint", "Request body:\n$requestBody");

$app = AppInstance::load($appId, $accountId, connectDb());
$replyStatus = true;

switch ($method) {
    case 'PUT':
    case 'POST':
        $get = json_encode($_GET);
        loginfo("vendor-endpoint", "Request method: $method, data = $get");

        if (!$app->getStatusName()) {
            $app->accessToken = $accessToken;
//            $app->status = AppInstance::SETTINGS_REQUIRED;
            $app->status = AppInstance::ACTIVATED;
            $app->persist([
                'accountName' => $data->accountName,
                'appid' => $_GET['appid'],
                'appUid' => $appUid,
                'accid' => $_GET['accid'],
                'access_token' => $accessToken
            ]);
        }
        loginfo("vendor-endpoint", "App activated");
        break;
    case 'GET':
        loginfo("vendor-endpoint", "Request method: $method");
        break;
    case 'DELETE':
        loginfo("vendor-endpoint", "Request method: $method");
        $app->delete([
            'accountName' => $data->accountName,
            'appid' => $_GET['appid'],
            'appUid' => $appUid,
            'accid' => $_GET['accid'],
            'access_token' => $accessToken
        ]);
        $replyStatus = false;
        break;
}

if (!$app->getStatusName()) {
    http_response_code(404);
} else if ($replyStatus) {
    header("Content-Type: application/json");
    echo '{"status": "' . $app->getStatusName() . '"}';
}
