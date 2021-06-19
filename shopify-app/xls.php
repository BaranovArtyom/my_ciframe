<?php
ini_set('display_errors', 'on');
require_once "functions.php";
include_once "config.php";

require_once __DIR__ . '/Classes/PHPExcel.php';
require_once __DIR__ . '/Classes/PHPExcel/Writer/Excel2007.php';
require_once __DIR__ . '/Classes/PHPExcel/IOFactory.php';

// dd($_POST['url']);die();

$url_collections = $_POST['url'].'/collections.json';        // формирование адреса колекций сайта
// $url_collections = URL_SHOPIFY.'/collections.json';        // формирование адреса колекций сайта

$dataCollections = getCollections($url_collections);       // данные колекций сайта
// dd($dataCollections);die();
$urlNameCollection = '';                                   // формирование адреса для получения продуктов коллекций
$data_colections = array();

foreach($dataCollections['name'] as $nameCollection) {
  $urlNameCollection = $_POST['url'].'/collections/'.$nameCollection.'/products.json';
  // dd($urlNameCollection);
  // echo "---".$nameCollection."----";
  // $urlNameCollection = URL_SHOPIFY.'/collections/'."all-products-1".'/products.json';
  $data_colections[] = getProductsCollection($urlNameCollection);
}
// dd($data_colections);die();

// if ( FORMAT == 'xlsx' )	{
	
$xls = new PHPExcel();

$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$sheet->setTitle("URL_SHOPIFY");

$sheet->setCellValue("A1", "colection_id");
$sheet->setCellValue("B1", "colection_handle");
$sheet->setCellValue("C1", "colection_title");
$sheet->setCellValue("D1", "colection_vendor");
$sheet->setCellValue("E1", "colection_product_type");
$sheet->setCellValue("F1", "body_html");
$sheet->setCellValue("G1", "colection_tags");
$sheet->setCellValue("H1", "product_id");
$sheet->setCellValue("I1", "product_title");
$sheet->setCellValue("J1", "product_sku");
$sheet->setCellValue("K1", "product_price");
$sheet->setCellValue("L1", "images");

// dd($data_colections);die();

foreach($data_colections as $key=>$collect) {
	// dd(count($collect));die();
	
	foreach ($collect as $k => $item) {
	// echo $k."<br>";	
		// dd($k);die();
		$prod['images'] = '';
		foreach($item['images'] as $image) {
			// dd($image);die();
			$prod['images'].= '<p>'."$image".'<p>';
		}
		$body_html = '"'.strip_tags($item['colection_body_html']).'"';
	$sheet->setCellValueExplicit("A".++$i, $item['colection_id'], PHPExcel_Cell_DataType::TYPE_NUMERIC);	
	$sheet->setCellValueExplicit("B".$i, $item['colection_handle'], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("C".$i, $item['colection_title'], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("D".$i, $item['colection_vendor'], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("E".$i, $item['colection_product_type'], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("F".$i, $body_html, PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("G".$i, $item['colection_tags'], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("H".$i, $item['product_id'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
	$sheet->setCellValueExplicit("I".$i, $item['product_title'], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("J".$i, $item['product_sku'], PHPExcel_Cell_DataType::TYPE_STRING);
	$sheet->setCellValueExplicit("K".$i, $item['product_price'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
	$sheet->setCellValueExplicit("L".$i, $prod['images'], PHPExcel_Cell_DataType::TYPE_STRING);
	// dd($item['colection_id']);
	// echo $i;
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
exit();
		
// }

