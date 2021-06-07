<?php
ini_set('display_errors', 1);
ini_set('error_log', 'logger.log');
error_reporting(E_ALL);

/**
 * Приложение МойСклад-ТелеграмБот
 *
 *
 */
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Expires: " . gmdate("D, d M Y H:i:s") . "GMT");

use DbCon\TgDatabase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use MySklad\MoySklad;

require_once 'run/lib.php';


$accountId = @$_GET['accid'];

if (@$GLOBALS['DEBUG']) {
    $contextKey = '6ecc304a5b50f3576c4763492e3d58ffe06af758';
    $accountId = '445842ed-740c-11e6-7a69-971100000991';
} else
    $contextKey = filter_input(INPUT_GET, 'contextKey', FILTER_SANITIZE_STRING);

// var_dump($GLOBALS['DEBUG']);exit;

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
// connect logger
$log_dir = 'run/'.'logs/' . date('Y-m-d');
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}
// print_r($log_dir);exit;

$stream = new StreamHandler($log_dir . '/iframe.log', Logger::DEBUG);
$logger = new Logger('iframe');

$logger->pushHandler($stream);

$logger->info('Initial log', [
//    'php_version' => phpversion(),
   
    'get_request' => $_GET,
    'ip' => $ip,
    'post_request' => $_POST
]);
// echo 1;exit;

loginfo("IFRAME", "Loaded iframe with contextKey: $contextKey");
// echo 1;exit;

try {
    if (empty($contextKey) or $contextKey === false)
        throw new Exception('context is empty');

    $tg_database = new TgDatabase($GLOBALS['dbAuth'], $logger);
// echo 1;exit;

//    $tg_database->createTables();

    if (!empty($accountId))
        $cntx = $tg_database->getContext($contextKey, $accountId);

    if (empty($cntx)) {
        $employee = vendorApi()->context($contextKey);
        $accountId = $employee->accountId;
        $isAdmin = $employee->permissions->admin->view;
        $settings = $tg_database->getAppSettings($accountId);
        $tg_database->createContext($settings['app_info_id'], $contextKey);
//        $ms = new JsonApi($settings['access_token']);
        $ms = new MoySklad('', $settings['access_token']);

    } else {
//        $accountId = $cntx['accid'];
        $isAdmin = true;
        $settings = $tg_database->getAppSettings($accountId);
//        $ms = new JsonApi($settings['access_token']);
        $ms = new MoySklad('', $settings['access_token']);
    }

    $employee = !empty($employee) ? $employee : 'none';
    $logger->info('Get context', ['context' => $contextKey, '$employee' => $employee]);

    // нужно бы апи писать... Контроллер,таски и все такое. Laravel jobs
    // проблема, что это блокирует основной поток...
    if (!empty($_GET['task'])) {
        $GLOBALS['rootPath'] = dirname(__FILE__);
        switch ($_GET['task']) {
            case 'activateBot':
                if (!$settings['tg_webhook'])
                    require_once 'run/telegram/telegramBot.php';
                break;
            case 'updateSettings':
            case 'dropSettings':
                require_once 'update-settings.php';
                break;
//            case 'syncProducts':
//                require_once 'sync_products.php';
//                break;
        }
        $settings = $tg_database->getAppSettings($accountId);
    }
} catch (Exception $e) {
    $logger->error($e->getMessage(), [
        'trace' => $e->getTrace()
    ]);
}

try {
    if (empty($accountId)) throw new Exception('accountId is empty');
    $db = connectDb(); // todo...
    $app = AppInstance::loadApp($accountId, $db);
    $isSettingsRequired = $app->status != AppInstance::ACTIVATED;
    $infoMessage = $app->infoMessage;

    if ($isAdmin) {
        if (empty($settings['app_info_id'])) throw new Exception('app_info_id is empty');
        $categories = $tg_database->getAllCategories($settings['app_info_id']);

        $menu_list = $tg_database->getCustomMenu(['app_id' => $settings['app_info_id']]);
        if ($db->error()[2])
            $logger->error('Get category ends with errors', [
                'err' => $db->error(),
                'log' => $db->log()
            ]);

        // load all products displayed in telegram, need rework with ajax req // lazy load
        $products = $tg_database->getAllProducts($settings['app_info_id'], 0, 0, true, true);

//        $ms = new JsonApi($settings['access_token']);
//        $prices = $ms->prices();
//        $currency = $ms->currency();
//        $base_url = 'https://marketplace.sandbox.moysklad.ru/api/remap/1.2';
        $base_url = 'https://online.moysklad.ru/api/remap/1.2';
        $urls = [
            'prices' => "$base_url/context/companysettings/pricetype",
            'currency' => "$base_url/entity/currency/",
            'organization' => "$base_url/entity/organization/",
            'store' => "$base_url/entity/store"
        ];
        $responses = $ms->sendMultiRequest($urls);
        $prices = empty($responses[0]) ?: json_decode($responses[0]);
        $currency = empty($responses[1]) ?: json_decode($responses[1]);
        $organizations = empty($responses[2]) ?: json_decode($responses[2]);
        $stores = empty($responses[3]) ?: json_decode($responses[3]);
        $logger->debug('Get prices, cur and other', ['multi_response' => $responses]);

        $orders = $tg_database->getOrders($settings['app_info_id']); //users
        $count_orders = count($orders);
        $count_products = count($products);
    }
} catch (Exception $e) {
    $logger->error($e->getMessage(), [
        'trace' => $e->getTrace()
    ]);
}
$tg_webhook_is_set = false;
if (!empty($settings['tg_webhook']) and $settings['tg_webhook'] == '1') {
    $tg_webhook_is_set = true;
} elseif (empty($settings['tg_bot_token'])) { // если поле токен пустое не показывать ссылку на активацию хука
    $tg_webhook_is_set = true;
}

$dom = ''; // почему я не делал апи...
function print_list($array, $parent = 0, $store_name = 'cat', $remove_links = false, $edit_links = false)
{
    global $dom;
    $dom .= "<ul>";
    foreach ($array as $row) {
        if ($row['parent_id'] == $parent) {
            $dom .= "<li>
            <label><input class='uk-checkbox' type='checkbox'";
            if ($row['display'] == '1')
                $dom .= " checked";
//            $dom .= " name='cat[]' value='{$row['name']}'>{$row['name']}</label>";
            $dom .= " name='{$store_name}[]' value='{$row['id']}'> {$row['name']}";

            if (@$row['category_id']) {
                $cat_key = array_search($row['category_id'], array_column($GLOBALS['categories'], 'id'));
                $category_name = @$GLOBALS['categories'][$cat_key]['name'];
                $dom .= "<span class='uk-padding uk-padding-remove-vertical uk-text-small uk-text'>$category_name</span>";
            }
            $dom .= "<span class='uk-margin-large-left'></span>";

            if ($edit_links)
                $dom .= "<a href='#' class='uk-icon-link edit-link' uk-icon='icon: pencil; ratio: 0.8'></a>";

            if ($remove_links)
                $dom .= "<a href='#' class='uk-icon-link remove-link uk-margin-small-left' uk-icon='icon: close; ratio: 0.8'></a>";

            $dom .= "</label>";
            print_list($array, $row['id'], $store_name, $remove_links, $edit_links);
            $dom .= "</li>";
        }
    }
    $dom .= "</ul>";
    return $dom;
}

if (!empty($categories)) {
    $category_list_dom = print_list($categories);
}
$dom = '';
if (!empty($menu_list))
    $menu_list_dom = print_list($menu_list, 0, 'menu', true, true);

require 'iframe.html.php';