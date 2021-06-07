<?php
ini_set('display_errors', 'on');
require_once "functions.php";
require_once "Classes/PHPExcel.php";


$inputFileName = "Kasan1.xls";

$phpexcel = new PHPExcel(); //создали обьект класса
$phpexcel = PHPExcel_IOFactory::load($inputFileName);

$phpexcel->setActiveSheetIndex(0); //указываем страницу с которой работаем

$active_sheet = $phpexcel->getActiveSheet(0);

foreach ($phpexcel->getWorksheetIterator() as $worksheet){
	$Title = $worksheet->getTitle(); //имя таблицы
	$lastRow = $worksheet->getHighestRow(); //последняя используемая строка
	$lastCol = $worksheet->getHighestColumn();//последний исп.столбец
	$lastColIndex = PHPExcel_Cell::columnIndexFromString($lastCol);//последний испю индекс столбца
	// dd($lastColIndex);
}
// echo $Title."<br>";
//получения массива из xls файла
for ($row = 1; $row <=$lastRow; ++$row){
	for ($col = 0; $col<$lastColIndex; $col++){
		if($col == 0){
			$exist['title_rus'] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
		}elseif($col == 2){
			$exist['val'] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
		}elseif($col == 3){
			$exist['title_eng'] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
		}elseif($col>=5 and $col<=6){
			$exist['count'] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
		}
	}
	$prod[]= $exist;
}
// dd($prod);
if (!empty($prod)){
	foreach($prod as $val){
		if ($val['count']== 4)
		echo $val['title_rus']."<br>";
	}
}
// конвертирование массива в xml файл
$xml = "<root_items>";
foreach($prod as $r)
	{
	$xml .= "<goods><name>{$r['title_rus']}</name><val>{$r['val']}</val><count>{$r['count']}</count></goods>";
	}
$xml .= "</root_items>";
$sxe = new SimpleXMLElement($xml);
$dom = new DOMDocument('1,0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($sxe->asXML());

// echo $dom->saveXML();
$dom->save('new_items.xml');

$xml1 = simplexml_load_file('new_items.xml'); 
foreach ($xml1->goods as $key){
	$count[] = $key->count;
	$title[] = $key->name;
}
dd($count);
?>



