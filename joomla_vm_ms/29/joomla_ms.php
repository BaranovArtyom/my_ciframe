<?php

header("Content-Type: text/html;charset=UTF-8");
include("joomla_inc.php");
/*
$res = mysqli_query($db, "SELECT goods FROM ms_system");
list($goods) = mysqli_fetch_row($res);
if ($goods > (time() - 60*4)) exit;
else {
//	mysqli_query($db, 'UPDATE ms_system SET goods="'.time().'"');
} */

echo date("d.m.Y H:i:s",time())."<br><br>";


$link='https://online.moysklad.ru/api/remap/1.1/entity/currency';
$json=ms_query($link);
foreach($json['rows'] as $k=>$v){

		$CURR[$v['name']]=$v['meta'];
		$CURR_RATE[$v['meta']['href']]=$v['rate'];

}


$CHECK_MS = true;
$page = 0;
while($CHECK_MS) {
	$limit = 100;
	$offset = $limit*$page;
	$link = "https://online.moysklad.ru/api/remap/1.1/entity/productfolder?limit=$limit&offset=$offset";
	$json = ms_query($link);

	foreach($json['rows'] as $k => $v) {

		$tmpx = explode("/", $v['productFolder']['meta']['href']);
		$tmp_ms_par_cat = $tmpx[8];


		$respr = mysqli_query($db, "SELECT site_id FROM ms_cats WHERE ms_id='$tmp_ms_par_cat'");
		list($par_cat_id) = mysqli_fetch_row($respr);

		$res = mysqli_query($db, "SELECT MC.id, MC.site_id FROM ms_cats as MC, kpfdj_jshopping_categories as JC WHERE MC.ms_id='{$v['id']}' and MC.site_id=JC.category_id");
		list($chid, $site_id) = mysqli_fetch_row($res);
		if (!$chid) {
			if($v['id']){

				mysqli_query($db, "INSERT INTO kpfdj_jshopping_categories SET category_parent_id='$par_cat_id', products_row='4', category_add_date='".date("Y-m-d H:i:s", time())."', `name_ru-RU`='".addslashes($v['name'])."', `description_ru-RU`='".addslashes($v['description'])."'");
				$site_id = mysqli_insert_id($db);
				mysqli_query($db, "INSERT INTO ms_cats SET ms_id='{$v['id']}', ms_name='".addslashes($v['name'])."', site_id='$site_id'");
			}

//echo "add cat --- {$v['name']}<br>";//exit;

		} else {

			mysqli_query($db, "UPDATE ms_cats SET ms_name='".addslashes($v['name'])."' WHERE id='$chid'");
			mysqli_query($db, "UPDATE kpfdj_jshopping_categories SET `name_ru-RU`='".addslashes($v['name'])."', `description_ru-RU`='".addslashes($v['description'])."', category_parent_id='$par_cat_id' WHERE category_id='$site_id'");

//echo "update cat --- $site_id --- {$v['name']}<br>";//exit;
//echo("UPDATE kpfdj_jshopping_categories SET `name_ru-RU`='".addslashes($v['name'])."', `description_ru-RU`='".addslashes($v['description'])."', category_parent_id='$par_cat_id' WHERE category_id='$site_id'<BR>");

		}

 	}

	if (!count($json['rows'])) {
		$CHECK_MS=false;
	} 
	$page++;

}

echo "<br><br>";



$link = "https://online.moysklad.ru/api/remap/1.1/entity/product";
$json = ms_query($link);
$good_count = 0;
$ff = fopen('now', "r");
$page = fread($ff, 1000);
fclose($ff);
$good_limit = $page + 3;
if ($json['meta']['size'] < ($page*100)) {
	$good_limit = 3;
	$page = 0;
}
if (!$page) $page = 0;

$headers = "Content-type: text/html; charset=utf-8 \r\n";


$CHECK_MS = true;
$limit = 100;
while($CHECK_MS) {
	$offset = $limit*$page;
	$link = "https://online.moysklad.ru/api/remap/1.1/entity/product?limit=$limit&offset=$offset";
	$json = ms_query($link);
	foreach($json['rows'] as $k => $v) {

		$saleprice = $v['salePrices'][0]['value']/100;

     		$kurs=$CURR_RATE[$v['salePrices'][0]['currency']['meta']['href']];
//echo("<pre>");
//var_dump($CURR_RATE);
//var_dump($v['salePrices']);
     		if(!$kurs) $kurs=1;
		$saleprice=$kurs*$saleprice;

//var_dump($v['code']);
//var_dump($kurs);
//var_dump($saleprice);
//echo("</pre>");
		$stock_count = 0;
		$link_stock = "https://online.moysklad.ru/api/remap/1.1/report/stock/bystore?limit=10&offset=0&product.id=".$v['id'];
		$json_stock = ms_query($link_stock);
		if (count($json_stock['rows'][0]['stockByStore']) > 0) foreach($json_stock['rows'][0]['stockByStore'] as $ks => $vs) {
			if ($vs['stock'] > 0 && $vs['stock'] != $vs['reserve']) { // $vs['name'] == 'Интернет-магазин'
				$stock_count += (int) $vs['stock'];
			}
		}
		if (isset($v['code'])) {
			$res_gr = ms_query($v['productFolder']['meta']['href']);
			$resx = mysqli_query($db, "SELECT site_id FROM ms_cats WHERE ms_id='{$res_gr['id']}'");
			list($site_id) = mysqli_fetch_row($resx);
			$res = mysqli_query($db, "SELECT product_id, unlimited FROM kpfdj_jshopping_products WHERE product_ean='".$v['code']."'");
			list($product_id, $unlimited) = mysqli_fetch_row($res);

			if ($product_id) {
				$res = mysqli_query($db, "SELECT id FROM ms_products WHERE product_id='$product_id'");
				list($chid) = mysqli_fetch_row($res);
				if ($chid) {
					mysqli_query($db, "UPDATE ms_products SET code='".$v['code']."', ms_id='".$v['id']."' WHERE product_id='$product_id'");
				} else {
					mysqli_query($db, "INSERT INTO ms_products SET code='".$v['code']."', product_id='$product_id', ms_id='".$v['id']."'");
				}

				$unlimited = (int) $unlimited;
				if ($unlimited == 1) {
					mysqli_query($db, "UPDATE kpfdj_jshopping_products SET product_price='$saleprice', `name_ru-RU`='".addslashes($v['name'])."', `description_ru-RU`='".addslashes($v['description'])."', date_modify='".date("Y-m-d H:i:s", time())."' WHERE product_id='$product_id'");
				} else {
					mysqli_query($db, "UPDATE kpfdj_jshopping_products SET product_quantity='$stock_count', product_price='$saleprice', `name_ru-RU`='".addslashes($v['name'])."', `description_ru-RU`='".addslashes($v['description'])."', date_modify='".date("Y-m-d H:i:s", time())."' WHERE product_id='$product_id'");
				}

				$resc = mysqli_query($db, "SELECT product_id FROM kpfdj_jshopping_products_to_categories WHERE product_id='$product_id'");
				list($cproduct_id) = mysqli_fetch_row($resc);
				if (!$cproduct_id) {
					mysqli_query($db, "INSERT INTO kpfdj_jshopping_products_to_categories SET product_id='$product_id', category_id='$site_id'");
				} else {
					mysqli_query($db, "UPDATE kpfdj_jshopping_products_to_categories SET category_id='$site_id' WHERE product_id='$product_id'");
				}

				//mysqli_query($db, "DELETE FROM kpfdj_jshopping_products_images WHERE product_id='$product_id'");
				//mysqli_query($db, "UPDATE kpfdj_jshopping_products SET image='noimage.gif' WHERE product_id='$product_id'");

				if (isset($v['image']['meta']['href'])) {
					$image_url = $v['image']['meta']['href'];
					$topath = $_SERVER['DOCUMENT_ROOT'].'/components/com_jshopping/files/img_products/full_';
					$file_path = $topath.$v['image']['filename'];
					if (!file_exists($file_path) || (filesize($file_path) != $v['image']['size'])) {
						$rescurl = ms_query_image($image_url);
						$fp = fopen($file_path, 'w');
						fwrite($fp, $rescurl);
						fclose($fp);
						$tmp['tmp_name'] = $file_path;
						if (file_exists($file_path)) {
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/components/com_jshopping/files/img_products/thumb_', $v['image']['filename'], 200, 150, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/components/com_jshopping/files/img_products/', $v['image']['filename'], 350, 300, 100);
							mysqli_query($db, "UPDATE kpfdj_jshopping_products SET `image`='".$v['image']['filename']."' WHERE product_id='$product_id'");
							$resi = mysqli_query($db, "SELECT image_id FROM kpfdj_jshopping_products_images WHERE product_id='$product_id'");
							list($image_id) = mysqli_fetch_row($resi);
							if (!$image_id) {
								mysqli_query($db, "INSERT INTO kpfdj_jshopping_products_images SET product_id='$product_id', image_name='".$v['image']['filename']."'");
							} else {
								mysqli_query($db, "UPDATE kpfdj_jshopping_products_images SET image_name='".$v['image']['filename']."' WHERE product_id='$product_id'");
							}
						}
					} elseif (file_exists($file_path)) {
						mysqli_query($db, "UPDATE kpfdj_jshopping_products SET `image`='".$v['image']['filename']."' WHERE product_id='$product_id'");
						$resi = mysqli_query($db, "SELECT image_id FROM kpfdj_jshopping_products_images WHERE product_id='$product_id'");
						list($image_id) = mysqli_fetch_row($resi);
						if (!$image_id) {
							mysqli_query($db, "INSERT INTO kpfdj_jshopping_products_images SET product_id='$product_id', image_name='".$v['image']['filename']."'");
						} else {
							mysqli_query($db, "UPDATE kpfdj_jshopping_products_images SET image_name='".$v['image']['filename']."' WHERE product_id='$product_id'");
						}
					}
				} else {
						$resi = mysqli_query($db, "SELECT image_id FROM kpfdj_jshopping_products_images WHERE product_id='$product_id'");
						list($image_id) = mysqli_fetch_row($resi);
						if (!$image_id) {
							mysqli_query($db, "INSERT INTO kpfdj_jshopping_products_images SET product_id='$product_id', image_name='noimage.gif'");
						} else {
							mysqli_query($db, "UPDATE kpfdj_jshopping_products_images SET image_name='noimage.gif' WHERE product_id='$product_id'");
						}
				}

			echo "update pr --- $product_id --- {$v['name']} --- $saleprice / $price --- $stock_count --- $unlimited<br>";//exit;


			} else {

				mysqli_query($db, "INSERT INTO kpfdj_jshopping_products SET product_ean='".$v['code']."', product_quantity='$stock_count', unlimited='0', product_date_added='".date("Y-m-d H:i:s", time())."', date_modify='".date("Y-m-d H:i:s", time())."', product_price='$saleprice', min_price='0', `name_ru-RU`='".addslashes($v['name'])."', `description_ru-RU`='".addslashes($v['description'])."', product_publish='1', currency_id='2', extra_field_1='0', extra_field_2='0'");
				$product_id = mysqli_insert_id($db);
				mysqli_query($db, "INSERT INTO kpfdj_jshopping_products_to_categories SET product_id='$product_id', category_id='$site_id'");
				mysqli_query($db, "INSERT INTO ms_products SET code='".$v['code']."', product_id='$product_id', ms_id='".$v['id']."'");
				if (isset($v['image']['meta']['href']) && $product_id > 0) {
					$image_url = $v['image']['meta']['href'];
					$topath = $_SERVER['DOCUMENT_ROOT'].'/components/com_jshopping/files/img_products/full_';
					$file_path = $topath.$v['image']['filename'];
					$rescurl = ms_query_image($image_url);
					$fp = fopen($file_path, 'w');
					fwrite($fp, $rescurl);
					fclose($fp);
					$tmp['tmp_name'] = $file_path;
					if (file_exists($file_path)) {
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/components/com_jshopping/files/img_products/thumb_', $v['image']['filename'], 200, 150, 100);
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/components/com_jshopping/files/img_products/', $v['image']['filename'], 350, 300, 100);
						mysqli_query($db, "UPDATE kpfdj_jshopping_products SET `image`='".$v['image']['filename']."' WHERE product_id='$product_id'");
						mysqli_query($db, "INSERT INTO kpfdj_jshopping_products_images SET product_id='$product_id', image_name='".$v['image']['filename']."'");
					}
				}

echo "add pr --- $product_id --- {$v['name']} --- $saleprice / $price  --- $stock_count<br>";//exit;

			}
		}
	}
	$page++;
	if (!count($json['rows']) || ($page == $good_limit)) {
		$CHECK_MS = false;
		echo("###");
		$ff = fopen("now","w");
		fwrite($ff, $good_limit);
		fclose($ff);
	} 
	echo($page." - $good_limit<BR>");
}

echo date("d.m.Y H:i:s",time())."<br><br>";

?>
