<?php 
set_time_limit(600);
ini_set('display_errors', 'on');
require_once "funcs.php";
require_once "config.php";


/**получение всех продуктов */
$all_products = array();
$page = 0; $limit = 1000;
$products_size = getProducts();			    // получаем размер продаж розницы
$max_pages = ceil($products_size / $limit);           // количество страниц
// dd($products_size);exit;

/**перебор всех  продаж розницы*/
while ($page < $max_pages) {
    $i= 0;
	$offset = $page * $limit;
	$all_products = getProductAll($offset);            // все 
    // dd($all_products);exit;
        foreach ($all_products as $prod) {
            /**заполнение таблицы */
            // $date = explode(' ', $retailDemand->moment); // получение даты 
            // $data = strtotime($date[0]);
            // $number_order = $retailDemand->name;
            // $kol_pos = $retailDemand->positions->meta->size;
            // $sum_order = $retailDemand->sum;
            // $id_seller = $retailDemand->owner->meta->href;
            // $id_agent = $retailDemand->agent->meta->href;
            // $discount = (float)2.7;
            // dd($prod);
            $meta_prod = $prod->meta->href;
            $name_prod = addslashes($prod->name);
            $type_prod = $prod->meta->type;
            $sum_prod = 0;
                if(isset($prod->attributes)){
                    foreach ($prod->attributes as $attribute) {
                        $sum_prod = 0;
                        if ($attribute->name == 'Продавец %'){
                           
                            $procent_skidka = (int)$attribute->value;
                            $sum_prod = $prod->salePrices[0]->value/10000*$procent_skidka;  //сумма продавца

                        }
                    }
                }
                $href_product = '';
                if(isset($prod->product)){
                    $href_product = $prod->product->meta->href;

                }
            // $i++;
            // echo $i;
            // dd($meta_prod);
                // dd($sum_prod);
            // exit;
            if (!$check=@mysqli_fetch_row(@mysqli_query($db,"SELECT `meta_prod` FROM `products`  WHERE `meta_prod`= '$meta_prod'"))[0]){
                /**заполнение таблицы в retail_demand в бд */
                $insertProd = mysqli_query($db,"INSERT INTO `products` (`id`, `meta_prod`, `name_prod`,`type_prod`,`sum_prod`,`href_product`) 
                VALUES (NULL, '{$meta_prod}','{$name_prod}','{$type_prod}','{$sum_prod}','{$href_product}') ");
                // print_r(mysqli_error($db));
                // exit();
                file_put_contents('logger.log',date('Y-m-d H:i:s').'  создания insertProd - '.$meta_prod."\n",FILE_APPEND);
                dd($insertProd);
            }else {
              /*  $updateProd = mysqli_query($db,"UPDATE `products` SET `sum_prod` = '$sum_prod' WHERE `meta_prod`= '$meta_prod'");
                dd($updateProd);*/
                
            }
            file_put_contents('logger.log',date('Y-m-d H:i:s').'  нет  новой розницы - '."\n",FILE_APPEND);
        }
    $page++;
}