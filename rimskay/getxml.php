<?php
require_once "func.php";
require_once __DIR__ . '/Classes/PHPExcel.php';
require_once __DIR__ . '/Classes/PHPExcel/Writer/Excel2007.php';
require_once __DIR__ . '/Classes/PHPExcel/IOFactory.php';
/**для вывода и сохранения ошибок */
ini_set('display_errors', 1);
ini_set('error_log', 'logger.log');
error_reporting(E_ALL);

$s = date("Y-m-d_H:i:s");


$getProductId = getProductId(); // получение id всех продуктов
// dd($getProductId);
$prodXml = array();
foreach ($getProductId  as $idProduct) {
    // dd($idProduct['id']);
    // $idProduct['id'] = "02d7e091-6c9e-11eb-0a80-0361000106c6";
    // получение остатков по id товара
    $getStockProduct = getStockProduct($idProduct['id']);
    // dd($getStockProduct);
    $pr = array();
    if ($getStockProduct[0]['main']>0) {
        // echo "остаток больше нуля";
        //получение товара по id для создания xml по ним
        $id = $getStockProduct[0]['id'];
        $pr = getProductData($id);
        $pr['stock'] = $getStockProduct[0]['main'];   // остаток в складе
        // dd($pr);
        $prodXml[] = $pr;
        // dd($prodXml);
    }
// exit;
}



$xls = new PHPExcel();

$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$sheet->setTitle("stock");

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
$objWriter->save(__DIR__ . '/'.$s.'.xlsx');
// dd($prodXml);