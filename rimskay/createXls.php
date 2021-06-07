<?php
ini_set('display_errors', 'on');
require_once "func.php";
// require_once "getxml.php";

// require_once __DIR__ . '/Classes/PHPExcel.php';
// require_once __DIR__ . '/Classes/PHPExcel/Writer/Excel2007.php';
// require_once __DIR__ . '/Classes/PHPExcel/IOFactory.php';

echo 1;
// dd($prodXml);
echo 1;
exit;
$xls = new PHPExcel();

$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$sheet->setTitle("URL_SHOPIFY");

$sheet->setCellValue("A1", "sku");
$sheet->setCellValue("B1", "title");
$sheet->setCellValue("C1", "stock");

$i=1;
foreach($prodXml as $key=>$value) {
	
	$sheet->setCellValueExplicit("A".++$i, $value['sku'], PHPExcel_Cell_DataType::TYPE_STRING);	
	$sheet->setCellValueExplicit("B".$i, $value['title'], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("C".$i, $value['stock'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
	
}


$objWriter = new PHPExcel_Writer_Excel2007($xls);
$objWriter->save(__DIR__ . '/file.xlsx');
exit();