<?php
ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$getProduct = getProduct();                     // получение товаров из ссылки
$products = new SimpleXMLElement($getProduct);  // 

$check=@mysqli_fetch_row(@mysqli_query($db,"SELECT `meta_prod` FROM `products`  WHERE `meta_prod`= '$meta_prod'"))[0];
dd(mysqli_error($db));
file_put_contents('logger.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db)."\n",FILE_APPEND);
// exit;
// $i = 0;
// foreach ($products as $product) {
//     // dd($product->Артикул);
//     dd($product);
//     // $prod['id'] = $i++;
//     $prod['barcode'] = $product->Штрихкод;
//     // echo $prod['barcode'];
//     $pr[] = $prod; 
// }

// dd($pr);
$logger = __DIR__.'/logger.log';
$size = filesize($logger);
if ($size>11462000) file_put_contents($logger, ''); // 11mb