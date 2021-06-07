<?php
require_once 'lib.php';
session_cache_limiter('nocache');

// echo 1;exit;

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'];
$__get = json_encode($_GET);
loginfo("MOYSKLAD => APP", "Received: method=$method, path=$path");
loginfo("MOYSKLAD => APP", "Received: get = $__get");


$pp = explode('/', $path);
$n = count($pp);
$appId = $pp[$n - 2];
$accountId = $pp[$n - 1];

$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody);

$appUid = $data->appUid;
$accessToken = $data->access[0]->access_token;

loginfo("MOYSKLAD => APP", "(appId = $appId, accountId = $accountId, appUid = $appUid, access_token = $accessToken)");
loginfo("MOYSKLAD => APP", "Request body:\n$requestBody");

try {
    $database = connectDb();
} catch (Exception $e) {
    file_put_contents('sada', 'asdsadas');
}
$app = AppInstance::loadApp($accountId, $database);
$replyStatus = true;

switch ($method) {
    case 'POST':
    case 'PUT':
        if (!$app->getStatusName()) {

            $app->accessToken = $accessToken;
            $app->status = AppInstance::ACTIVATED;
            loginfo("MOYSKLAD => APP", "Request PUT acctoken:\n$accessToken");
            $app->persist([
                'accountName' => $data->accountName,
                'accid' => $_GET['accid'],
                'access_token' => $accessToken
            ]);
        }

        break;
    case 'GET':
        break;
    case 'DELETE':
        loginfo('App-delete', json_encode($_GET));
        $app->delete([
            'accname' => $data->accountName,
            'accid' => $_GET['accid']
        ]);
        $replyStatus = false;
        break;
}

if (!$app->getStatusName()) {
    file_put_contents('asd.txt', '404 err');
    http_response_code(404);
} else if ($replyStatus) {
    file_put_contents('asd.txt', $app->getStatusName());

    header("Content-Type: application/json");
    echo '{"status": "' . $app->getStatusName() . '"}';
}
