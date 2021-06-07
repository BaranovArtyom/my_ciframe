<?php
// ini_set('display_errors', 'on');
require_once "functions.php";
require_once "Classes/PHPExcel.php";


$inputFileName = "New20201017111928_admin@info7290_1142007626.xlsx";

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
		if($col == 4){
			$exist['gname'] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
		}elseif($col == 6){
			$exist['article'] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
		}elseif($col == 34){
			$exist['count'] = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
		}
	}
	$prod[]= $exist;
}
// dd($prod);

$ms_assort = myCurl('https://online.moysklad.ru/api/remap/1.2/entity/assortment');//получение json с остатками из мойсклад
//  dd($ms_assort);
// echo '<form action="#" method="post">
//         <p>Товары из ERC</p>
//         <select name="artnumberList">';
		
foreach ($prod as $item) {        // перебор эл-ов из файла скаченного с яндекс диска

    
    // echo "<option value='{$item['article']}'> {$item['gname']} </option>"; // вывод в селекте имена эл-ов
   
    foreach ($ms_assort->rows as $assort) {    // перебор эл-ов ассортимента из мойсклад
    
        if ($item['article'] == $assort->article) {     // если артикулы совпали сохраняем данные из файла
            $exist1['name'] = $item['gname'];
            if (empty($item['article'])){
                $exist1['article'] = 1;
            }
            $exist1['article'] = $item['article'];
            $exist1['balance'] = $item['count'];
            $prod1[] = $exist1;
           
        }
    }
}
// dd($prod1);
// echo '</select>
//         <button type="submit" value="Submit">сравнить</button>
//     </form>
//     <div>';
    if(!empty($prod1)){
        
        foreach ($ms_assort->rows as $assort) {
            // if ($_POST['artnumberList'] == $assort->article){
            //     echo "Выбранный товар из мойсклад {$assort->name} остаток = {$assort->stock}.<br> ";
                // dd($prod1);
            // dd($assort);
            foreach ($prod1 as $pr) {
                        // dd($pr);
                // if ($assort->stock == $assort->article){
                //     echo "Выбранный товар из мойсклад {$assort->name} остаток = {$assort->stock}.<br> ";
                if ($pr['article'] == $assort->article and $pr['balance'] < $assort->stock and !empty($pr['balance'])){
                    echo "надо списать";
                    $kol_spisania = $assort->stock-$pr['balance'];
                    // echo " {$kol_spisania}";
                    // echo "в мс {$assort->name} его остаток = {$assort->stock }"."<br>";
                    // echo "в файле {$pr['name']} его остаток = {$pr['balance']}"."<br>";

                    //функция списания 
                     $filter['name'] = 'name';
                     $filter['value'] = $pr['name'];
                     $filtr = "?filter = {$filter['name']} = {$filter['value']}";
                     $getIdItem = myCurl("https://online.moysklad.ru/api/remap/1.2/entity/product",$method='GET',$filtr);
                    //  dd($getIdItem);
                         foreach($getIdItem->rows as $idItem){
                             if ($idItem->name == $assort->name){
                                 $id = $idItem->id; //id выбранного товара
                                 $product = $idItem->meta;
                                //  dd($product);
                             }
                             
                         }
                    $getStoreItem = myCurl("https://online.moysklad.ru/api/remap/1.1/entity/store",$method='GET');
                    //  dd($getStoreItem);
                         foreach ($getStoreItem->rows as $store){
                             $store = $store->meta;
                         }
                     $getOrgItem = myCurl("https://online.moysklad.ru/api/remap/1.2/entity/organization",$method='GET');
                         foreach ($getOrgItem->rows as $organize){
                             $organize = $organize->meta;
                             // dd($organize);
                         }
                     
                     $positions = [];
                     $positions["quantity"]["quantity"] = $kol_spisania;
                     $positions["quantity"]["assortment"]["meta"] = $product;
                     $positions = json_encode(array_values($positions));
                    //  dd($positions);
                   

                     $body = [];
                     $body["store"]["meta"] = $store;
                     $body["organization"]["meta"] = $organize;
                     $body["positions"] = json_decode($positions);
                   
                     
                    //  dd($body);

                    $ms_spisanie = myCurlPost('https://online.moysklad.ru/api/remap/1.2/entity/loss',$method='POST',$body);

                }elseif($pr['article'] == $assort->article and $pr['balance'] > $assort->stock and !empty($pr['balance'])){
                    echo "надо оприходывать";
                    $kol_oprihod = $pr['balance']-$assort->stock;
                    // echo " {$kol_oprihod}";
                    // echo "<p>остатки  из файла {$pr['name']} - {$pr['article']} - остаток = {$pr['balance']}</p>";
                    // echo "в мс {$assort->name} его остаток = {$assort->stock }"."<br>";
                    // echo "в файле {$pr['name']} его остаток = {$pr['balance']}"."<br>";

                    //функция оприходования

                    $filter['name'] = 'name';
                    $filter['value'] = $assort->name;
                    $filtr = "?filter = {$filter['name']} = {$filter['value']}";
                    $getIdItem = myCurl("https://online.moysklad.ru/api/remap/1.2/entity/product",$method='GET',$filtr);
                    // dd($getIdItem);
                        foreach($getIdItem->rows as $idItem){
                            if ($idItem->name == $assort->name){
                                $id = $idItem->id; //id выбранного товара
                                $product = $idItem->meta;
                                // dd($product);
                            }
                            
                        }
                    $getStoreItem = myCurl("https://online.moysklad.ru/api/remap/1.1/entity/store",$method='GET');
                    // dd($getStoreItem);
                        foreach ($getStoreItem->rows as $store){
                            $store = $store->meta;
                        }
                    $getOrgItem = myCurl("https://online.moysklad.ru/api/remap/1.2/entity/organization",$method='GET');
                        foreach ($getOrgItem->rows as $organize){
                            $organize = $organize->meta;
                            // dd($organize);
                        }
                    
                    $positions = [];
                    $positions["quantity"]["quantity"] = $kol_oprihod;
                    $positions["quantity"]["assortment"]["meta"] = $product;
                    $positions = json_encode(array_values($positions));
                    // dd($positions);
                  

                    $body = [];
                    $body["store"]["meta"] = $store;
                    $body["organization"]["meta"] = $organize;
                    $body["positions"] = json_decode($positions);
                   
                    
                    // dd($body);

                    $ms_oprihod = myCurlPost('https://online.moysklad.ru/api/remap/1.2/entity/enter','POST',$body);





                }
               
            
            }
           
    }}

// echo '</div>';


// 			if (empty($item['count'])){
// 				$item['count'] == 0;
// 				$exist1['balance'] = (string)$item['count'];
// 			}else{
// 				$exist1['balance'] = (string)$item['count'];
// 			}
//             $prod1[] = $exist1;
           
//         }
//     }
// }
// echo '</select>
//         <button type="submit" value="Submit">сравнить</button>
//     </form>
// 	<div>';
	
	// if(!empty($prod1) and !empty($_POST['artnumberList'])){
        // if(!empty($prod1)){
		// dd($_POST['artnumberList']);
        // foreach ($ms_assort->rows as $assort) {
        //  dd($assort->article);
            // if ($_POST['artnumberList'] == $assort->article){
            //     echo "Выбранный товар из мойсклад {$assort->name} остаток = {$assort->stock}.<br> ";
             
                // foreach ($prod1 as $pr) {
					// dd($pr);
            //     if ($item['article'] == $assort->article and $item['count']< $assort->stock and !empty($item['count'])){
			// 		// $pr['balance'];
            //             echo "надо списать";
            //             $kol_spisania = $assort->stock-$item['count'];
            //             echo " {$kol_spisania}";
            //             echo "<p>остатки  из файла {$item['gname']} - {$item['article']} - остаток = {$item['count']}</p>";
                
            //             //функция списания 
            //             $filter['name'] = 'name';
            //             $filter['value'] = $assort->name;
            //             $filtr = "?filter = {$filter['name']} = {$filter['value']}";
            //             // dd($filtr);
            //             $getIdItem = myCurl("https://online.moysklad.ru/api/remap/1.2/entity/product",$method='GET',$filtr);
            //             // dd($getIdItem);
            //                 foreach($getIdItem->rows as $idItem){
            //                     if ($idItem->name == $assort->name){
            //                         $id = $idItem->id; //id выбранного товара
            //                         $product = $idItem->meta;
            //                         // dd($product);
            //                     }
                                
            //                 }
            //             $getStoreItem = myCurl("https://online.moysklad.ru/api/remap/1.1/entity/store",$method='GET');
            //             // dd($getStoreItem);
            //                 foreach ($getStoreItem->rows as $store){
            //                     $store = $store->meta;
            //                 }
            //             $getOrgItem = myCurl("https://online.moysklad.ru/api/remap/1.2/entity/organization",$method='GET');
            //                 foreach ($getOrgItem->rows as $organize){
            //                     $organize = $organize->meta;
            //                     // dd($organize);
            //                 }
                        
            //             $positions = [];
            //             $positions["quantity"]["quantity"] = $kol_spisania;
            //             $positions["quantity"]["assortment"]["meta"] = $product;
            //             $positions = json_encode(array_values($positions));
            //             dd($positions);
                      

            //             $body = [];
            //             $body["store"]["meta"] = $store;
            //             $body["organization"]["meta"] = $organize;
            //             $body["positions"] = json_decode($positions);
            //             $body["positions"] = $positions;
                        
            //             // dd($body);

            //             // $ms_spisanie = myCurlPost('https://online.moysklad.ru/api/remap/1.2/entity/loss','POST',$body);
            //         }
            //     }
            // }
                    
//                     }elseif($pr['article'] == $assort->article and $pr['balance']>$assort->stock){
//                         echo "надо оприходывать";
//                         $kol_oprihod = $pr['balance']-$assort->stock;
//                         echo " {$kol_oprihod}";
//                         echo "<p>остатки  из файла {$pr['title']} - {$pr['article']} - остаток = {$pr['balance']}</p>";

//                         //функция оприходования

//                         $filter['name'] = 'name';
//                         $filter['value'] = $assort->name;
//                         $filtr = "?filter = {$filter['name']} = {$filter['value']}";
//                         $getIdItem = myCurl("https://online.moysklad.ru/api/remap/1.2/entity/product",$method='GET',$filtr);
//                         // dd($getIdItem);
//                             foreach($getIdItem->rows as $idItem){
//                                 if ($idItem->name == $assort->name){
//                                     $id = $idItem->id; //id выбранного товара
//                                     $product = $idItem->meta;
//                                     // dd($product);
//                                 }
                                
//                             }
//                         $getStoreItem = myCurl("https://online.moysklad.ru/api/remap/1.1/entity/store",$method='GET');
//                         // dd($getStoreItem);
//                             foreach ($getStoreItem->rows as $store){
//                                 $store = $store->meta;
//                             }
//                         $getOrgItem = myCurl("https://online.moysklad.ru/api/remap/1.2/entity/organization",$method='GET');
//                             foreach ($getOrgItem->rows as $organize){
//                                 $organize = $organize->meta;
//                                 // dd($organize);
//                             }
                        
//                         $positions = [];
//                         $positions["quantity"]["quantity"] = $kol_oprihod;
//                         $positions["quantity"]["assortment"]["meta"] = $product;
//                         $positions = json_encode(array_values($positions));
//                         // dd($positions);
                      

//                         $body = [];
//                         $body["store"]["meta"] = $store;
//                         $body["organization"]["meta"] = $organize;
//                         $body["positions"] = json_decode($positions);
//                         // $body["positions"] = $positions;
                        
//                         // dd($body);

//                         $ms_oprihod = myCurlPost('https://online.moysklad.ru/api/remap/1.2/entity/enter','POST',$body);





//                     }
//                     // echo "<p>остатки  из файла {$pr['title']} - {$pr['article']} - остаток = {$pr['balance']}</p>";
//                 }
//             }
//         }
//         foreach ($ms_assort->rows as $assort) {
//         echo "<br> Товары из мойсклада {$assort->name} остаток = {$assort->stock}";
//          }
// // }
// echo '</div>';
// // if (!empty($prod)){
// // 	foreach($prod as $val){
// // 		if ($val['count'] == 4)
// // 		echo $val['article']."<br>";
// // 	}
// // }

// // конвертирование массива в xml файл

// // if (!file_exists('new_items.xml')){//

// // 	$xml = "<root_items>";
// // 	foreach($prod as $r){
// // 		if (empty($r['count'])){
// // 			$r['count'] = 0;
// // 		}
		
// // 		$xml .= "<goods><name>{$r['gname']}</name>
// // 						<article>{$r['article']}</article>
// // 						<count>{$r['count']}</count>
// // 				</goods>";
// // 	}
// // 		$xml .= "</root_items>";
// // 		$sxe = new SimpleXMLElement($xml);
// // 		$dom = new DOMDocument('1,0');
// // 		$dom->preserveWhiteSpace = false;
// // 		$dom->formatOutput = true;
// // 		$dom->loadXML($sxe->asXML());
// // 		echo $dom->saveXML();
// // 		$dom->save('new_items.xml');
// // }else{

	
// // 	// $url = 'https://test3.spey.ru/parsinXls/new_items.xml';
// // 	// $xml = file_get_contents("{$url}");
// // 	// $xml = simplexml_load_file($url);

// // 	// var_dump($_SERVER['DOCUMENT_ROOT']);

	
// // 	$xml1 = simplexml_load_file('items1.xml','SimpleXMLElement');
// // 	foreach ($xml->goods as $key){
// // 		$count[] = $key->count;
// // 		$name[] = $key->name;
// // 		$article[] = $key->article;
// // 	}
// //     // dd($xml1);

// // }


// // $xml1 = simplexml_load_file('new_items.xml'); 
// // foreach ($xml1->goods as $key){
// // 	$count[] = $key->name;
// // 	$title[] = $key->name;
// // }
// // dd($count);
// ?>