<?php
ini_set('display_errors', 'on');
require_once 'funcs.php';

$getProduct = getProduct();                     // получение товаров из ссылки

$products = new SimpleXMLElement($getProduct);  // 

$i = 0;
foreach ($products as $product) {
    // dd($product->Артикул);
    dd($product);
    // $prod['id'] = $i++;
    $prod['barcode'] = $product->Штрихкод;
    // echo $prod['barcode'];
    $pr[] = $prod; 
}

// dd($pr);