<?php
ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$logger = __DIR__.'/ci_log.log';                                // создание лога и директории
$size_logger = filesize($logger);
if ( $size_logger>5462000 ) file_put_contents($logger, '');    // 5mb , проверка на размер лога если более 11mb очистка

$getProduct = getProduct($KIDD_USER, $KIDD_PASSWORD);           // получение товаров из ссылки
// dd($getProduct);exit;
$products = new SimpleXMLElement($getProduct);                  // 

    foreach ($products as $product) {                           // проверка на существование в таблице
        dd($product);
        $name_product = addslashes($product->НаименованиеПолное); // экранирование имени
        $power_need = addslashes($product->Питание);
        $material = addslashes($product->Материал);
        $komplekt_in = addslashes($product->В_комплект_входит);
        $made_in = addslashes($product->Страна_происхождения);
        $rekomenden_year = addslashes($product->Рекомендация_по_возрасту_от);
        $name_N1 = addslashes($product->Наименование_Н1); 
        $brand = addslashes($product->Бренд);
        $proizvoditel = addslashes($product->Производитель);
        $href_image = addslashes($product->Путь_к_файлу_с_изображением_FTP);
        $descption = addslashes($product->Описание);
        // dd($rekomenden_year);
        if (!$check=mysqli_fetch_row(mysqli_query($db,"SELECT `name_product` FROM `products`  WHERE `artikul`= '{$product->Артикул}'"))[0]){
            
        /**заполнение таблицы в products в бд */

            $insertProd = mysqli_query($db,"INSERT INTO `products` (`id`,`artikul`,`name_product`,`price`,`quantity`,`shelf_life`,`power_need`,`batteries`,`material`,`color`,`komplekt_in`,`made_in`,`rekomenden_year`,`play_to`,
            `sex`,`status_product`,`type_individual_pack`,`code`,`name_N1`,`brand`,`proizvoditel`,`sub_category`,`video`,`href_site`,`href_image`,`descption`,`barcode`,`length`,`width`,`height`,
            `weight_brutto`,`weight`) 
            VALUES (NULL, '{$product->Артикул}','{$name_product}','{$product->Цена}','{$product->КоличествоОстаток}','{$product->Срок_годности_мес}','{$power_need}','{$product->Батарейки_входят_в_комплект}',
            '{$material}','{$product->Цвет}','{$komplekt_in}','{$made_in}','{$rekomenden_year}','{$product->Интересно_играть_в_возрасте_до}','{$product->Пол}','{$product->Статус}',
            '{$product->Тип_индивидуальной_упаковки}','{$product->Код_УКТВЭД}','{$name_N1}','{$brand}','{$proizvoditel}','{$product->Подкатегория}','{$product->Видеообзор}','{$product->Ссылка_на_сайт}',
            '{$href_image}','{$descption}','{$product->Штрихкод}','{$product->Длина_без_упаковки}','{$product->Ширина_без_упаковки}','{$product->Высота_без_упаковки}',
            '{$product->Вес_брутто}','{$product->Вес_без_упаковки}') ");
                if (mysqli_error($db)) {                        // проверка на  ошибку в запросе mysql записью в лог
                    file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$name_product."\n",FILE_APPEND);
                }
            echo "insert".$product->Артикул."<br>";
        }else {
            $updateProd = mysqli_query($db,"UPDATE `products` SET `artikul` = '{$product->Артикул}',`name_product` = '{$name_product}',`price`= '{$product->Цена}',`quantity`='{$product->КоличествоОстаток}',
            `shelf_life`='{$product->Срок_годности_мес}',`power_need`='{$power_need}',`batteries`='{$product->Батарейки_входят_в_комплект}',`material`='{$material}',
            `color`='{$product->Цвет}',`komplekt_in`='{$komplekt_in}',`made_in`='{$made_in}',`rekomenden_year`= '{$rekomenden_year}',`play_to`='{$product->Интересно_играть_в_возрасте_до}',
            `sex`='{$product->Пол}',`status_product`='{$product->Статус}',`type_individual_pack`='{$product->Тип_индивидуальной_упаковки}',`code`='{$product->Код_УКТВЭД}',
            `name_N1`='{$name_N1}',`brand`='{$brand}',`proizvoditel`='{$proizvoditel}',`sub_category`='{$product->Подкатегория}',`video`='{$product->Видеообзор}',
            `href_site`='{$product->Ссылка_на_сайт}',`href_image`='{$href_image}',`descption`='{$descption}',`barcode`='{$product->Штрихкод}',
            `length`='{$product->Длина_без_упаковки}',`width`='{$product->Ширина_без_упаковки}',`height`='{$product->Высота_без_упаковки}',`weight_brutto`='{$product->Вес_брутто}',
            `weight`='{$product->Вес_без_упаковки}' WHERE `artikul`= '{$product->Артикул}'");
            if (mysqli_error($db)) {                            // проверка на  ошибку в запросе mysql записью в лог
                file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$name_product."\n",FILE_APPEND);
            }
            echo "update".$product->Артикул."<br>";
        }
    }
