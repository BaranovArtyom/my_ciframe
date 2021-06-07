<?php

class AlertInfo
{
    public $action;
    public $message;

    /**
     * AlertInfo constructor.
     * @param string $action like 'primary', 'success', 'warning', 'danger',
     * @param string $message
     */
    function __construct($action = '', $message = '')
    {
        $this->action = $action;
        $this->message = $message;
    }
}


function syncProducts($products, $category)
{
    global $ms, $db, $logger, $settings;
    $new_alert = [];
    foreach ($products as $product) {

        if ($product->meta->type == 'variant') {
            $parent_id = get_id_from_href($product->product->meta->href);
            $product->pathName = $db->buildPathNameForVariant($parent_id);
        }

        if ($settings['product_filter']) {
            $c = checkProductByFilterFromAttr(@$product->attributes, $parent_id ?? '');
            if (!$c) {
                $db->updateProduct('', ['to_telegram' => 0], ['ms_id' => $product->id]);
                continue;
            }
        }

        if ($settings['quantityMode'] != 'all' and $product->quantity <= 0) {
            $logger->debug("User define quantity as positive only, product quantity is {$product->quantity}");
            continue;
//            throw new Exception("User define quantity as positive only, product quantity is {$product->quantity}");
        }

        $category_path = empty($category['ms_path']) ? $category['name'] : "{$category['ms_path']}/{$category['name']}";
        if ($product->pathName != $category_path) {
//            $logger->error("Wrong pathName. Product pathName: {$product->pathName}, cat path: $category_path");
            continue;
        }

        if (checkSalePrices($product->salePrices, $settings['product_price_type'])) {
            $default_image = empty($settings['default_product_image']) ? 'https://i.spey.ru/saas/shopbot_prod/data/images/no-image.jpg' : $settings['default_product_image'];
            $image = (int)$product->images->meta->size > 0 ? $ms->getImage($product->images->meta->href, $settings['app_info_id']) : $default_image;

            // check and create
            try {
                $pr = $db->createProduct($product, $image, $settings['app_info_id'], $category['id']);
                $logger->debug("Create prod {$product->name}", ['ms_id' => $product->id]);

                if (empty($pr))
                    $product_id = $db->getProductFromDb((string)$product->name, '', '', $settings['app_info_id'])['id'];
                else
                    $product_id = $pr;

//                if ((int)$product->images->meta->size > 0) { // global $product_task;
//                    $product_task::create([
//                        'app_id' => $settings['app_info_id'],
//                        'action' => 'downloadImage',
//                        'data' => json_encode([
//                            'product_id' => $product_id,
//                            'images_url' => $product->images->meta->href
//                        ])
//                    ]);
//                }

//            $cur_list = $ms->getCurrency();
                foreach ($product->salePrices as $price) {
                    if ($settings['product_price_type'] == $price->priceType->name) {
                        if (empty($settings['currency_id'])) {
                            $cur_url = $price->currency->meta->href;
//                        $settings['currency_id'] = $ms->get_id_from_href($price->currency->meta->href);
                        } else {
                            $cur_url = "https://online.moysklad.ru/api/remap/1.2/entity/currency/{$settings['currency_id']}";
                        }
                        if ($cur_url == $price->currency->meta->href) {
                            $_cur = $ms->getCurrency($settings['currency_id']);
//                            print_r($_cur);
                            $price_id = $db->createPrice($price, $product_id, $_cur);
                            $logger->debug("Create price {$product->name}", ['db_price' => $price_id, 'name' => $price->priceType->name, 'value' => $price->value]);
                            break;
                        } else {
                            $db->update('products', ['to_telegram' => 0], ['id' => $product_id]);
                            $logger->error('Create price failed', ['priceType' => $price->priceType->name, '$cur_url' => $cur_url, '$price' => $price]);
//                            echo "Выбранная в настройках приложения валюта не совпадает с валютой $product->name <br>";
                            $new_alert[] = new AlertInfo('warning',
                                "Выбранная в настройках приложения валюта не совпадает с валютой $product->name <br>"
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                $logger->error($e->getMessage(), ['trace' => $e->getTrace()]);
            }
        } else {
            $logger->error('salePrice is 0', ['ms_product_id' => $product->id, 'app_id' => $settings['app_info_id']]);
            $new_alert[] = new AlertInfo('warning',
                "Товар {$product->name}(id = {$product->id}) будет исключен из списка, ибо имеет цену 0. <br>"
            );
//            echo "Товар {$product->name}(id = {$product->id}) будет исключен из списка, ибо имеет цену 0. <br>";
        }
    }
    if ($new_alert) {
        if (!empty($GLOBALS['alert']))
            $GLOBALS['alert'] = array_merge($GLOBALS['alert'], $new_alert);
        else
            $GLOBALS['alert'] = @$new_alert ? $new_alert : null;
    }
}

function checkProductByFilterFromAttr($attributes, $parent_id = '')
{
    global $db, $settings;
    if (empty($attributes)) {
        if (!$parent_id)
            return false;

//        $_parent = $db->get('products', ['id', 'to_telegram'], ['ms_id' => $parent_id]);
        $_parent = $db->getProductFromDb('', '', '', '', $parent_id);
        if ($_parent['to_telegram'])
            return true;
        else
            return false;
    }
    $s = '';
    switch (true) {
        case stripos($settings['product_filter'], '!=') !== false:
            $s = '!=';
            break;
        case stripos($settings['product_filter'], '=') !== false:
            $s = '=';
            break;
        case stripos($settings['product_filter'], '>') !== false:
            $s = '>';
            break;
        case stripos($settings['product_filter'], '<') !== false:
            $s = '<';
            break;
    }
    if (!$s)
        return false;
    list($filter, $value) = explode($s, $settings['product_filter']);
    foreach ($attributes as $attribute) {
        if ($attribute->name == $filter and $attribute->value == $value)
            return true;
    }
    return false;
}

/**
 * @param array $prices список цен продажи
 * @param string $price_type__name название типа цен продажи
 * @param int $value значиение цены больше чем это значение
 * @return bool
 */
function checkSalePrices(array $prices, string $price_type__name, int $value = 0)
{
    $checked_state = false;
    foreach ($prices as $price) {
        if ($price->value > $value and $price->priceType->name == $price_type__name) {
            $checked_state = true;
            break;
        }
    }
    return $checked_state;
}


function generateMetaForSklad($entity, $id)
{
    $type = $entity;
    if ($entity == 'state') $entity = 'customerorder/metadata/states';
    return array(
        'meta' => array(
            'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/' . $entity . '/' . $id,
            'type' => $type,
            'mediaType' => 'application/json',
            'uuidHref' => 'https://online.moysklad.ru/app/#' . $entity . '/edit?id=' . $id
        )
    );
}

function get_id_from_href($href)
{
    $t = explode('/', $href);
    $id = explode('?', $t[count($t) - 1])[0];
    return $id;
}


/**
 * @param array $children like array of MS ids
 */
function generateCategoryChildLinks(array $children = [])
{
    $links = [];
    foreach ($children as $child) {
        if (!$child) continue;

    }
}

function checkPublishChildCategories($app_id, $category_id = '', $parent_name = '', $check_child = true)
{
    global $db;
    $connection = $db->getConnection();
    $par_id = $category_id;
    if (!empty($parent_name)) {
        $par_id = $connection->get('categories', 'id', ['name[~]' => $parent_name, 'app_info' => $app_id]);
//        var_dump($par_id);
//        echo '<br>';
    }
    if ($par_id) {
        $db->updateCategory(['display' => true], ['id' => $par_id]);
        echo $par_id . '<br>';
        $childs = $connection->select('categories', 'id', ['parent_id' => $par_id, 'app_info' => $app_id]);
        if ($childs)
            foreach ($childs as $child) {
                echo $child . '<br>';
                checkPublishChildCategories($app_id, $child);
            }
    }
}

