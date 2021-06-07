<?php

ini_set('display_errors', 'on');
require_once "funcs.php";

$data_fr = $_POST['data_from'];
// $data_fr = '2021-06-02';
$data_from = date($data_fr." 00:00:00");                 //дата с 
$data_from = urlencode($data_from);
// dd($data_from);
$data_t = $_POST['data_to'];
// $data_t = '2021-06-02';
$data_to = date($data_t." 23:59:59");                   //дата до
$data_to = urlencode($data_to);
// dd($data_to);

// dd($_POST['sborList']);
// dd($_POST['controlList']);
// exit;

/**массив выбранных cборщиков */
if (!empty($_POST['sborList'])){
    $nameZborchikov = $_POST['sborList']; // Аркадий 'Богдан'
}else {
    $nameZborchikov = [];
}


// $nameZborchikov = ['Смолкин Александр'];

/**массив выбранных контоллеров*/
if (!empty($_POST['controlList'])){
    $nameControler = $_POST['controlList']; // Аркадий 'Богдан'
}else {
    $nameControler = [];
}


// $nameControler = ['Смолкин Саша'];

/**получение размер отгрузок*/
$getSizeDemand=getSizeDemand($data_from, $data_to); 
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
        // dd($demand);exit;
        if (isset($demand->attributes)) {                       // проверка есть ли аттрибуты Контроллер заказа или Сборщик заказов
            foreach($demand->attributes as $atrribute){
                // dd($atrribute);exit;
                if ($atrribute->name == 'сборщик заказа') {
                    if(!empty($nameZborchikov)){
                        if (in_array($atrribute->value->name, $nameZborchikov)){
                            // $sborchik = array();
                            $sborchik['name'] = $atrribute->value->name;
                            $date = explode(' ', $demand->moment);
                            $sborchik['date'] = $date[0];               // получение даты
                            $sborchik['kol_zakazov'] = 1;
                            $sborchik['kol_positions'] = (int)$demand->positions->meta->size;
                            $sborchik['sum'] = (int)$demand->sum;
                            $sb[$date[0]][$atrribute->value->name][] = $sborchik;
                        }

                    }
                   
                    // else{
                    //     $sborchik['name'] = $atrribute->value->name;
                    //     $date = explode(' ', $demand->moment);
                    //     $sborchik['date'] = $date[0];               // получение даты
                    //     $sborchik['kol_zakazov'] = 1;
                    //     $sborchik['kol_positions'] = (int)$demand->positions->meta->size;
                    //     $sborchik['sum'] = (int)$demand->sum;
                    //     $sb[$date[0]][$atrribute->value->name][] = $sborchik;
                    // }
                    
                }
                if ($atrribute->name == 'контролёр заказа') {
                    if(!empty($nameControler)){
                        if (in_array($atrribute->value->name, $nameControler)){
                            // $sborchik = array();
                            $controler['name'] = $atrribute->value->name;
                            $date = explode(' ', $demand->moment);
                            $controler['date'] = $date[0];               // получение даты
                            $controler['kol_zakazov'] = 1;
                            $controler['kol_positions'] = (int)$demand->positions->meta->size;
                            $controler['sum'] = (int)$demand->sum;
                            $cont[$date[0]][$atrribute->value->name][] = $controler;
                        }
                    }
                  
                }
            }
        }else {
            echo "нет аттрибутов";
        }
    }
    $page++;
}
// dd($sb);exit;
require_once __DIR__.'/Classes/PHPExcel.php';
require_once __DIR__.'/Classes/PHPExcel/Writer/Excel2007.php';
require_once __DIR__.'/Classes/PHPExcel/IOFactory.php';

// /**массив выбранных зборщиков */
// $nameZborchikov = ['Аркадий']; // Аркадий 'Богдан'

// if (!empty($sb))
$xls = new PHPExcel();

if (!empty($sb)){
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle("Сборщик");

        $i=1;
        $sheet->setCellValue("A".$i, "дата");
        $sheet->setCellValue("B".$i, "имя");
        $sheet->setCellValue("C".$i, "кол-во заказов");
        $sheet->setCellValue("D".$i, "кол-во позиций");
        $sheet->setCellValue("E".$i, "сумма");

        $sheet->getColumnDimension("A")->setAutoSize(true);
        $sheet->getColumnDimension("B")->setAutoSize(true);
        $sheet->getColumnDimension("C")->setAutoSize(true);
        $sheet->getColumnDimension("D")->setAutoSize(true);

        $sheet->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        foreach($sb as $key=>$sborch) {
            // dd($sborch);exit;
        
            $itog_sum=$itog_position=$itog_zakaz=0;
            foreach ($sborch as $s) {
                // dd($s[0]['date']);exit;
                
                $kol_zakaz=$kol_positions=$sum= 0;
                foreach ($s as $a) {
                    $name = $a['name'];
                    $kol_zakaz += $a['kol_zakazov'];
                    $kol_positions += $a['kol_positions'];
                    $sum += $a['sum'];
                    
                }
                $sum = $sum/100;
                // $format_number = number_format($num, 2, ',', '');
                $sheet->setCellValueExplicit("A".++$i, $key, PHPExcel_Cell_DataType::TYPE_STRING);	
                $sheet->setCellValueExplicit("B".$i, $name, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C".$i, $kol_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("D".$i, $kol_positions, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("E".$i, $sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                
                $itog_zakaz += $kol_zakaz;
                $itog_position += $kol_positions; 
                $itog_sum += $sum;        

                $border = array(
                    'borders'=>array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('rgb' => '000000')
                        )
                    )
                );
                 
                $sheet->getStyle("A".$i.":E".$i)->applyFromArray($border);
            }
            
            $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
            $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C".$i, $itog_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("D".$i, $itog_position, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("E".$i, $itog_sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
            $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);

        }


}
if (!empty($cont)){
    $xls->createSheet();
    $xls->setActiveSheetIndex(1);
    $sheet = $xls->getActiveSheet();
    $sheet->setTitle("Котроллер");

    $i=1;
    $sheet->setCellValue("A".$i, "дата");
    $sheet->setCellValue("B".$i, "имя");
    $sheet->setCellValue("C".$i, "кол-во заказов");
    $sheet->setCellValue("D".$i, "кол-во позиций");
    $sheet->setCellValue("E".$i, "сумма");

    $sheet->getColumnDimension("A")->setAutoSize(true);
    $sheet->getColumnDimension("B")->setAutoSize(true);
    $sheet->getColumnDimension("C")->setAutoSize(true);
    $sheet->getColumnDimension("D")->setAutoSize(true);

    $sheet->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("B1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("C1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("D1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("E1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $itog_total_sum = $itog_total_zakaz = 0;
    foreach($cont as $key=>$cn) {
        // dd($sborch);exit;
    
        $itog_sum=$itog_position=$itog_zakaz=0;
        foreach ($cn as $c) {
            // dd($s[0]['date']);exit;
            
            $kol_zakaz=$kol_positions=$sum= 0;
            foreach ($c as $b) {
                $name = $b['name'];
                $kol_zakaz += $b['kol_zakazov'];
                $kol_positions += $b['kol_positions'];
                $sum += $b['sum'];
                
            }
            $sum = $sum/100;
            $sheet->setCellValueExplicit("A".++$i, $key, PHPExcel_Cell_DataType::TYPE_STRING);	
            $sheet->setCellValueExplicit("B".$i, $name, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C".$i, $kol_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("D".$i, $kol_positions, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("E".$i, $sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            
            $itog_zakaz += $kol_zakaz;
            $itog_position += $kol_positions; 
            $itog_sum += $sum;        

            $border = array(
                'borders'=>array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000')
                    )
                )
            );
             
            $sheet->getStyle("A".$i.":E".$i)->applyFromArray($border);
        }
        $itog_total_sum += $itog_sum;
        $itog_total_zakaz += $kol_zakaz;
        
        // $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
        // $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        // $sheet->setCellValueExplicit("C".$i, $itog_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        // $sheet->setCellValueExplicit("D".$i, $itog_position, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        // $sheet->setCellValueExplicit("E".$i, $itog_sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
        $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("C".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("E".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);

    }

        $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
        $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("C".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("E".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);

        $sheet->setCellValueExplicit("A".++$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);	
        $sheet->setCellValueExplicit("B".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("C".$i, $itog_total_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit("D".$i, " ", PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("E".$i, $itog_total_sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);




}
// dd($sb);exit;

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

// dd($sb);
// dd($cont);