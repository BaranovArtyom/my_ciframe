<?php 
ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$logger = __DIR__.'/ci_log.log';                                // создание лога и директории
$size_logger = filesize($logger);
    if ( $size_logger>5462000 ) file_put_contents($logger, '');    // 5mb , проверка на размер лога если более 11mb очистка

$getProduct = getProduct($conf['user'], $conf['password']);           // получение товаров из ссылки

$products = new SimpleXMLElement($getProduct);                  // 

    foreach ($products as $product) {                           // проверка на существование в таблице

        /**получение id товаров из таблицы ci_kiddsvit_goods */
        $getGoodId = mysqli_fetch_assoc(mysqli_query($db,"SELECT id FROM `ci_kiddsvit_goods` WHERE `sku`='{$sku}'"));
        
        foreach ($product as $key=>$attr) {
            $attr = addslashes($attr); ;
            if (!$check=mysqli_fetch_row(mysqli_query($db,"SELECT `meta_key` FROM `ci_kiddsvit_goods_meta`  WHERE `good_id`= '{$getGoodId['id']}'"))[0]){

                /**заполнение таблицы в ci_kiddsvit_goods в бд */
                $insertProd = mysqli_query($db,"INSERT INTO `ci_kiddsvit_goods_meta` (`id`,`id_goods`,`meta_key`,`meta_value`) 
                    VALUES (NULL,'{$getGoodId['id']}','{$key}','{$attr}') ");
                        if (mysqli_error($db)) {                        // проверка на  ошибку в запросе mysql записью в лог
                            file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$name_product."\n",FILE_APPEND);
                        }
                    echo "insert".$product->Артикул."<br>";
                }else {
                    $updateProd = mysqli_query($db,"UPDATE `ci_kiddsvit_goods_meta` SET `id_goods`='{$getGoodId['id']}',`meta_key`='{$key}',`meta_value`= '{$attr}'
                    WHERE `good_id`= '{$getGoodId['id']}'");
                    if (mysqli_error($db)) {                            // проверка на  ошибку в запросе mysql записью в лог
                        file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$name_product."\n",FILE_APPEND);
                    }
                    echo "update".$product->Артикул."<br>";
                }
        }
    }