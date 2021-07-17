<?php

ini_set('display_errors', 'on');
require_once 'funcs.php';
require_once 'config.php';

$current_time = date("y-m-d h:i:s");

$logger = __DIR__.'/ci_log.log';                                     // создание лога и директории
$size_logger = filesize($logger);
    if ( $size_logger>5462000 ) file_put_contents($logger, '');      // 5mb , проверка на размер лога если более 11mb очистка

    $goods = mysqli_query($db,"SELECT * FROM `_DWA_posts` WHERE `post_type` = 'product' ORDER BY `ID` DESC");
    // $goods = mysqli_query($db,"SELECT * FROM `_DWA_posts` WHERE `post_type` = 'product' AND `ID` = '10841'");
    // dd($goods);
    // exit;
foreach ($goods as $good) {
    // dd($good);
    // dd($good['ID']);
    /**получаем связи по id товара таблица _DWA_term_relationships */
    $relationships = mysqli_query($db,"SELECT * FROM `_DWA_term_relationships` WHERE `object_id` = '{$good['ID']}'");
    // $relationships = mysqli_query($db,"SELECT * FROM `_DWA_term_relationships` WHERE `object_id` = '10821'");

    /**получаем поля по таблице postmeta */
    $good['sku'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$good['ID']}' and `meta_key` = '_sku'"))['meta_value'];
    
    /**получаем галерею товаров _product_image_gallery*/
    $good['product_image_gallery'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT meta_value FROM `_DWA_postmeta` WHERE `post_id` = '{$good['ID']}' and `meta_key` = '_product_image_gallery'"))['meta_value'];
    $good['product_image_gallery'] = explode(',',$good['product_image_gallery']);
    dd($good);
    
    $productImage = $body = array();
        foreach ($good['product_image_gallery'] as $image) {
            // dd($image);
            $image_data = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM `_DWA_posts` WHERE `ID` = '{$image}'"));
            // dd($image_data);
            $image_name = $image_data['post_title'].'jpg';
            $image_url = $image_data['guid'];
            // dd($image_url);
            $content = base64_encode(file_get_contents($image_url));

            $get_images = get_image($image_name, $content); // Для image
            $productImage[] = $get_images;
                // $body["images"] = $productImage;
            // dd($productImage);

        }
        $body["images"] = $productImage;
    dd($body);
    // dd($good['sku']);exit;
    // dd($relationships->num_rows);
    if ($relationships->num_rows!=0) {
        foreach ($relationships as $relationship) {
            $term_taxonomy = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM `_DWA_term_taxonomy` WHERE `term_taxonomy_id` = '{$relationship['term_taxonomy_id']}'"));
            // dd($relationship);
            if ($term_taxonomy['taxonomy'] == 'product_cat'){
                $good['name_category'] = mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM `_DWA_terms` WHERE `term_id` = '{$term_taxonomy['term_id']}'"))['name'];
            } 
            dd($term_taxonomy);
            dd($good['name_category']);
            
            

        }
    }else{
        file_put_contents($logger,date('Y-m-d H:i:s').' нет связей - '.$good['ID'].'  '.$good['post_title']."\n",FILE_APPEND);
        echo 'нет связей - '.$good['ID'];
    }
    
    $getIdGoods = getIdGoods($good['sku']);
    dd($getIdGoods);
    // dd($good['ID']);

    // $PutGoods = PutGoods($body,$getIdGoods);
    // dd($PutGoods);
//   exit;
}

