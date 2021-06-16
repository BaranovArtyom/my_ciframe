<?php

require_once 'run/lib.php';
require_once 'run/env.php';
require_once 'run/vendor/autoload.php';
require_once 'run/functions.php';

use DbCon\TgDatabase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use MySklad\MoySklad;

//ini_set('display_errors', '1');

// connect logger
$log_dir = 'logs/' . date('Y-m-d');
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}
$stream = new StreamHandler($log_dir . '/run.log', Logger::DEBUG);
$logger = new Logger('update_settings');
$logger->pushHandler($stream);
$logger->info('Initial log update-settings', [
    'get_request' => $_GET,
    'post_request' => $_POST,
    'body' => file_get_contents('php://input')
]);
try {
    $db = new TgDatabase($dbAuth, $logger);
} catch (Exception $e) {
    $logger->error('Init database class', ['trace' => $e->getTrace()]);
}

try {
    $accountId = empty($GLOBALS['accountId']) ? $_POST['accountId'] : $GLOBALS['accountId'];
    if (empty($accountId)) {
        $accountId = filter_input(INPUT_GET, 'accid', FILTER_SANITIZE_STRING);
        if (empty($accountId))
            throw new Exception('Update settings error: accountId is empty');
    }
    $app_info = $db->getAppInfo($accountId);

    if (empty($app_info['id'])) throw new Exception('Losing app_info. Check this');

    $ms = new MoySklad('', $app_info['access_token']);

    switch ($_GET['action']) {
        case 'remove_cmenu':
            $logger->debug('Remove custom menu', ['_GET' => $_GET]);
            $menu_id = filter_input(INPUT_GET, 'my_cmenu', FILTER_SANITIZE_NUMBER_INT);
            $c_menu = $db->getCustomMenu([], $menu_id);
            if (!empty($c_menu)) {
                if ($c_menu['app_id'] !== $app_info['id']) throw new Exception('Permission denied', ['$menu_id' => $menu_id, 'app_id' => $app_info['id']]);
                $logger->debug('remove cmenu', ['cmenu' => $c_menu]);
                if ($c_menu['category_id']) {
                    $db->updateCategory(['display' => 0], ['id' => $c_menu['category_id']]);
                    $db->updateProduct('', ['to_telegram' => 0], ['category_id' => $c_menu['category_id']]);
                }
                if ($c_menu['parent_id']) {
                    $db->updateCustomMenu(['parent_id' => $c_menu['parent_id']], ['id' => $c_menu['id']]);
                } else {
                    $db->updateCustomMenu(['parent_id' => 0], ['parent_id' => $c_menu['id']]);
                }
                $db->deleteCustomMenu($_GET['my_cmenu']);
            }
            break;
        case "main":
            $columns = [
                'tg_bot_name' => trim($_POST['botName']),
                'tg_bot_token' => @$_POST['botToken'] ? trim($_POST['botToken']) : null,
                'menu_custom_name_field' => trim(@$_POST['menu_custom_name_field']),
                'menu_custom_filter_field' => trim(@$_POST['menu_custom_filter_field']),
                //            'category_list' => json_encode($_POST['cat'])
            ];

            if (empty($_POST['botToken'])) {
                $columns['tg_webhook'] = 0;
            }

            if (!empty($_POST['product_currency'])) {
                $columns['currency_id'] = trim($_POST['product_currency']);
            }

            if (!empty($_POST['main_menu_banner']))
                $columns['main_menu_banner'] = trim($_POST['main_menu_banner']);

            if (!empty($_POST['organization'])) {
                $columns['org_id'] = trim($_POST['organization']);
            }
            if (!empty($_POST['store'])) {
                $columns['store_id'] = trim($_POST['store']);
            }

            $db->updateAppSettings($columns, ['app_info_id' => $app_info['id']]);

            if (!empty($_POST['cat'])) {
                $db->setAllCategoriesNotDisplay($app_info['id']);

                foreach ($_POST['cat'] as $item) {
                    $cat_id = $db->insertOrUpdate('categories', ['display' => 1], ['id' => (int)$item, 'app_info' => $app_info['id']]);
                    $db->update('products', ['to_telegram' => true], ['category_id' => $cat_id]);
                    $logger->debug("Products set to telegram", ['category_id' => $cat_id, 'app_info' => $app_info['id']]);
                }

                // todo rework. Дернуть обновление товаров.
                $sss = file_get_contents("https://i.spey.ru/saas/shopbot_prod/sync_products.php?accid=$accountId&sync_cat=true&sync_products=true");

            } else {
                $db->setAllCategoriesNotDisplay($app_info['id']);
                $db->setAllProductsNotDisplay($app_info['id']);
            }

            if (!empty($_POST['menu_custom_name_field'])) {
                // чтобы не делать отдельную таблицу и связку с продуктами в телеграмм боте, создаю как категорию.
                $_POST['menu_custom_name_field'] = filter_input(INPUT_POST, 'menu_custom_name_field', FILTER_SANITIZE_STRING);
                $cat_name = iconv(mb_detect_encoding($_POST['menu_custom_name_field'], mb_detect_order(), true), "UTF-8", $_POST['menu_custom_name_field']);
                $cat_exist = $db->getCategory('', $app_info['id'], null, $cat_name);
                if (empty($cat_exist)) {
                    $cat = new  stdClass();
                    $cat->name = $cat_name;
                    $cat->id = '';
                    $c = $db->createCategory($cat, '', $app_info['id'], 1);
                    $cat_exist['id'] = $c;
                }
//                $db->updateCategory(['display' => 1, 'custom' => 1], ['id' => $cat_exist['id']]);
                //            $db->update('categories', ['display' => 1], ['id' => $cat_exist['id']]);
                if (!empty($_POST['menu_custom_filter_field']) and !empty($cat_exist['id'])) {
                    foreach (explode(',', $_POST['menu_custom_filter_field']) as $item) {
                        $article = trim($item);
                        $pr = $db->getProductFromDb('', $article, '', $app_info['id']);
//                        print_r($pr);
                        if (!empty($pr['id'])) {
                            $db->updateProduct($pr['id'], ['category_id' => $cat_exist['id']]);
                            //                    $pr = $db->update('products', ['category_id' => $cat_exist['id']], ['article' => trim($item)]);
                        } else {
//                            todo create product
                            if (!empty($accountId)) {
                                try {
                                    $ms_products = $ms->getProducts("article=$article"); // "code=$code"
                                    $logger->debug('Get product by article', ['article' => $article, 'response' => $ms_products]);
                                    if (!empty($ms_products->rows)) {
                                        $product = $ms_products->rows[0];
                                        $default_image = empty($settings['default_product_image']) ? __DIR__ . '/data/images/no-image.jpg' : $settings['default_product_image'];
                                        $image = (int)$product->images->meta->size > 0 ? $ms->getImage($product->images->meta->href, $settings['app_info_id']) : $default_image;

                                        $db->createProduct($product, $image, $app_info['id'], $cat_exist['id']);
                                    } else {
                                        $logger->error('Get assortment by article failed', [
                                            'resp' => $ms_products,
                                            '$article' => $article,
                                            'app_info' => $app_info['id']
                                        ]);
                                        $alert = new AlertInfo();
                                        $alert->action = 'warning';
                                        $alert->message = 'Не можем найти товар с артикулом <strong>$article</strong>.<br>';
                                        $new_alert[] = $alert;
//                                        echo "Не можем найти товар с артикулом <strong>$article</strong>.<br>";
//                                    echo "Заранее приносим извинения за доставленные неудобства. <br>";
                                    }
                                } catch (Exception $e) {
                                    $logger->error("Create product failed {$e->getMessage()}", [
                                        'err' => $e->getMessage(),
                                        'trace' => $e->getTrace(),
                                        'ms_token' => $app_info['access_token'],
                                        'acc_id' => $accountId
                                    ]);
                                }
                            } else {
                                $logger->error('$accountId id empty, create custom field failed', ['$accountId' => $accountId, '$article' => $article]);
                            }
                        }
                    }
                }
            }


            $alert1 = new AlertInfo();
            $alert1->action = 'success';
            $alert1->message = 'Настройки обновлены';
            $new_alert[] = $alert1;
            break;
        case 'c_menu':
            if (!$_POST['c_menu_name']) break;
            $_POST['c_menu_name'] = filter_input(INPUT_POST, 'c_menu_name', FILTER_SANITIZE_STRING);
            $menu_name = iconv(mb_detect_encoding($_POST['c_menu_name'], mb_detect_order(), true), "UTF-8", $_POST['c_menu_name']);
            $settings = $db->getAppSettings($accountId);
            $columns = [
                'app_id' => $app_info['id'],
                'name' => $menu_name,
                'message' => trim(filter_input(INPUT_POST, 'c_menu_message', FILTER_SANITIZE_STRING)),
                'parent_id' => empty($_POST['c_menu_field']) ? 0 : trim(filter_input(INPUT_POST, 'c_menu_field', FILTER_SANITIZE_NUMBER_INT)),
                'url' => empty($_POST['c_menu_url']) ? null : trim(filter_input(INPUT_POST, 'c_menu_url', FILTER_SANITIZE_URL)),
                'text' => empty($_POST['c_menu_text']) ? null : trim(filter_input(INPUT_POST, 'c_menu_text', FILTER_SANITIZE_STRING)),
                'category_id' => empty($_POST['c_menu_cat']) ? null : trim(filter_input(INPUT_POST, 'c_menu_cat', FILTER_SANITIZE_NUMBER_INT)),
                'display' => true,
            ];
            $db->createCustomMenu($columns, $settings['quantityMode']);

            $new_alert[] = new AlertInfo('success', "Меню $menu_name добавлено");
            break;
        case "products":
            $columns = [];
            $columns['quantityMode'] = empty($_POST['stock']) ? 'all' : 'positiveOnly';
            if (!empty($_POST['products_per_page'])) $columns['products_per_page'] = filter_input(INPUT_POST, 'products_per_page', FILTER_SANITIZE_NUMBER_INT);
            if (!empty($_POST['product_filter'])) {
                $v = filter_input(INPUT_POST, 'product_filter', FILTER_SANITIZE_STRING);
                if (!$v) break;
                $columns['product_filter'] = $v;
            }
            if (!empty($_POST['product_price_type'])) {
                $product_price_type = iconv(mb_detect_encoding($_POST['product_price_type'], mb_detect_order(), true), "UTF-8", $_POST['product_price_type']);
                $columns['product_price_type'] = $product_price_type;
            }
            if (!empty($_POST['product_image'])) {
                $columns['product_image'] = filter_input(INPUT_POST, 'product_image', FILTER_SANITIZE_URL);
            }

            if (!empty($columns)) {
                $db->updateAppSettings($columns, ['app_info_id' => $app_info['id']]);
                // todo rework. Дернуть обновление товаров.
                $sss = file_get_contents("https://i.spey.ru/saas/shopbot_prod/sync_products.php?accid=$accountId&sync_cat=true&sync_products=true");
            }
            break;
        case "design":
            $columns = [
                'main_menu_banner' => $_POST['main_menu_banner'],
                'cart_banner' => $_POST['cart_banner'],
                'confirm_order_banner' => $_POST['confirm_order_banner'],
            ];
            $db->updateAppSettings($columns, ['app_info_id' => $app_info['id']]);
            break;
    }
} catch (Exception $e) {
    $logger->error($e->getMessage(), ['trace' => $e->getTrace()]);
}
//echo 'Настройки обновлены, перезагрузите приложение'; // Обновите страницу
if (@$GLOBALS['alert'] and @$new_alert)
    $GLOBALS['alert'] = array_merge($GLOBALS['alert'], $new_alert);
else
    $GLOBALS['alert'] = @$new_alert ? $new_alert : null;
