<?php
require_once "func.php";

/**для вывода и сохранения ошибок */
ini_set('display_errors', 1);
ini_set('error_log', 'logger.log');
error_reporting(E_ALL);


$getProductId = getProductId(); // получение id всех продуктов
// dd($getProductId);
foreach ($getProductId  as $idProduct) {
    // $idProduct['id'] = "f47dd39a-8ca3-11eb-0a80-040b000edbcd";
    // получение остатков по id товара
    $getStockProduct = getStockProduct($idProduct['id']);
    dd($getStockProduct);
    /**Проверка остатков в складах */
    foreach ($getStockProduct as $value){
        if ($value['main'] > 0) {
            echo $value['id']."в наличии";
            $getStatusAvail = getStatusAvail($idProduct['id']);
            file_put_contents('logger.log',date('H:i:s').' основной склад остаток - '.$getStatusAvail."\n",FILE_APPEND);

        }elseif ($value['main'] == 0 and $value['people'] > 0){

            echo $value['id']."Ожидание 2-3 дня";
            $getStatusAwait = getStatusAwait($idProduct['id']);
            file_put_contents('logger.log',date('H:i:s').' основной склад остаток - '.$getStatusAwait."\n",FILE_APPEND);

        }else {
            echo $value['id']."Нет в наличии";
            $getStatusNotAvail = getStatusNotAvail($idProduct['id']);
            file_put_contents('logger.log',date('H:i:s').' основной склад остаток - '.$getStatusNotAvail."\n",FILE_APPEND);
        }
        exit;
    }
    
}
