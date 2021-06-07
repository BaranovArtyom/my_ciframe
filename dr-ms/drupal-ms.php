<?php 
ini_set('display_errors', 'on');
require_once 'config.php';                                 
require_once 'funcs.php';                               

/**Получение товаров из drupal */
/** field_data_name_field   */

$getProductDrupal = mysqli_query($db,"SELECT * FROM `commerce_product`");
// dd($getProductDrupal);
$rev  = array();
foreach ($getProductDrupal as $val ){
    // dd($val);
    $revision_id['revision_id'] = $val['revision_id'];      // получение revision_id
    $revision_id['product_id'] = $val['product_id'];        // получение product_id
    $revision_id['sku'] = $val['sku'];                      // получение sku
    $revision_id['status'] = $val['status'];                // получение status
    $revision_id['type'] = $val['type'];                // получение status
   
    // $revision_id['revision_id'] = 151;      // получение revision_id  915  164
    // $revision_id['product_id'] = 153599 ;  //156368   153613   154369
    // dd($revision_id['product_id']);
    /**получение id категории по   $revision_id['product_id']*/
    $get_id_category = '';
    $get_id_category =  mysqli_fetch_row(mysqli_query($db,"SELECT revision_id FROM `field_data_field_product` WHERE  `field_product_product_id` = '{$revision_id['product_id']}'"))[0];
    // dd($get_id_category);exit;
    $revision_id['get_id_category'] = $get_id_category;


    /**получение цены товара  по    $revision_id['revision_id']*/
    $get_price_product = '';
    $get_price_product = mysqli_fetch_row(mysqli_query($db,"SELECT commerce_price_amount FROM `field_data_commerce_price` WHERE  `entity_id` = '{$revision_id['product_id']}' "))[0];
    // $get_id_category = 127;
    // dd($get_price_product);
    $revision_id['get_price_product'] = $get_price_product;

    /**проверка есть ли дисконт у товара */
    $get_has_discount = $get_value_discount = '';
    $get_has_discount = mysqli_fetch_row(mysqli_query($db,"SELECT field_product_has_discount_value FROM `field_data_field_product_has_discount` WHERE  `revision_id` = '{$revision_id['get_id_category']}'"))[0];
        // dd($get_has_discount);
        file_put_contents('looger.log',date('H:i:s').' наличие скидки '.$get_has_discount."\n",FILE_APPEND);

        if ($get_has_discount) {                // проверка на наличие скидки 
            // echo "true";                        // перерасчет цены со скидкой
            $revision_id['get_value_discount'] = '';
            $get_value_discount = mysqli_fetch_row(mysqli_query($db,"SELECT field_product_discount_value FROM `field_data_field_product_discount` WHERE  `revision_id` = '{$revision_id['get_id_category']}'"))[0];
            $revision_id['get_value_discount'] = $get_value_discount;
            if (isset($get_value_discount)) {   //проверка на установлена скидка
                $revision_id['get_price_product'] = $revision_id['get_price_product']*$get_value_discount/100;
                // dd($get_value_discount);
                // dd($get_price_product);
                file_put_contents('looger.log',date('H:i:s').' значение скидки '.$get_value_discount ."\n",FILE_APPEND);
            }else {
                // echo "<br>"."не установлен процент скидки";
                // dd($get_price_product); 
                file_put_contents('looger.log',date('H:i:s').' не установлен процент скидки'.$get_value_discount ."\n",FILE_APPEND);
            }
            // dd($get_value_discount);
        }else {
            // echo "false";
        }
        file_put_contents('looger.log',date('H:i:s').' конечная цена товара '.$revision_id['get_price_product']."\n",FILE_APPEND);

        // exit;
    /**получение id category_product по   $get_id_category*/
    $get_id_category_product = '';
    $get_id_category_product = mysqli_fetch_row(mysqli_query($db,"SELECT field_product_category_tid FROM `field_data_field_product_category` WHERE  `revision_id` = '{$revision_id['get_id_category']}'"))[0];
    $revision_id['get_id_category_product'] = $get_id_category_product;

    // dd($get_id_category_product);
    /**получение имени category_product по   $get_id_category_product*/
    $get_name_category_product = '';
    $get_name_category_product = mysqli_fetch_row(mysqli_query($db,"SELECT name_field_value FROM `field_data_name_field` WHERE  `revision_id` = '{$revision_id['get_id_category_product']}' and `bundle`= 'category'"))[0];
    $revision_id['get_name_category_product'] = $get_name_category_product;
    if (!isset($revision_id['get_name_category_product'])){
        $revision_id['get_name_category_product'] = mysqli_fetch_row(mysqli_query($db,"SELECT name_field_value FROM `field_data_name_field` WHERE  `revision_id` = '{$revision_id['get_id_category_product']}' and `bundle`= 'category' "))[0];;
    }
    // dd($get_name_category_product);

    // /**получение имени страны  по   $get_id_category_product ????*/
    // $get_name_country_product = mysqli_fetch_row(mysqli_query($db,"SELECT name_field_value FROM `field_data_name_field` WHERE  `revision_id` = '$get_id_category_product' and `bundle`= 'strana' "))[0];
    // dd($get_name_country_product);
    //field_data_field_product_facets

     /**получение  списка  facets по   $get_id_category_product ????*/
    // $get_field_data_field_product_facets = '';
    $get_field_data_field_product_facets = mysqli_query($db,"SELECT field_product_facets_target_id FROM `field_data_field_product_facets` WHERE  `revision_id` = '{$revision_id['get_id_category']}' ");
    //  dd($get_field_data_field_product_facets);
    // $revision_id['get_field_data_field_product_facets'] = $get_field_data_field_product_facets;
    $revision_id['strana'] =  $revision_id['provincija']= $revision_id['vid']=$revision_id['forma']=$revision_id['sort']=$revision_id['proizvoditel']='';
    $revision_id['svoystva'] = $revision_id['vkus']= array();
        foreach($get_field_data_field_product_facets as $valchar) { // перечисление характеристик товара
                // dd($valchar['field_product_facets_target_id']);
                
                $getCharProd = mysqli_fetch_row(mysqli_query($db,"SELECT bundle,name_field_value FROM `field_data_name_field` WHERE  `revision_id` = '{$valchar['field_product_facets_target_id']}' "));
                // dd($getCharProd[0]);
                // dd($getCharProd[1]);
                if ($getCharProd[0] == 'strana') {
                    $revision_id['strana'] = $getCharProd[1];
                }
                if ($getCharProd[0] == 'svoystva') {
                    $revision_id['svoystva'][] = $getCharProd[1];
                }
                if ($getCharProd[0] == 'vkus') {
                    $revision_id['vkus'][] = $getCharProd[1];
                }
                if ($getCharProd[0] == 'provincija') {
                    $revision_id['provincija'] = $getCharProd[1];
                }
                if ($getCharProd[0] == 'forma') {
                    $revision_id['forma'] = $getCharProd[1];
                }
                if ($getCharProd[0] == 'vid') {
                    $revision_id['vid'] = $getCharProd[1];
                }
                if ($getCharProd[0] == 'sort') {
                    $revision_id['sort'] = $getCharProd[1];
                }
                if ($getCharProd[0] == 'proizvoditel') {
                    $revision_id['proizvoditel'] = $getCharProd[1];
                }
        }
    // exit;
    /**получение имени товара  по    $get_id_category*/
    $get_name_product = mysqli_fetch_row(mysqli_query($db,"SELECT title FROM `node` WHERE  `nid` = '{$revision_id['get_id_category']}'"))[0];

    /**получение старой цены товара  по    $revision_id['revision_id']*/
    $get_old_price_product = '';
    $get_old_price_product = mysqli_fetch_row(mysqli_query($db,"SELECT field_prod_old_price_amount FROM `field_data_field_prod_old_price` WHERE  `entity_id` = '{$revision_id['product_id']}' "))[0];
    $revision_id['get_old_price_product'] = $get_old_price_product;
    if (is_null($get_old_price_product)){
        // echo "нет старой цены";
        file_put_contents('looger.log',date('H:i:s').' нет старой цены '.$revision_id['product_id']."\n",FILE_APPEND);
    }else {
        // echo "старая цена - ". $get_old_price_product;
        file_put_contents('looger.log',date('H:i:s').' старая цена -  '.$revision_id['get_old_price_product']." ".$revision_id['product_id']."\n",FILE_APPEND);
        
    }
    
    /**получение наличие товара  по    $revision_id['revision_id']*/
    $get_nalichie_product = '';
    $get_nalichie_product = mysqli_fetch_row(mysqli_query($db,"SELECT field_prod_nalichie_value FROM `field_data_field_prod_nalichie` WHERE  `entity_id` = '{$revision_id['product_id']}' "))[0];
    $revision_id['get_nalichie_product'] = $get_nalichie_product;


    /**получение поля ид для фасовки  по    $revision_id['revision_id']*/
    $get_field_prod_size = mysqli_fetch_row(mysqli_query($db,"SELECT field_prod_size_tid FROM `field_data_field_prod_size` WHERE  `entity_id` = '{$revision_id['product_id']}' "))[0];
     /**получение фасовки  по    $revision_id['revision_id']*/
    
    $get_fasovka_prod = '';
    $get_fasovka_prod = mysqli_fetch_row(mysqli_query($db,"SELECT name_field_value FROM `field_data_name_field` WHERE  `entity_id` = '{$get_field_prod_size}' and `bundle`= 'size' "))[0];
    $revision_id['get_fasovka_prod'] = $get_fasovka_prod;

    // dd($revision_id['get_id_category']);
    // dd($get_name_product);
    // dd($revision_id['get_id_category_product']);
    // dd($revision_id['get_id_category_product']);
    // dd($revision_id['get_price_product']);
    // dd($get_nalichie_product);
    // dd($get_field_prod_size);
    // dd($get_fasovka_prod);
    // dd($get_value_discount);
    // dd($revision_id['strana_product']);
    
    // $revision_id['title']= $val['title'];
    $revision_id['title'] = '';
    $revision_id['title'] = mysqli_fetch_row(mysqli_query($db,"SELECT title_field_value FROM `field_data_title_field` WHERE  `entity_id` = '{$revision_id['get_id_category']}' and `bundle`= 'product' and `language`= 'uk'"))[0];
    $revision_id['title'] = $revision_id['title']." (".$revision_id['get_fasovka_prod'].")";
    if (!isset($revision_id['title'])){
        $revision_id['title'] = mysqli_fetch_row(mysqli_query($db,"SELECT title_field_value FROM `field_data_title_field` WHERE  `entity_id` = '{$revision_id['get_id_category']}' and `bundle`= 'product'"))[0];
        $revision_id['title'] = $revision_id['title']." (".$revision_id['get_fasovka_prod'].")";
        file_put_contents('looger.log',date('H:i:s').' нет украинского названия -  '.$revision_id['get_id_category']."\n",FILE_APPEND);
    }

    $rev[] = $revision_id;
    // dd($rev);
    // exit;
}
// dd($rev);

