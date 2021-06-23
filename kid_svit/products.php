<?php
ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$logger = __DIR__.'/ci_log.log'; // создание лога и директории
$size_logger = filesize($logger);
if ( $size_logger>11462000 ) file_put_contents($logger, ''); // 11mb , проверка на размер лога если более 11mb очистка

$getProduct = getProduct($KIDD_USER, $KIDD_PASSWORD);                     // получение товаров из ссылки
// file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db)."\n",FILE_APPEND);
// var_dump($getProduct);exit;
$products = new SimpleXMLElement($getProduct);  // 

// file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db)."\n",FILE_APPEND);
foreach ($products as $product) {
    // dd($product->Артикул);
    // if (!$check=@mysqli_fetch_row(@mysqli_query($db,"SELECT `meta_prod` FROM `products`  WHERE `meta_prod`= '$meta_prod'"))[0]){
    //     /**заполнение таблицы в retail_demand в бд */
    //     $insertProd = mysqli_query($db,"INSERT INTO `products` (`id`, `meta_prod`, `name_prod`,`type_prod`,`sum_prod`,`href_product`) 
    //     VALUES (NULL, '{$meta_prod}','{$name_prod}','{$type_prod}','{$sum_prod}','{$href_product}') ");
    //     file_put_contents('logger.log',date('Y-m-d H:i:s').'  создания insertProd - '.$insertProd."\n",FILE_APPEND);
    //     dd($insertProd);
    //     echo "insert".$name_prod;
    // }else {
    //     $updateProd = mysqli_query($db,"UPDATE `products` SET `sum_prod` = '$sum_prod' WHERE `meta_prod`= '$meta_prod'");
    //     file_put_contents('logger.log',date('Y-m-d H:i:s').'  update - '.$insertProd."\n",FILE_APPEND);
    //     dd($updateProd);
    //     echo "update".$name_prod;
    // }

    dd($product);
    // $prod['id'] = $i++;
    $prod['barcode'] = $product->Штрихкод;
    // echo $prod['barcode'];
    $pr[] = $prod; 
}

// dd($pr);

