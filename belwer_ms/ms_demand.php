<?php
set_time_limit(600);
// ini_set('display_errors', 'on');
require_once "funcs.php";
require_once "config.php";

/**фио продавцов */
// $GetSeller = GetSeller();
// dd($GetSeller);
// exit; 

/**дата для выборки отгрузок*/
$data_fr = $_POST['data_from'];

// $data_fr = '2021-05-01';                                // тестовые даты
$data_from = date($data_fr." 00:00:00");                 //дата с 
$data_from = urlencode($data_from);
// dd($data_from);

$data_t = $_POST['data_to'];
// $data_t = '2021-05-30';
$data_to = date($data_t." 23:59:59");                   //дата до
$data_to = urlencode($data_to);

/**массив выбранных продавцов */
if (!empty($_POST['sellerList'])){
    $nameSeller = $_POST['sellerList']; // Аркадий 'Богдан'
}else {
    $nameSeller = [];
}
// $nameSeller = ['Морозов А. А.']; // для теста

$data_fr = strtotime($_POST['data_from']);
$data_t = strtotime($_POST['data_to']);

// /**получение продаж розницы */
$getRetailDemand = mysqli_query($db,"SELECT * FROM `retail_demand` WHERE `moment`>=$data_fr and `moment`<=$data_t ORDER BY `retail_demand`.`moment` ASC");
    foreach($getRetailDemand as $retail){
        $getNameSel =  mysqli_fetch_row(mysqli_query($db,"SELECT name FROM `seller` WHERE  `id_seller` = '{$retail['id_seller']}'"))[0];
        // dd($retail);
        // dd($getNameSel);
        if(!empty($nameSeller)){
            if (in_array($getNameSel, $nameSeller)){     // проверка на вхождение в массив имена
                $roz['agent_name'] = mysqli_fetch_row(mysqli_query($db,"SELECT name FROM `agent` WHERE  `id_agent` = '{$retail['id_agent']}'"))[0];
                $roz['name'] = $getNameSel;
                $roz['number_order'] = $retail['number_order'];
                $roz['date'] = date("y-m-d",$retail['moment']);                     // получение даты
                $roz['kol_positions'] = $retail['kol_pos'];
                $roz['sum_zakaza'] = $retail['sum_order'];

                $roz['sum_prodavec_zakaza'] = 0;
                    if(isset($retail['kol_pos'])){        
                        $pos = $retail['href_positions'];                                 // проверка позиций в заказе
                        $getPositions = getPositions($pos);
                        
                        foreach ($getPositions as $product) {
                            
                            if ((int)$product->discount == 0) {
                                $discount = $product->discount;
                                $discount = NULL;
                            }else {
                                $discount = $product->discount;
                            }
                        
                            if ($product->assortment->meta->type == 'product'){             //проверка ассортимента на продукт или вариант
                                // dd($product->assortment->meta->href);
                                $getProduct_sum = mysqli_fetch_row(mysqli_query($db,"SELECT sum_prod FROM `products` WHERE  `meta_prod` = '{$product->assortment->meta->href}'"))[0];
                                // dd($getProduct_sum);
                                // echo 'sum'.$getProduct_sum.' '.$roz['number_order'].'<br>';
                                $discount = $getProduct_sum/100*$discount;
                                $getProduct_totalsum = $getProduct_sum - $discount;
                                // $getProduct = getProduct($product->assortment->meta->href, $discount);
                                // dd($getProduct_totalsum);
                                $roz['sum_prodavec_zakaza'] += number_format($getProduct_totalsum, 2, '.', '');
                                // $roz['sum_prodavec_zakaza'] += $getProduct_totalsum;
                                // echo 'sum_prodavec_zakaza'.$roz['sum_prodavec_zakaza'].'<br>';
                                // dd($roz['sum_prodavec_zakaza']);

                            }elseif( $product->assortment->meta->type == 'variant'){
                                // dd($product);exit;
                                // dd($product->assortment->meta->href);
                                // $getProduct = getProduct($product->assortment->meta->href);
                                $getHref = mysqli_fetch_row(mysqli_query($db,"SELECT href_product FROM `products` WHERE  `meta_prod` = '{$product->assortment->meta->href}'"))[0];
                                $getProduct_sum = mysqli_fetch_row(mysqli_query($db,"SELECT sum_prod FROM `products` WHERE  `meta_prod` = '{$getHref}'"))[0];
                                // echo 'sum'.$getProduct_sum.' '.$roz['number_order'].'<br>';
                                $discount = $getProduct_sum/100*$discount;
                                $getProduct_totalsum = $getProduct_sum - $discount;
                                // $getProductVariant = getProductVariant($product->assortment->meta->href);
                                // dd($getProductVariant);
                                // $getProduct = getProduct($getProductVariant, $discount);
                                // dd($getProduct);
                                $roz['sum_prodavec_zakaza'] += number_format($getProduct_totalsum, 2, '.', '');
                                // $roz['sum_prodavec_zakaza'] += $getProduct_totalsum;
                                // echo 'sum_prodavec_zakaza'.$roz['sum_prodavec_zakaza'];

                                // exit;
                            }

                        
                        }
                        // dd($getPositions);
                    }

                $rz[$roz['date']][] = $roz;
            }
        }
    }
// dd($rz);exit;
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
                        // dd($product);exit;
                        if ((int)$product->discount == 0) {
                            $discount = $product->discount;
                            $discount = NULL;
                        }else {
                            $discount = $product->discount;
                        }
                    
                        if ($product->assortment->meta->type == 'product'){             //проверка ассортимента на продукт или вариант
                            // dd($product->assortment->meta->href);exit;
                            $getProduct_sum = mysqli_fetch_row(mysqli_query($db,"SELECT sum_prod FROM `products` WHERE  `meta_prod` = '{$product->assortment->meta->href}'"))[0];
                            $discount = (int)$getProduct_sum/100*$discount;
                            $getProduct_totalsum =(int)$getProduct_sum - $discount;
                            // $getProduct = getProduct($product->assortment->meta->href, $discount);
                            // dd($getProduct);
                            $seller['sum_prodavec_zakaza'] += number_format($getProduct_totalsum, 2, '.', '');
                        }elseif( $product->assortment->meta->type == 'variant'){
                            // dd($product);exit;
                            // $getProduct = getProduct($product->assortment->meta->href);
                            $getHref = mysqli_fetch_row(mysqli_query($db,"SELECT href_product FROM `products` WHERE  `meta_prod` = '{$product->assortment->meta->href}'"))[0];
                            $getProduct_sum = mysqli_fetch_row(mysqli_query($db,"SELECT sum_prod FROM `products` WHERE  `meta_prod` = '{$getHref}'"))[0];

                            // $getProduct_sum = mysqli_fetch_row(mysqli_query($db,"SELECT sum_prod FROM `products` WHERE  `meta_prod` = '{$product->assortment->meta->href}'"))[0];
                            $discount = (int)$getProduct_sum/100*$discount;
                            $getProduct_totalsum = (int)$getProduct_sum - $discount;
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
            $sel[$seller['date']][$NameSel][] = $seller;
        }
    }
}
// dd($sel);
// dd($rz);
// exit;
require_once __DIR__.'/Classes/PHPExcel.php';
require_once __DIR__.'/Classes/PHPExcel/Writer/Excel2007.php';
require_once __DIR__.'/Classes/PHPExcel/IOFactory.php';

$xls = new PHPExcel();

    if (!empty($sel)){
            $xls->setActiveSheetIndex(0);
            $sheet = $xls->getActiveSheet();
            $sheet->setTitle("Отгрузки");

            $i=1;
            $sheet->setCellValue("A".$i, "Имя клиента");
            $sheet->setCellValue("B".$i, "Номер отгрузки");
            $sheet->setCellValue("C".$i, "Дата документа");
            $sheet->setCellValue("D".$i, "кол-во позиций");
            $sheet->setCellValue("E".$i, "сумма заказа");
            $sheet->setCellValue("F".$i, "Процент от продаж");

            $sheet->getColumnDimension("A")->setAutoSize(true);
            $sheet->getColumnDimension("B")->setAutoSize(true);
            $sheet->getColumnDimension("C")->setAutoSize(true);
            $sheet->getColumnDimension("D")->setAutoSize(true);
            $sheet->getColumnDimension("E")->setAutoSize(true);
            $sheet->getColumnDimension("F")->setAutoSize(true);

            $sheet->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            foreach($sel as $key=>$sels) {
                // dd($sels);
            
                $itog_sum=$itog_position=$itog_zakaz=$itog_sum_prodavec_zakaza = 0;
                foreach ($sels as $s) {
                    // dd($s[0]['date']);exit;
                    
                    $kol_zakaz = $kol_positions = $sum_zakaza = $sum_prodavec_zakaza = 0;
                    foreach ($s as $a) {
                        // dd($a);exit;
                        $agent_name = $a['agent_name'];
                        // dd($agent_name);exit;
                        $name = $a['name'];
                        $number = $a['number_order'];
                        $data = $a['date'];
                        $kol_zakaz = $a['kol_zakazov'];
                        $kol_positions = $a['kol_positions'];
                        $sum_zakaza = $a['sum_zakaza'];
                        $sum_prodavec_zakaza = $a['sum_prodavec_zakaza'];

                        $sum_zakaza = $sum_zakaza/100;
                        $sheet->setCellValueExplicit("A".++$i, $agent_name, PHPExcel_Cell_DataType::TYPE_STRING);	
                        $sheet->setCellValueExplicit("B".$i, $number, PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("C".$i, $data, PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("D".$i, $kol_positions, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("E".$i, $sum_zakaza, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("F".$i, $sum_prodavec_zakaza, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        
                        $itog_zakaz += $kol_zakaz;
                        $itog_position += $kol_positions; 
                        $itog_sum += $sum_zakaza;      
                        $itog_sum_prodavec_zakaza += $sum_prodavec_zakaza;   

                        $border = array(
                            'borders'=>array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('rgb' => '000000')
                                )
                            )
                        );
                        
                        $sheet->getStyle("A".$i.":F".$i)->applyFromArray($border);
                        
                    }
                    $total_itog_zakaz += $itog_zakaz;
                    $total_itog_position += $itog_position; 
                    $total_itog_sum += $itog_sum;      
                    $total_itog_sum_prodavec_zakaza += $itog_sum_prodavec_zakaza;     
                    
                    $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
                    $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("C".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("D".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("E".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("F".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
                $sheet->setCellValueExplicit("A".++$i, $name, PHPExcel_Cell_DataType::TYPE_STRING);	
                $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("D".$i, $total_itog_position, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("E".$i, $total_itog_sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("F".$i, $total_itog_sum_prodavec_zakaza, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                $bg = array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '01B050')
                    )
                );
                $sheet->getStyle("A".$i.":F".$i)->applyFromArray($bg);

                $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
                $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("D".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("E".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("F".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
    }

    if (!empty($rz)){
        $xls->createSheet();
        $xls->setActiveSheetIndex(1);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle("Розница");

        $m=1;
        $sheet->setCellValue("A".$m, "Имя клиента");
        $sheet->setCellValue("B".$m, "Номер продажи");
        $sheet->setCellValue("C".$m, "Дата документа");
        $sheet->setCellValue("D".$m, "кол-во позиций");
        $sheet->setCellValue("E".$m, "сумма заказа");
        $sheet->setCellValue("F".$m, "Процент от продаж");
        // $sheet->setCellValue("G".$i, "номер заказа");

        $sheet->getColumnDimension("A")->setAutoSize(true);
        $sheet->getColumnDimension("B")->setAutoSize(true);
        $sheet->getColumnDimension("C")->setAutoSize(true);
        $sheet->getColumnDimension("D")->setAutoSize(true);
        $sheet->getColumnDimension("E")->setAutoSize(true);
        $sheet->getColumnDimension("F")->setAutoSize(true);
        // $sheet->getColumnDimension("G")->setAutoSize(true);

        $sheet->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle("G1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        foreach($rz as $key=>$rs) {
            // dd($rs);exit;
        
            // $itog_sum_roz=$itog_position_roz=$itog_zakaz_roz=$itog_sum_prodavec_zakaza_roz = 0;
            // foreach ($rs as $r) {
                // dd($r);exit;
                
                $kol_zakaz_roz = $kol_positions_roz = $sum_zakaza_roz = $sum_prodavec_zakaza_roz = 0;
                foreach ($rs as $b) {
                    // dd($b);exit;
                    $agent_name_roz = $b['agent_name'];
                    // dd($agent_name);exit;
                    $name_roz = $b['name'];
                    $number_roz = $b['number_order'];
                    $data_roz = $b['date'];
                    $kol_zakaz_roz = $b['kol_zakazov'];
                    $kol_positions_roz = $b['kol_positions'];
                    $sum_zakaza_roz = $b['sum_zakaza'];
                    $sum_prodavec_zakaza_roz = $b['sum_prodavec_zakaza'];

                    $sum_zakaza_roz = $sum_zakaza_roz/100;
                    // $format_number = number_format($num, 2, ',', '');
                    $sheet->setCellValueExplicit("A".++$m, $agent_name_roz, PHPExcel_Cell_DataType::TYPE_STRING);	
                    $sheet->setCellValueExplicit("B".$m, $number_roz, PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("C".$m, $data_roz, PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit("D".$m, $kol_positions_roz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("E".$m, $sum_zakaza_roz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("F".$m, $sum_prodavec_zakaza_roz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    // $sheet->setCellValueExplicit("G".$i, $sum_prodavec_zakaza, PHPExcel_Cell_DataType::TYPE_STRING);
                    $itog_zakaz_roz += $kol_zakaz_roz;
                    $itog_position_roz += $kol_positions_roz; 
                    $itog_sum_roz += $sum_zakaza_roz;      
                    $itog_sum_prodavec_zakaza_roz += $sum_prodavec_zakaza_roz;   

                    $border = array(
                        'borders'=>array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000')
                            )
                        )
                    );
                    
                    $sheet->getStyle("A".$m.":F".$m)->applyFromArray($border);
                    
                }
                
                $total_itog_zakaz_roz += $itog_zakaz_roz;
                $total_itog_position_roz += $itog_position_roz; 
                $total_itog_sum_roz += $itog_sum_roz;      
                $total_itog_sum_prodavec_zakaza_roz += $itog_sum_prodavec_zakaza_roz;     
                
            // }

            $sheet->setCellValueExplicit("A".++$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
            $sheet->setCellValueExplicit("B".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);

        }
            $sheet->setCellValueExplicit("A".++$m, $name_roz, PHPExcel_Cell_DataType::TYPE_STRING);	
            $sheet->setCellValueExplicit("B".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D".$m, $total_itog_position_roz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("E".$m, $total_itog_sum_roz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("F".$m, $total_itog_sum_prodavec_zakaza_roz, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $bg = array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '01B050')
                )
            );
            $sheet->getStyle("A".$m.":F".$m)->applyFromArray($bg);

            $sheet->setCellValueExplicit("A".++$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
            $sheet->setCellValueExplicit("B".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F".$m, " ", PHPExcel_Cell_DataType::TYPE_STRING);
    }


$fecha = date("Y-m-d_h:i:s");

// Отдача на скачивание
header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Disposition: attachment; filename='."Отчет_".$fecha.'.xlsx');

$objWriter = new PHPExcel_Writer_Excel2007($xls);
$objWriter->save('php://output'); 