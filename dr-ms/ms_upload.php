<?php 
ini_set('display_errors', 'on');

require_once 'config.php';                                 
require_once 'funcs.php';     
require_once 'drupal-ms.php';   

/**выгрузка товаров в мс $rev товары в drupal */

foreach ($rev as $val){
    // if ($val['get_name_category_product'] == "Зеленый чай" and $val["product_id"] == 153645){
    if ($val['get_name_category_product'] == "Зеленый чай" ){
        dd($val);
        $productDataAtribute = array();
        // dd($val['get_nalichie_product']);
        $val_price = $val['get_price_product'];
        $val_price_old = $val['get_old_price_product'];
        $val_nalichie = $val['get_nalichie_product'];
        $val_category = $val['get_name_category_product'];
        $val_skidka = $val['get_value_discount']."%";
        $val_svoystva = $val['svoystva'][0];
        $val_vkus = $val['vkus'][0];
        $val_vid = $val['vid'];
        $val_forma = $val['forma'];
        
        $val_provincija = $val['provincija'];
        // dd($val_provincija);exit;
        $nameProductfolder = $val['get_name_category_product'];
        $val_sku = $val['sku'];
        $val_name = $val['title'];
        $val_code = $val['sku'];
        $country = $val['strana'];
        $kol_fasovki = $val['get_fasovka_prod'];
        // $val['get_fasovka_prod'] = '1 шт';
        $proverka_izm = strpos($val['get_fasovka_prod'], 'г');
        $proverka1_izm = strpos($val['get_fasovka_prod'], 'шт');
        if ( $proverka_izm !== false ) {
            $ed_izmerenia = $body_gramm['uom'];
            // dd($ed_izmerenia);
        }elseif ($proverka1_izm !== false ) {
            $ed_izmerenia = $body_shtuk['uom'];
            // dd($ed_izmerenia);
        }
        // exit;
       
        // $nameProductfolder = "Белый чай";                           // имя группы 
        $getProductFolder = getProductFolder($nameProductfolder);   // получение meta группы
        // dd((object)$getProductFolder);

        $price = (float)$val_price;$old_price = (float)$val_price_old;
        $getPrice = getPrice($price,$old_price);                       // получение meta для цены
        // dd((object)$getPrice);

        // формирование атрибутов 

        // для фасовки 
        // $kol_fasovki = "7г";
        $get_fasovka = get_fasovka($kol_fasovki); // meta для фасовки 
        // dd($get_fasovka);

        // для страны 
        // $country = 'Венесуэлла';                 // значение страны в товаре
        $getValueCountry = getValueCountry($country);
        // dd($getValueCountry);

        //для статуса
        if ($val['status'] == 1) {
            $val['status'] = true;
        }else {
            $val['status'] = false;
        }
        $get_status = get_status($val['status']);
        // dd($get_status);exit;

        //получение атрибута Наличие
        $getValueNalichie = getValueNalichie($val_nalichie);
        // dd($val['get_nalichie_product']);
        // dd($getValueNalichie);

        // получение атрибута категории
        $getCategory =getCategory($val_category);
        // dd($getCategory);

        // получение скидки атрибута 
        // $val_skidka = "20%";
        $getSkidka = getSkidka($val_skidka);
        // dd($getSkidka);

        // получение свойства атрибута
        $getValuesvoystva = getValuesvoystva($val_svoystva);
        // dd($getValuesvoystva);

        // получение вкус атрибутов
        $getValueVkus = getValueVkus($val_vkus); 
        // dd($getValueVkus);

        //получение вид атрибутов
        $getVid = getVid($val_vid);
        // dd($getVid);

        //получение форма атрибутов
        $getForma = getForma($val_forma);
        // dd($getForma);

        //получение провинции атрибут
        $getValueProvincija = getValueProvincija($val_provincija);
        // dd($getValueProvincija);

        // exit;
        // $productDataAtribute[] = $get_fasovka;
        if(isset($get_fasovka)) {
            $productDataAtribute[] = $get_fasovka;
        }
        // $productDataAtribute[] = $getValueCountry ;
        if(isset($getValueCountry)) {
            $productDataAtribute[] = $getValueCountry;
        }
        // $productDataAtribute[] = $get_status;
        if(isset($get_status)) {
            $productDataAtribute[] = $get_status;
        }
        // $productDataAtribute[] = $getValueNalichie;
        if(isset($getValueNalichie)) {
            $productDataAtribute[] = $getValueNalichie;
        }
        // $productDataAtribute[] = $getCategory;
        if(isset($getCategory)) {
            $productDataAtribute[] = $getCategory;
        }
        // $productDataAtribute[] = $getSkidka;
        if(isset($getSkidka)) {
            $productDataAtribute[] = $getSkidka;
        }
        // $productDataAtribute[] = $getValuesvoystva;
        if(isset($getValuesvoystva)) {
            $productDataAtribute[] = $getValuesvoystva;
        }
        // $productDataAtribute[] = $getValueVkus;
        if(isset($getValueVkus)) {
            $productDataAtribute[] = $getValueVkus;
        }
        // $productDataAtribute[] = $getVid;
        if(isset($getVid)) {
            $productDataAtribute[] = $getVid;
        }
        // $productDataAtribute[] = $getForma;
        if(isset($getForma)) {
            $productDataAtribute[] = $getForma;
        }
        if(isset($getValueProvincija)) {
            $productDataAtribute[] = $getValueProvincija;
        }
       
        $body["attributes"] = $productDataAtribute;
        $body["name"] = $val_name;
        $body["article"] = $val_sku;
        $body["code"] = $val_sku;
        $body["productFolder"] = $getProductFolder;
        $body["salePrices"] = $getPrice;
        $body["uom"] = $ed_izmerenia;

        file_put_contents('looger.log',date('H:i:s').' названия - '.$val_name." ".$val_sku." ".$val_price." ".$country." ".$kol_fasovki." ". $val_category." ".$val_skidka.
        " ".$val_svoystva." ".$val_vkus." ".$val_forma." ".$val_vid." ".$val_provincija." ".$nameProductfolder." ".$kol_fasovki." ".$val_price_old. "\n",FILE_APPEND);
        dd($body);
        // exit;

        $createProduct = createProduct($body);
        dd($createProduct);
          exit;

    }
    
}
// exit;
