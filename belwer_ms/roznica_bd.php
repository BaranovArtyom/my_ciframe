<?php 

ini_set('display_errors', 'on');
require_once "funcs.php";
// require_once "retailDemand_bd.php";

/**для соединения с базой */
define('DB_HOST', 'localhost');
define('DB_USER', 'sasha');
define('DB_PASSWORD', 'пароль');
define('DB_NAME', 'ms_belwer');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("Ошибка " . mysqli_error($db));
mysqli_set_charset($db, 'utf8');

/**дата для выборки отгрузок*/
// $data_fr = $_POST['data_from'];
$data_fr = '2021-05-01';         
$data_fr = strtotime($data_fr);
// $time = date("y-m-d ",$data_fr);
// echo $time."<br>";
dd($data_fr);
// exit;
// тестовые даты
$data_from = date($data_fr." 00:00:00");                 //дата с 
$data_from = urlencode($data_from);
// dd($data_from);

// $data_t = $_POST['data_to'];
$data_t = '2021-05-20';
$data_t = strtotime($data_t);

$data_to = date($data_t." 23:59:59");                   //дата до
$data_to = urlencode($data_to);

/**массив выбранных продавцов */
if (!empty($_POST['sellerList'])){
    $nameSeller = $_POST['sellerList']; // Аркадий 'Богдан'
}else {
    $nameSeller = [];
}
$nameSeller = ['Морозов А. А.']; // для теста

/**получение продаж розницы */
// $getRetailDemand = getRetailDemand($data_from, $data_to);
// $getRetailDemand = mysqli_query($db,"SELECT * FROM `retail_demand` WHERE `moment`>=$data_fr and `moment`<=$data_t ORDER BY `retail_demand`.`moment` ASC");
//     foreach($getRetailDemand as $retail){
//         $getNameSel =  mysqli_fetch_row(mysqli_query($db,"SELECT name FROM `seller` WHERE  `id_seller` = '{$retail['id_seller']}'"))[0];
//         // dd($retail);
//         // dd($getNameSel);
//         if(!empty($nameSeller)){
//             if (in_array($getNameSel, $nameSeller)){     // проверка на вхождение в массив имена
//                 $roz['agent_name'] = mysqli_fetch_row(mysqli_query($db,"SELECT name FROM `agent` WHERE  `id_agent` = '{$retail['id_agent']}'"))[0];
//                 $roz['name'] = $getNameSel;
//                 $roz['number_order'] = $retail['number_order'];
//                 $roz['date'] = date("y-m-d",$retail['moment']);                     // получение даты
//                 $roz['kol_positions'] = $retail['kol_pos'];
//                 $roz['sum_zakaza'] = $retail['sum_order'];

//                 $roz['sum_prodavec_zakaza'] = 0;
//                 if(isset($retail['kol_pos'])){        
//                     $pos = $retail['href_positions'];                                 // проверка позиций в заказе
//                     $getPositions = getPositions($pos);                               // $pos=https://online.moysklad.ru/api/remap/1.2/entity/retaildemand/0bc7c93a-abe6-11eb-0a80-084800727f49/positions
//                     // dd($getPositions);exit;
                    
//                     foreach ($getPositions as $product) {                               // получение продуктов
//                         // dd((int)$product->price);
//                         // dd($product);exit;
//                         if ((int)$product->discount == 0) {
//                             $discount = $product->discount;
//                             $discount = NULL;
//                         }else {
//                             $discount = $product->discount;
//                         }
                       
//                         if ($product->assortment->meta->type == 'product'){             //проверка ассортимента на продукт или вариант
//                             $getProduct = getProduct($product->assortment->meta->href, $discount);
//                             // dd($getProduct);
//                             $roz['sum_prodavec_zakaza'] += number_format($getProduct, 2, '.', '');
//                         }elseif( $product->assortment->meta->type == 'variant'){
//                             // dd($product);exit;
//                             // $getProduct = getProduct($product->assortment->meta->href);
//                             $getProductVariant = getProductVariant($product->assortment->meta->href);
//                             // dd($getProductVariant);
//                             $getProduct = getProduct($getProductVariant, $discount);
//                             // dd($getProduct);
//                             $roz['sum_prodavec_zakaza'] += number_format($getProduct, 2, '.', '');
//                             // exit;
//                         }

                       
//                     }
//                     // dd($getPositions);
//                 }


//                 $rz[$roz['date']][] = $roz;
//             }
//         }
//     }
// dd($rz);
// exit;
/**получение отгрузки */
$Demand = mysqli_query($db,"SELECT * FROM `demand` WHERE `moment`>=$data_fr and `moment`<=$data_t ORDER BY `demand`.`moment` ASC");
    foreach($Demand as $demand){
        $NameSel =  mysqli_fetch_row(mysqli_query($db,"SELECT name FROM `seller` WHERE  `id_seller` = '{$demand['id_seller']}'"))[0];
        // dd($retail);
        // dd($getNameSel);
        if(!empty($nameSeller)){
            if (in_array($NameSel, $nameSeller)){     // проверка на вхождение в массив имена
                $seller['agent_name'] = mysqli_fetch_row(mysqli_query($db,"SELECT name FROM `agent` WHERE  `id_agent` = '{$demand['id_agent']}'"))[0];
                $seller['name'] = $NameSel;
                $seller['number_order'] = $demand['number_order'];
                $seller['date'] = date("y-m-d",$demand['moment']);                     // получение даты
                $seller['kol_positions'] = $demand['kol_pos'];
                $seller['sum_zakaza'] = $demand['sum_order'];

                $seller['sum_prodavec_zakaza'] = 0;
                if(isset($demand['kol_pos'])){        
                    $pos_d = $demand['href_positions'];                                 // проверка позиций в заказе
                    $getPositions = getPositions($pos_d);                               // $pos=https://online.moysklad.ru/api/remap/1.2/entity/retaildemand/0bc7c93a-abe6-11eb-0a80-084800727f49/positions
                    // dd($getPositions);exit;
                    
                    foreach ($getPositions as $product) {                               // получение продуктов
                        // dd((int)$product->price);
                        dd($product);
                        if ((int)$product->discount == 0) {
                            $discount = $product->discount;
                            $discount = NULL;
                        }else {
                            $discount = $product->discount;
                        }
                       
                        if ($product->assortment->meta->type == 'product'){             //проверка ассортимента на продукт или вариант
                            // dd($product->assortment->meta->href);exit;
                            $getProduct_sum = mysqli_fetch_row(mysqli_query($db,"SELECT sum_prod FROM `products` WHERE  `meta_prod` = '{$product->assortment->meta->href}'"))[0];
                            $discount = $getProduct_sum/100*$discount;
                            $getProduct_totalsum = $getProduct_sum - $discount;
                            // $getProduct = getProduct($product->assortment->meta->href, $discount);
                            // dd($getProduct);
                            $seller['sum_prodavec_zakaza'] += number_format($getProduct_totalsum, 2, '.', '');
                        }elseif( $product->assortment->meta->type == 'variant'){
                            // dd($product);exit;
                            // $getProduct = getProduct($product->assortment->meta->href);
                            $getProduct_sum = mysqli_fetch_row(mysqli_query($db,"SELECT sum_prod FROM `products` WHERE  `meta_prod` = '{$product->assortment->meta->href}'"))[0];
                            $discount = $getProduct_sum/100*$discount;
                            $getProduct_totalsum = $getProduct_sum - $discount;
                            // $getProductVariant = getProductVariant($product->assortment->meta->href);
                            // dd($getProductVariant);
                            // $getProduct = getProduct($getProductVariant, $discount);
                            // dd($getProduct);
                            $seller['sum_prodavec_zakaza'] += number_format($getProduct_totalsum, 2, '.', '');
                            // exit;
                        }

                       
                    }
                    // dd($getPositions);
                }


                $sel[$seller['date']][] = $seller;
            }
        }
    }
dd($sel);