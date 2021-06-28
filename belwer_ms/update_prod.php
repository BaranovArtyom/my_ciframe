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

$checkUpdateDemand = checkUpdateDemand();
// dd($checkUpdateassort);exit;

foreach ($checkUpdateDemand as $UpdateDemand) {
    $date_d = explode(' ',$UpdateDemand->moment); // получение даты 
    $data = strtotime($date_d[0]);

    $number_order_d = $UpdateDemand->name;
    $kol_pos_d = $UpdateDemand->positions->meta->size;
    $sum_order_d = $UpdateDemand->sum;
    $id_seller_d = addslashes($UpdateDemand->owner->meta->href);
    $id_agent_d = addslashes($UpdateDemand->agent->meta->href);
    $positions_d = addslashes($UpdateDemand->positions->meta->href);

    if (!$check_d=@mysqli_fetch_row(@mysqli_query($db,"SELECT `number_order` FROM `demand`  WHERE `number_order`= '$number_order_d'"))[0]){
        /**заполнение таблицы в retail_demand в бд */
        $insert_d = mysqli_query($db,"INSERT INTO `demand` (`id`, `number_order`, `moment`,`kol_pos`,`sum_order`,`id_seller`,`id_agent`,`href_positions`) 
        VALUES (NULL, '{$number_order_d}','{$data}','{$kol_pos_d}','{$sum_order_d}','{$id_seller_d}','{$id_agent_d}','{$positions_d}') ");
        file_put_contents('logger.log',date('Y-m-d H:i:s').'  создали отгрузку - '.$number_order_d."\n",FILE_APPEND);
        file_put_contents('logger.log',date('Y-m-d H:i:s').'  создали отгрузку - '.mysqli_error($db)."\n",FILE_APPEND);
        
    }else {
        $update_d = mysqli_query($db,"UPDATE `demand` SET `kol_pos` = '$kol_pos_d',`sum_order` = '$sum_order_d',`id_seller` = '$id_seller_d', `id_agent` = '$id_agent_d' ,`href_positions` = '$positions_d'  WHERE `number_order`= '$number_order_d'");
        file_put_contents('logger.log',date('Y-m-d H:i:s').'  update отгрузку - '.$number_order_d."\n",FILE_APPEND);
        // dd($updateProd);
        file_put_contents('logger.log',date('Y-m-d H:i:s').'  update отгрузку- '.mysqli_error($db)."\n",FILE_APPEND);
        // echo "update".$number_order_d;
    }
}

$checkUpdateReatailDemand = checkUpdateReatailDemand();
foreach ($checkUpdateReatailDemand as $Update_retail) {
    $date = explode(' ',$Update_retail->moment); // получение даты 
    $data = strtotime($date[0]);

    $number_order = $Update_retail->name;
    $kol_pos = $Update_retail->positions->meta->size;
    $sum_order = $Update_retail->sum;
    $id_seller = addslashes($Update_retail->owner->meta->href);
    $id_agent = addslashes($Update_retail->agent->meta->href);
    $positions = addslashes($Update_retail->positions->meta->href);

    if (!$check_ret=@mysqli_fetch_row(@mysqli_query($db,"SELECT `number_order` FROM `retail_demand`  WHERE `number_order`= '$number_order'"))[0]){
        /**заполнение таблицы в retail_demand в бд */
        $insert_ret = mysqli_query($db,"INSERT INTO `retail_demand` (`id`, `number_order`, `moment`,`kol_pos`,`sum_order`,`id_seller`,`id_agent`,`href_positions`) 
        VALUES (NULL, '{$number_order}','{$data}','{$kol_pos}','{$sum_order}','{$id_seller}','{$id_agent}','{$positions}') ");
        file_put_contents('logger.log',date('Y-m-d H:i:s').'  update  розницу- '.mysqli_error($db)."\n",FILE_APPEND);
        file_put_contents('logger.log',date('Y-m-d H:i:s').'  создали розницу - '.$number_order."\n",FILE_APPEND);
        // dd($update_ret);
                
    }else {
        $update_ret = mysqli_query($db,"UPDATE `retail_demand` SET `kol_pos` = '$kol_pos',`sum_order` = '$sum_order',`id_seller` = '$id_seller', `id_agent` = '$id_agent',`href_positions` = '$positions'  WHERE `number_order`= '$number_order'");
        file_put_contents('logger.log',date('Y-m-d H:i:s').'  update  розницу- '.$number_order."\n",FILE_APPEND);
        // file_put_contents('logger.log',date('Y-m-d H:i:s').'  update  розницу- '.$kol_pos."\n",FILE_APPEND);
        // file_put_contents('logger.log',date('Y-m-d H:i:s').'  update  розницу- '.$sum_order."\n",FILE_APPEND);
        // file_put_contents('logger.log',date('Y-m-d H:i:s').'  update  розницу- '.$id_seller."\n",FILE_APPEND);
        // file_put_contents('logger.log',date('Y-m-d H:i:s').'  update  розницу- '.$id_agent."\n",FILE_APPEND);
        // file_put_contents('logger.log',date('Y-m-d H:i:s').'  update  розницу- '.$positions."\n",FILE_APPEND);
        file_put_contents('logger.log',date('Y-m-d H:i:s').'  update  розницу- '.mysqli_error($db)."\n",FILE_APPEND);
        dd($update_ret);
        // echo "update".$number_order_d;
    }

}