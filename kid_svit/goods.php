<?php
ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$current_time = date("y-m-d h:i:s");

$logger = __DIR__.'/ci_log.log';                                // создание лога и директории
$size_logger = filesize($logger);
    if ( $size_logger>5462000 ) file_put_contents($logger, '');    // 5mb , проверка на размер лога если более 11mb очистка

$getProduct = getProduct($conf['user'], $conf['password']);           // получение товаров из ссылки
$products = new SimpleXMLElement($getProduct);                  // 

foreach ($products as $product) {                           // проверка на существование в таблице
    // dd($product);
    // $name_product = addslashes($product->НаименованиеПолное); // экранирование имени
    // $power_need = addslashes($product->Питание);
    // $material = addslashes($product->Материал);
    // $komplekt_in = addslashes($product->В_комплект_входит);
    // $made_in = addslashes($product->Страна_происхождения);
    // $rekomenden_year = addslashes($product->Рекомендация_по_возрасту_от);
    // $name_N1 = addslashes($product->Наименование_Н1); 
    // $brand = addslashes($product->Бренд);
    // $proizvoditel = addslashes($product->Производитель);
    // $href_image = addslashes($product->Путь_к_файлу_с_изображением_FTP);
    // $descption = addslashes($product->Описание);

        if (!$check=mysqli_fetch_row(mysqli_query($db,"SELECT `name` FROM `ci_kiddsvit_goods`  WHERE `sku`= '{$product->Артикул}'"))[0]){

        /**заполнение таблицы в ci_kiddsvit_goods в бд */
        $insertProd = mysqli_query($db,"INSERT INTO `ci_kiddsvit_goods` (`id`,`name`,`sku`,`price`,`qty`,`type`,`updated`) 
            VALUES (NULL,'{$name_product}','{$product->Артикул}','{$product->Цена}','{$product->КоличествоОстаток}','product','{$current_time}') ");
                if (mysqli_error($db)) {                        // проверка на  ошибку в запросе mysql записью в лог
                    file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$name_product."\n",FILE_APPEND);
                }
            echo "insert".$product->Артикул."<br>";
        }else {
            $updateProd = mysqli_query($db,"UPDATE `ci_kiddsvit_goods` SET `sku` = '{$product->Артикул}',`name` = '{$name_product}',`price`= '{$product->Цена}',
            `qty`='{$product->КоличествоОстаток}',`updated`='{$current_time}' WHERE `sku`= '{$product->Артикул}'");
            if (mysqli_error($db)) {                            // проверка на  ошибку в запросе mysql записью в лог
                file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$name_product."\n",FILE_APPEND);
            }
            echo "update".$product->Артикул."<br>";
        }

}