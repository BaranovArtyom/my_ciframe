<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
session_cache_limiter('nocache');

require_once __DIR__ . '/run/vendor/autoload.php';
require_once __DIR__ . '/run/env.php';
require_once __DIR__ . '/run/functions.php';

use DbCon\TgDatabase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use MySklad\MoySklad;

// connect logger
$log_dir = __DIR__ . '/logs/' . date('Y-m-d');
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}
$stream = new StreamHandler($log_dir . '/sync_products.log', Logger::DEBUG);
$logger = new Logger('sync_products');
$logger->pushHandler($stream);
$logger->info('Initial log');

$db = new TgDatabase($GLOBALS['dbAuth'], $logger);


$account_id = empty($_GET['accid']) ? $argv[1] : $_GET['accid'];

if (!empty($_GET['accid'])) {
    $access_token = $db->getMsToken($_GET['accid']);
    $ms = new MoySklad('', $access_token);

    $settings = $db->getAppSettings($_GET['accid']);

//    if ($settings['app_info_id'] == '90') { // 90
//        $cat_list = ['ТАБАК', 'ЧАЙНЫЕ СМЕСИ', 'ПАСТА', 'УГОЛЬ', 'АКСЕССУАРЫ', 'КАЛЬЯНЫ', 'ЭЛЕКТРОННЫЕ СИГАРЕТЫ',
//            'СПЕЦИАЛЬНЫЕ ПРЕДЛОЖЕНИЯ', 'ПОДАРОЧНЫЕ СЕРТИФИКАТЫ', 'MAGIC BOX'];
//        foreach ($cat_list as $item) {
//            echo $item . '<br>';
//            checkPublishChildCategories($settings['app_info_id'], '', $item);
//        }
//    }

    $logger->debug('get req', ['get' => $_GET, 'post' => $_POST]);

## CREATE MS ORDER ##
    if (!empty($_GET['new_order'])) {
        try {
            $logger->debug('start sync orders');
            $connection = $db->getConnection();
            // я хз почему сперва беру пользователей потом заказы...
            $users = $connection->select("users", "*", ["app_info_id" => $settings["app_info_id"]]); // join
            foreach ($users as $user) {
                if (!empty($user['name'] and $user['phone'])) {
                    $orders = [];
                    if (!empty($user['id']))
                        $orders = $connection->select('orders', '*', ['user' => $user['id'], 'completed' => false]);
                    if (!empty($orders))
                        foreach ($orders as $ord) {
                            $positions = [];
                            $products_in_cart = json_decode($ord['cart_info'], true);
                            if (!empty($products_in_cart)) {
                                foreach ($products_in_cart as $product) {
                                    $product__info = $db->getProductFromDb('', '', $product['products']);
                                    if (!empty($product__info['ms_id'])) {
                                        $product_ms_meta = generateMetaForSklad($product__info['type'], $product__info['ms_id']);
                                        $n_price = $db->getPrice($product__info['id']);
                                    } elseif (!empty($product__info['ms_code']) or !empty($product__info['article'])) {
                                        // Need use assortment, cause we use variations //
                                        if (!empty($product__info['ms_code']))
                                            $ms_products = $ms->getProducts('code=' . $product__info['ms_code']);
                                        else
                                            $ms_products = $ms->getProducts('article=' . $product__info['article']);
                                        if (!empty($ms_products->rows)) {
                                            $ms_products = $ms_products->rows[0];
                                            $product_ms_meta = ['meta' => $ms_products->meta];
                                            foreach ($ms_products->salePrices as $salePrice) {
                                                switch ($salePrice->priceType->name) {
                                                    case $settings['product_price_type']:
                                                        $n_price['value'] = $salePrice->value / 100;
                                                        break(2);
                                                }
                                            }
                                        } else {
                                            $logger->error('Problems with get product from ms', ['resp' => $ms_products]);
                                            throw new Exception("Problems with get product from ms: {$product__info['ms_id']}");
                                        }
                                    }
                                    if (!empty($product_ms_meta) and !empty($n_price))
                                        $positions[] = [
                                            'quantity' => (float)$product['quantity'],
                                            'price' => (float)$n_price['value'] * 100,
//                                        'assortment' => ['meta' => $product_ms_meta] // WORK WITH ASSORTMENT//
                                            'assortment' => $product_ms_meta
                                        ];
                                }
                            }

                            $org = empty($settings['org_id']) ? @$ms->getOrganization()->meta : generateMetaForSklad('organization', $settings['org_id']);
//                            if (!empty($org->errors))
//                                $logger->error('Get organization', ['resp' => $org]);
                            $agent = $ms->getAgent(['name' => $user['name'], 'phone' => $user['phone']], true);
                            if (!empty($agent->errors))
                                $logger->error('Get agent', ['resp' => $agent]);
                            else {
                                if (!empty($agent->rows))
                                    $agent = $agent->rows[0];
                                $body = [
                                    'name' => "TG-" . $ord['ms_name'],
                                    'organization' => $org,
                                    'agent' => ['meta' => $agent->meta],
                                    'rate' => ['currency' => generateMetaForSklad('currency', $settings['currency_id'])],
                                    'positions' => $positions
                                ];
                                if (!empty($settings['store_id']))
                                    $body['store'] = generateMetaForSklad('store', $settings['store_id']);
                                $new_order = $ms->createOrder($body);
                                if (@$new_order->errors)
                                    $logger->error('create ms order failed', ['response' => $new_order]);
                                else
                                    $logger->debug("Create new order {$ord['id']}", ['resp' => $new_order, 'db_ord' => $ord, 'body' => $body]);

                                if (!empty($new_order->id)) {
                                    $logger->debug('$new_order->id');
                                    $db->updateOrder($ord['id'], ['completed' => true, 'ms_id' => $new_order->id]);
                                }
                            }
                        }
                }
            }
        } catch (Exception $e) {
            $logger->error('Push new order ends with err:' . $e->getMessage(), ['trace' => $e->getTrace()]);
        }
    }

## SYNC CATEGORIES ##
    if (!empty($_GET['sync_cat']) and $_GET['sync_cat'] == 'true') {

        $categories = $ms->getProductFolders();
        if (!empty($categories->errors)) {
            $logger->error('Sync cat failed', ['resp' => $categories, 'app_info_id' => $settings['app_info_id'], 'ms' => $ms]);
            echo "Что-то пошло не так, {$categories->errors[0]->error}";
        }
        if (!empty($categories->rows)) {

            foreach ($categories->rows as $category) {
                $_cat = $db->getCategory($category->id, $settings['app_info_id']);
//                if (empty($_cat) and $_cat !== false) {
                try {
                    $p_id = 0;
                    if (!empty($category->productFolder)) {
                        $p_id = $db->createParentsCategory(
                            $categories->rows,
                            $ms->get_id_from_href($category->productFolder->meta->href),
                            $settings['app_info_id']
                        );
                    }
                    $cat_id = $db->createCategory($category, $p_id, $settings['app_info_id']);
                    $logger->debug("Create cat {$category->name}", ['ms_id' => $category->id]);
                } catch (Exception $e) {
                    $logger->error($e->getMessage(), ['trace' => $e->getTrace()]);
                }
//                }
            }
        }
    }

## SYNC PRODUCTS ##
    if (!empty($_GET['sync_products']) and $_GET['sync_products'] == 'true') {
        $logger->debug('Sync products started');

//        $__menu = $db->getCustomMenu(['app_id' => 107, 'display' => 1]);
//        foreach ($__menu as $menu){
//        if (@$menu['category_id']) {
//            echo '<br>'.$menu['category_id'];
//            $db->updateCategory(['display' => true], ['id' => $menu['category_id']]);
//            if ($settings['quantityMode'] == 'positiveOnly') {
//                $db->updateProduct('', ['to_telegram' => true], ['category_id' => $menu['category_id'], 'quantity[>]' => 0]);
//            } elseif ($settings['quantityMode'] == 'all') {
//                $db->updateProduct('', ['to_telegram' => true], ['category_id' => $menu['category_id']]);
//            }
//        }
//        }
        // sync products on published categories
        $categories = $db->getAllCategories($settings['app_info_id'], true);

        if (!empty($categories)) {
            foreach ($categories as $_category) {
                if ($_category['custom'] == '0') {
                    $filter = [
                        "quantityMode={$settings['quantityMode']}",
                        "productFolder=https://online.moysklad.ru/api/remap/1.2/entity/productfolder/{$_category['ms_id']}"
                    ];

                    $filter = implode(';', $filter);
                    $logger->debug('$ms_products', ['filter' => $filter]);

                    $ms_products = $ms->getAssortment($filter);
                    if (!empty($ms_products->errors)) {
                        $logger->error('Sync products failed', ['resp' => $ms_products, 'app_info_id' => $settings['app_info_id']]);
                        echo "Что-то пошло не так, {$ms_products->errors[0]->error}";
                    }

                    if (!empty($ms_products->rows)) {
                        try {
                            syncProducts($ms_products->rows, $_category);
                        } catch (Exception $e) {
                            $logger->error($e->getMessage());
                        }
                    } else {
                        $logger->error('Have no products', ['products' => $ms_products]);
//                    echo "Sorry, we can't find any products on this account <br>";
                        echo "Мы не нашли товары в группе {$_category['name']}, если она не пустая, сообщите <a href='mailto:info@ciframe.com'>нам</a> <br>";
                    }
                    if (!empty($ms_products->meta->nextHref)) {
                        $logger->error('Productgroup have more than 1000 products in ms', ['size' => $ms_products->meta->size,
                            'db_cat' => $_category]);
                        echo "В группе больше 1000 товаров. Поддержка такого колличества добавится в ближайших релизах.
                    Спасибо за терпение. <br>";
                    }
                }
            }
        } else {
            $logger->debug("Categories is not set", ["cats" => $categories, "app_id" => $settings['app_info_id']]);
        }
    }

//    echo 'Настройки обновлены, перезагрузите приложение';
    $new_alert[] = new AlertInfo('success', 'Обновлено');
} else {
    $logger->error('accid is empty', ['get' => $_GET['accid'], 'post' => $_POST]);
//    echo 'Что-то пошло е так. ПОвторите попытку';
    $new_alert[] = new AlertInfo('danger', 'Что-то пошло не так. Повторите попытку');
}
if (!empty($GLOBALS['alert']))
    $GLOBALS['alert'] = array_merge($GLOBALS['alert'], $new_alert);
else
    $GLOBALS['alert'] = @$new_alert ? $new_alert : null;

if (!empty($_GET['contextKey']) and !$DEBUG) {
    include_once 'index.php';
}



