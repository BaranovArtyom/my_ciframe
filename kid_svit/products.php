<?php
ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$logger = __DIR__.'/ci_log.log';                                // создание лога и директории
$size_logger = filesize($logger);
if ( $size_logger>11462000 ) file_put_contents($logger, '');    // 11mb , проверка на размер лога если более 11mb очистка

$getProduct = getProduct($KIDD_USER, $KIDD_PASSWORD);           // получение товаров из ссылки

$products = new SimpleXMLElement($getProduct);                  // 

    foreach ($products as $product) {                           // проверка на существование в таблице
        if (!$check=mysqli_fetch_row(mysqli_query($db,"SELECT `artikul` FROM `products`  WHERE `artikul`= '{$product->Артикул}'"))[0]){
            
        /**заполнение таблицы в products в бд */
            $insertProd = mysqli_query($db,"INSERT INTO `products` (`id`, `artikul`) 
            VALUES (NULL, '{$product->Артикул}') ");
                if (mysqli_error($db)) {                        // проверка на  ошибку в запросе mysql записью в лог
                    file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db)."\n",FILE_APPEND);
                }
            echo "insert".$product->Артикул;
            dd($product->Артикул);
        }else {
            $updateProd = mysqli_query($db,"UPDATE `product` SET `artikul` = '{$product->Артикул}' WHERE `artikul`= '{$product->Артикул}'");
            if (mysqli_error($db)) {                            // проверка на  ошибку в запросе mysql записью в лог
                file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db)."\n",FILE_APPEND);
            }
            echo "update".$product->Артикул."<br>";
        }
    }
