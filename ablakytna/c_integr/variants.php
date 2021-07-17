<?php
ini_set('display_errors', 'on');
set_time_limit(600);
require_once 'funcs.php';
require_once 'config.php';

$current_time = date("y-m-d h:i:s");

$logger = __DIR__.'/ci_log.log';                                     // создание лога и директории
$size_logger = filesize($logger);
    if ( $size_logger>5462000 ) file_put_contents($logger, '');      // 5mb , проверка на размер лога если более 11mb очистка

    $variants = mysqli_query($db,"SELECT * FROM `_DWA_posts` WHERE `post_type` = 'product_variation' ORDER BY `ID` DESC");
    // $variants = mysqli_query($db,"SELECT * FROM `_DWA_posts` WHERE `post_type` = 'product_variation' AND `ID` = '5496'");
    // $i = 1;
    foreach ($variants as $variant) {
        //получение данных для модификаций
        $variant['sku'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$variant['ID']}' and `meta_key` = '_sku'"))['meta_value'];
            if (empty($variant['sku'])) { // проверка на существование
                $variant['sku'] = '';
                file_put_contents($logger,date('Y-m-d H:i:s').' нет SKU - '.$variant['ID'].'  '.$variant['post_title']."\n",FILE_APPEND);
            }
        $variant['price'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$variant['ID']}' and `meta_key` = '_price'"))['meta_value'];
            if (empty($variant['price'])) { // проверка на существование
                $variant['price']  = 0 ;
                file_put_contents($logger,date('Y-m-d H:i:s').' нет Price - '.$variant['ID'].'  '.$variant['post_title']."\n",FILE_APPEND);  
            }
        $variant['price'] = (float)$variant['price']*100;

        $getPrice = getPrice($variant['price']);                       // получение meta для цены

        /**получение характеристик для товара */
        $variant['character'] = explode(":", $variant['post_excerpt']);
        $name_character = $variant['character'][0];
        $value_character = $variant['character'][1];
        dd($name_character);
        dd($value_character);
        // exit;
        
        /**получение sku основного товара по его  post_parent для модификации*/
        $variant['sku_parent'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$variant['post_parent']}' and `meta_key` = '_sku'"))['meta_value'];

        /**id товара для модификации */
        $getIdGoods = getIdGoods($variant['sku_parent']);
        if (!empty($getIdGoods->errors[0])) {
            file_put_contents($logger,date('Y-m-d H:i:s').' ошибка получения id товара - '.$getIdGoods->errors[0]->error.' id-'.$variant['ID'].' name- '.$variant['post_title']."\n",FILE_APPEND);
            echo $getIdGoods->errors[0]->error;
        }

        dd($variant);
        dd($variant['sku']);
        dd($variant['price']);
        dd($getIdGoods);
            // exit;
        /**создание body для модификации */
        $body["name"] = $variant['post_title'];
        $body["code"] = $variant['sku'];
        $body["salePrices"] = $getPrice;
        $body["product"] = array(
            "meta"=> array(
                "href"=> "https://online.moysklad.ru/api/remap/1.2/entity/product/".$getIdGoods,
                "metadataHref"=>"https://online.moysklad.ru/api/remap/1.2/entity/product/metadata",
                "type"=>"product",
                "mediaType"=> "application/json"

            )
        );
        $body["characteristics"] = [
                
            array(
                "value" => $value_character,
                "name" => $name_character
                )
            ];

        dd($body);
        /**создание модификаций для товара */

        $createMod = createMod($body); //для модификаций
        dd($createMod);
        if (!empty($createMod->errors[0])) {
            file_put_contents($logger,date('Y-m-d H:i:s').' ошибка создания модификации - '.$createMod->errors[0]->error.' id-'.$variant['ID'].' name- '.$variant['post_title']."\n",FILE_APPEND);
            echo $createMod->errors[0]->error;
        }
        exit;
    }
