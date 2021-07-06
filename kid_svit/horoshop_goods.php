<?php 
ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$logger = __DIR__.'/ci_log.log';                                     // создание лога и директории
$size_logger = filesize($logger);
    if ( $size_logger>5462000 ) file_put_contents($logger, '');      // 5mb , проверка на размер лога если более 11mb очистка

// /**артикулы товаров */
// $sku['article'] = ["lesublack","ker140"];

// /**получение продуктов по sku */
// $getGoods = getGoods($sku,$config_horoshop['token']);
// dd($getGoods);

$page = 0; $limit = 500;
$offset = $page * $limit;

$allGoods = $getGoods = array();
/**получение всех  продуктов по нужным полям*/
$isProduct = true;
    while ($isProduct == true) {
        $offset = $page * $limit;
        $getGoods = getAllGoods($offset ,$config_horoshop['token'],$limit);
        // dd($offset);dd($getGoods);
            if (!$getGoods) {
                $isProduct = false;
            }
        $allGoods = array_merge($allGoods, $getGoods);
        $page++;
    }
// dd($allGoods);// exit;
//для теста
/** [parent_article] => MWP22/C
    [article] => MWP22/C
    [price] => 2299
    [price_old] => 0 */
foreach ($allGoods as $good) {
    $article = 'MWP22/C';
    if ($good->article == $article) { 

    // if ($check=@mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `ci_kiddsvit_goods_meta`  WHERE `meta_key` = 'Артикул' and `meta_value`='{$good->article}'"))[0]) { 
        // $product['name'] = $good->title->ru;
        // echo "SELECT `id_goods` FROM `ci_kiddsvit_goods_meta`  WHERE `meta_key` = 'Артикул' and `meta_value`= '{$article}'";exit;
        // $product['article'] = $good->article;
        $product['article'] = $article;

        $product['price_old'] = 0;
        $body['products'][] = $product;
        // dd($body);
        $UpdateGood = UpdateGood($body, $config_horoshop['token']);
        dd($UpdateGood);
    }else {
        echo "надо добавить"."</br>";
        // dd($check);
    }
    dd($good);
}