<?php

ini_set('display_errors', 'on');
require_once "funcs.php";
require_once "ms_demand.php";
require_once __DIR__ . '/Classes/PHPExcel.php';
require_once __DIR__ . '/Classes/PHPExcel/Writer/Excel2007.php';
require_once __DIR__ . '/Classes/PHPExcel/IOFactory.php';

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
                
                $sheet->setCellValueExplicit("A".++$i, $key, PHPExcel_Cell_DataType::TYPE_STRING);	
                $sheet->setCellValueExplicit("B".$i, $name, PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C".$i, $kol_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("D".$i, $kol_positions, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("E".$i, $sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                
                $itog_zakaz += $kol_zakaz;
                $itog_position += $kol_positions; 
                $itog_sum += $sum;        
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
            
            $sheet->setCellValueExplicit("A".++$i, $key, PHPExcel_Cell_DataType::TYPE_STRING);	
            $sheet->setCellValueExplicit("B".$i, $name, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C".$i, $kol_zakaz, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("D".$i, $kol_positions, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("E".$i, $sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            
            $itog_zakaz += $kol_zakaz;
            $itog_position += $kol_positions; 
            $itog_sum += $sum;        
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
        


// Отдача на скачивание
header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=file.xlsx");

$objWriter = new PHPExcel_Writer_Excel2007($xls);
$objWriter->save('php://output'); 

// $objWriter = new PHPExcel_Writer_Excel5($xls);
// $objWriter->save(__DIR__ . '/file1.xlsx');