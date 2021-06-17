<?php
ini_set('display_errors', 'on');
require_once "funcs.php";
require_once "config.php";

// file_put_contents('logger.log',date('Y-m-d H:i:s').'  update - '."\n",FILE_APPEND);
$checkUpdateassort = checkUpdateassort();
// dd($checkUpdateassort);exit;

foreach ($checkUpdateassort as $prod) {
//    dd($prod);exit;
    $meta_prod = $prod->meta->href;
    $name_prod = addslashes($prod->name);
    $type_prod = $prod->meta->type;
    $sum_prod = 0;
        if(isset($prod->attributes)){
            foreach ($prod->attributes as $attribute) {
                // dd($attribute);
                $sum_prod = 0;
                if ($attribute->name == 'Продавец %'){
                    // $procent_prod == $attribute->value;
                    // $sum_zakaza = $prod->salePrices[0]->value/10000*$discount;
                    // $total_sum = $prod->salePrices[0]->value/100;
                    // dd($sum_zakaza);
                    $procent_skidka = (int)$attribute->value;
                    // dd($procent_skidka);
                    // dd($total_sum);
                    $sum_prod = $prod->salePrices[0]->value/10000*$procent_skidka;  //сумма продавца

                }
            }
        }
        $href_product = '';
        if(isset($prod->product)){
            $href_product = $prod->product->meta->href;

        }

        if (!$check=@mysqli_fetch_row(@mysqli_query($db,"SELECT `meta_prod` FROM `products`  WHERE `meta_prod`= '$meta_prod'"))[0]){
            /**заполнение таблицы в retail_demand в бд */
            $insertProd = mysqli_query($db,"INSERT INTO `products` (`id`, `meta_prod`, `name_prod`,`type_prod`,`sum_prod`,`href_product`) 
            VALUES (NULL, '{$meta_prod}','{$name_prod}','{$type_prod}','{$sum_prod}','{$href_product}') ");
            file_put_contents('logger.log',date('Y-m-d H:i:s').'  создания insertProd - '.$insertProd."\n",FILE_APPEND);
            dd($insertProd);
            echo "insert".$name_prod;
        }else {
            $updateProd = mysqli_query($db,"UPDATE `products` SET `sum_prod` = '$sum_prod' WHERE `meta_prod`= '$meta_prod'");
            file_put_contents('logger.log',date('Y-m-d H:i:s').'  update - '.$insertProd."\n",FILE_APPEND);
            dd($updateProd);
            echo "update".$name_prod;
        }
        dd($sum_prod);
}