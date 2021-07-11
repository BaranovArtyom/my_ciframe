<?php 

ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

/**артикулы товаров */
$sku['article'] = ["lesublack","ker140"];

/**получение продуктов по sku */
$getGoods = getGoods($sku,$config_horoshop['token']);
// dd($getGoods);
$page = 0; $limit = 500;
$offset = $page * $limit;
// $getAllGoods = getAllGoods($offset ,$config_horoshop['token'],$limit);
// if (!$getAllGoods) {
//     echo "empty";
// }
// dd($getAllGoods);exit;

/**перебор всех  продаж розницы*/
$allGoods = array();
$getGoods = array();

$isProduct = true;
while ($isProduct) {
    // $getGoods = array();
    $offset = $page * $limit;
    $getGoods = getAllGoods($offset ,$config_horoshop['token'],$limit);
    dd($offset);
    // dd($getGoods);

    $allGoods[] = $getGoods;

    if (!$getGoods) {
    echo "empty";
    exit;
    }
    // $allGoods = array_merge($allGoods, $getGoods);
    $page++;
}
// dd($allGoods);
exit;

foreach ($getGoods as $good) {
   
    
    // if ($check=@mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `ci_kiddsvit_goods_meta`  WHERE `meta_key` = 'Артикул' and `meta_value`='{$good->article}'"))[0]) { 
    //     // $product['name'] = $good->title->ru;
    //     // echo "SELECT `id_goods` FROM `ci_kiddsvit_goods_meta`  WHERE `meta_key` = 'Артикул' and `meta_value`= '{$article}'";exit;
    //     $product['article'] = $good->article;
    //     $product['price_old'] = 120;
        
    //     $body['products'][] = $product;
    //     // dd($body);
    //     $UpdateGood = UpdateGood($body, $config_horoshop['token']);
    //     dd($UpdateGood);
    // }else {
    //     echo "надо добавить"."</br>";
    //     dd($check);
    // }
    dd($good);
}