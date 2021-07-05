<?php 

ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

/**артикулы товаров */
$sku['article'] = ["ker140","lesublack"];

/**получение продуктов по id */
$getGoods = getGoods($sku,$config_horoshop['token']);
// dd($getGoods);

/**перебор товаров */
foreach ($getGoods as $goods) {
    dd($goods);
}