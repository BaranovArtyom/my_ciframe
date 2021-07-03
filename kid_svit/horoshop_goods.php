<?php 

ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

/**артикулы товаров */
$sku['article'] = ["leather12baltic","ll38pinegreen"];

/**получение продуктов по id */
$getGoods = getGoods($sku,$config_horoshop['token']);
dd($getGoods);