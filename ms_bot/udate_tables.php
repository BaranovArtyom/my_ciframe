<?php
ini_set('display_errors', 1);
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require 'run/lib.php';

// connect logger
$log_dir = 'logs/' . date('Y-m-d');
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}
$stream = new StreamHandler($log_dir . '/iframe.log', Logger::DEBUG);
$logger = new Logger('udate_tables');
$logger->pushHandler($stream);

$db = connectDb();
$users = $db->select('app_info', '*', ['status' => 'Activated']);
$logger->debug('All activate users', ['usr' => $users]);

foreach ($users as $user) {
    $set = $db->get('app_settings', '*', ['app_info_id' => $user['id']]);
    if (!empty($set) and !empty($set['tg_bot_token'])) {
        $url = 'https://i.spey.ru/saas/shopbot_prod/sync_products.php?sync_cat=true&sync_products=true&accid=';
        $resp = file_get_contents($url . $user['accid']);
        sleep(5);
        $logger->debug('Response sync', ['accid' => $user['accid'], 'resp' => $resp]);
    }
}

echo 'Done';