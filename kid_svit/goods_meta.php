<?php 

ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$current_time = date("y-m-d h:i:s");

$logger = __DIR__.'/ci_log.log';                                // создание лога и директории
$size_logger = filesize($logger);
if ( $size_logger>5462000 ) file_put_contents($logger, '');    // 5mb , проверка на размер лога если более 11mb очистка

$getProduct = getProduct($conf['user'], $conf['password']);           // получение товаров из ссылки
// dd($getProduct);exit;
$products = new SimpleXMLElement($getProduct);                  // 
/**получение товаров из таблицы ci_kiddsvit_goods */
// dd($getGoods['name']);

foreach ($products as $product) {                           // проверка на существование в таблице
    // dd($product);
    // dd(count($product));
    $name_product = addslashes($product->НаименованиеПолное); // экранирование имени
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
    $sku = addslashes($product->Артикул);

    $getGoodId = mysqli_fetch_assoc(mysqli_query($db,"SELECT id FROM `ci_goods_kiddsvit` WHERE `sku`='{$sku}'"));
    // dd($getGoodId);exit;
    // dd($product);
    foreach ($product as $key=>$attr) {
        $attr = addslashes($attr); 
        $key=transliterate($key);
            $getGood = $getGoodId['id'];
             // unset($count);
            $count = mysqli_query($db,"SELECT COUNT(*) FROM `ci_goods_meta_kiddsvit` WHERE `good_id` = '$getGood' AND `meta_key` LIKE '$key'");

            $count = mysqli_fetch_assoc($count);

            if ($count['COUNT(*)'] == 0){
               echo '<br>'.'insert'. $key.' '.$attr.' '.$getGoodId['id'].'<br>';
                /**заполнение таблицы в ci_kiddsvit_goods в бд */
                    mysqli_query($db,"INSERT INTO `ci_goods_meta_kiddsvit` (`id`,`good_id`,`meta_key`,`meta_value`)  VALUES (NULL,'{$getGoodId['id']}','{$key}','{$attr}') ");
                    if (mysqli_error($db)) {                        // проверка на  ошибку в запросе mysql записью в лог
                        file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$name_product."\n",FILE_APPEND);
                    }
                echo "insert".$product->Артикул."<br>";
            }else {
                mysqli_query($db,"UPDATE `ci_goods_meta_kiddsvit` SET `meta_value`= '{$attr}' WHERE `meta_key`= '{$key}' AND `good_id`= '{$getGoodId['id']}'");
                // mysqli_query($db,"UPDATE `ci_kiddsvit_goods_meta` SET `id_goods`='{$getGoodId['id']}',`meta_key`='{$key}',`meta_value`= '{$attr}' WHERE `id_goods`= '{$getGoodId['id']}'");
                // echo '<br>'. $key.' '.$attr.' '.$getGoodId['id'].'<br>';
                if (mysqli_error($db)) {                            // проверка на  ошибку в запросе mysql записью в лог
                    file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$name_product."\n",FILE_APPEND);
                }
                echo "update".$product->Артикул."<br>";
            }
    }
    // exit;
}