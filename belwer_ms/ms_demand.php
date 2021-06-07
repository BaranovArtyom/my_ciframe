<?php

// ini_set('display_errors', 'on');
require_once "funcs.php";


/**фио продавцов */
$GetSeller = GetSeller();
// dd($GetSeller);
// exit; 


/**дата для выборки отгрузок*/
// $data_fr = $_POST['data_from'];
$data_fr = '2021-06-02';                                // тестовые даты
$data_from = date($data_fr." 00:00:00");                 //дата с 
$data_from = urlencode($data_from);
// dd($data_from);

// $data_t = $_POST['data_to'];
$data_t = '2021-06-04';
$data_to = date($data_t." 23:59:59");                   //дата до
$data_to = urlencode($data_to);

/**получение размер отгрузок*/
$getSizeDemand = getSizeDemand($data_from, $data_to); 
// dd($getSizeDemand);exit;
$all_demand= array();
$page = 0; $limit = 1000;
$demand_size = getSizeDemand($data_from, $data_to);			    // получаем размер отгрузок
$max_pages = ceil($demand_size / $limit);                       // количество страниц
// dd($max_pages);

/**перебор всех отгрузок за период заданный*/
while ($page < $max_pages) {
	$offset = $page * $limit;
	$all_demand = getDemand($data_from, $data_to,$offset);      // все 
    foreach ($all_demand as $demand) {                          // перебор отгрузок
        // dd($demand);
        // dd($demand->name);
        if (isset($demand->owner->meta->href)) {                // проверка наличие продавца
            // dd($demand->owner->meta->href);
            $getSellerByid = getSellerByid($demand->owner->meta->href); // получение имение продавца по id
            // dd($getSellerByid->name);
            if(!empty($GetSeller)){
                if (in_array($getSellerByid->name, $GetSeller)){     // проверка на вхождение в массив имена
                    $seller['name'] = $getSellerByid->name;
                    $date = explode(' ', $demand->moment);
                    $seller['number_order'] = $demand->name;
                    $seller['date'] = $date[0];                      // получение даты
                    $seller['kol_zakazov'] = 1;
                    $seller['kol_positions'] = (int)$demand->positions->meta->size;
                    $seller['sum_zakaza'] = (int)$demand->sum;
                    
                    $seller['sum_prodavec_zakaza'] = 0;
                    if(isset($demand->positions)){                  // проверка позиций в заказе
                        $getPositions = getPositions($demand->positions->meta->href);

                        foreach ($getPositions as $product) {
                            // dd($product->assortment);
                            // dd($product);exit;
                            // echo $product->assortment->meta->type;
                            // exit;
                            if ($product->assortment->meta->type == 'product'){ //проверка ассортимента на продукт или вариант
                                $getProduct = getProduct($product->assortment->meta->href);
                                // dd($getProduct);
                                $seller['sum_prodavec_zakaza'] += $getProduct;
                            }elseif( $product->assortment->meta->type == 'variant'){
                                // dd($product);exit;
                                // $getProduct = getProduct($product->assortment->meta->href);
                                $getProductVariant = getProductVariant($product->assortment->meta->href);
                                // dd($getProductVariant);
                                $getProduct = getProduct($getProductVariant);
                                // dd($getProduct);
                                $seller['sum_prodavec_zakaza'] += $getProduct;
                                // exit;
                            }

                           
                        }
                        // dd($getPositions);
                    }

                    $sel[$date[0]][$getSellerByid->name][] = $seller;

                }

            }
        
        }else {
            // echo "нет аттрибутов";
        }
    }
    $page++;
}

// dd($sel);
require_once __DIR__.'/Classes/PHPExcel.php';
require_once __DIR__.'/Classes/PHPExcel/Writer/Excel2007.php';
require_once __DIR__.'/Classes/PHPExcel/IOFactory.php';


$xls = new PHPExcel();

if (!empty($sel)){
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle("Продавцы");

        $i=1;
        $sheet->setCellValue("A".$i, "дата");
        $sheet->setCellValue("B".$i, "имя");
        $sheet->setCellValue("C".$i, "кол-во заказов");
        $sheet->setCellValue("D".$i, "кол-во позиций");
        $sheet->setCellValue("E".$i, "сумма заказа");
        $sheet->setCellValue("F".$i, "сумма продавца от заказа");

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
                    $name = $a['name'];
                    $kol_zakaz += $a['kol_zakazov'];
                    $kol_positions += $a['kol_positions'];
                    $sum_zakaza += $a['sum_zakaza'];
                    $sum_prodavec_zakaza += $a['sum_prodavec_zakaza'];
                    
                }
                $sum_zakaza = $sum_zakaza/100;
                // $format_number = number_format($num, 2, ',', '');
                $sheet->setCellValueExplicit("A".++$i, $key, PHPExcel_Cell_DataType::TYPE_STRING);	
                $sheet->setCellValueExplicit("B".$i, $name, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C".$i, $kol_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
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
            
            // $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
            // $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            // $sheet->setCellValueExplicit("C".$i, $itog_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            // $sheet->setCellValueExplicit("D".$i, $itog_position, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            // $sheet->setCellValueExplicit("E".$i, $itog_sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            // $sheet->setCellValueExplicit("F".$i, $itog_sum_prodavec_zakaza, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
            $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);

        }


}


$fecha = date("Y-m-d_h:i:s");
// dd($fecha);exit;

// Отдача на скачивание
header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Disposition: attachment; filename='."Отчет_".$fecha.'.xlsx');

$objWriter = new PHPExcel_Writer_Excel2007($xls);
$objWriter->save('php://output'); 