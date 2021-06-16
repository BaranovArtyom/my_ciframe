<?php

declare(strict_types=1);
ini_set('display_errors', 'on');
require_once 'functions.php';

$ms_assort = myCurl('https://online.moysklad.ru/api/remap/1.2/entity/assortment');//получение json с остатками из мойсклад
// dd($ms_assort);
echo '<form action="#" method="post">
        <p>Товары из ERC</p>
        <select name="artnumberList">';

$xml = simplexml_load_file('xml.xml');  // получение объекта json файла XML

$prod = []; // массив для товаров которые совпали с erc и мойсклад
foreach ($xml->vendor as $key) {        // перебор эл-ов из файла
    $artnumber = $key->goods->code;
    $title = $key->goods->gname;
    $price = $key->goods->sprice;
    $category_id = $key->goods->categoryId;
    $name = $key->goods->category;
    $id_product = $category_id;
    $balance = $key->goods->warehouse1 + $key->goods->warehouse44 + $key->goods->warehouse55 + $key->goods->warehouse5;
    
    echo "<option value='{$artnumber}'> {$title} </option>"; // вывод в селекте имена эл-ов
   
    foreach ($ms_assort->rows as $assort) {    // перебор эл-ов ассортимента из мойсклад
    
        if ($artnumber == $assort->code) {     // если артикулы совпали сохраняем данные из файла
            $exist['title'] = (string)$title;
            $exist['article'] = (string)$artnumber;
            $exist['balance'] = (string)$balance;
            $prod[] = $exist;
           
        }
    }
}
echo '</select>
        <button type="submit" value="Submit">сравнить</button>
    </form>
    <div>';
// dd($prod);
// dd($ms_assort->rows);


if(!empty($prod) and !empty($_POST['artnumberList'])){
        foreach ($ms_assort->rows as $assort) {
            // dd($assort);
            if ($_POST['artnumberList'] == $assort->code){
                echo "Выбранный товар из мойсклад {$assort->name} остаток = {$assort->stock}.<br> ";
             
                foreach ($prod as $pr) {
                    if ($pr['article'] == $_POST['artnumberList'] and $pr['balance']<$assort->stock){
                        echo "надо списать";
                        $kol_spisania = $assort->stock-$pr['balance'];
                        echo " {$kol_spisania}";
                       
                        //функция списания 
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
                        $positions["quantity"]["quantity"] = $kol_spisania;
                        $positions["quantity"]["assortment"]["meta"] = $product;
                        $positions = json_encode(array_values($positions));
                        // dd($positions);
                      

                        $body = [];
                        $body["store"]["meta"] = $store;
                        $body["organization"]["meta"] = $organize;
                        $body["positions"] = json_decode($positions);
                        // $body["positions"] = $positions;
                        
                        // die(dd($body));

                        $ms_spisanie = myCurlPost('https://online.moysklad.ru/api/remap/1.2/entity/loss','POST',$body);
                    
                    }elseif($pr['article'] == $_POST['artnumberList'] and $pr['balance']>$assort->stock){
                        echo "надо оприходывать";
                        $kol_oprihod = $pr['balance']-$assort->stock;
                        echo " {$kol_oprihod}";
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
                        // $body["positions"] = $positions;
                        
                        // dd($body);
                        // die(dd($body));

                        $ms_oprihod = myCurlPost('https://online.moysklad.ru/api/remap/1.2/entity/enter','POST',$body);





                    }
                    echo "<p>остатки  из файла {$pr['title']} - {$pr['article']} - остаток = {$pr['balance']}</p>";
                }
            }
        }
        foreach ($ms_assort->rows as $assort) {
        echo "<br> Товары из мойсклада {$assort->name} остаток = {$assort->stock}";
         }
}
echo '</div>';
