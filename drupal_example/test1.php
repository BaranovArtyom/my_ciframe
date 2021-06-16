<?php

$hour=date("H",time());
if(($hour>2) and ($hour<7)) exit();

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
//ini_set('memory_limit', '512M');
//ini_set('max_execution_time', '300');
header("Content-Type: text/html;charset=UTF-8");
include("inc.php");

$PR_NAME=array('ret_old','s_opt','l_opt','retail');



$updfrom=date("Y-m-d%20H:i:s",time()-(3600*1.5));




///////////stock
$CHECK_MS=true;                                                                                  
$limit=1000;
$page=0;
while($CHECK_MS){
	$offset=$limit*$page;
	$link="https://online.moysklad.ru/api/remap/1.1/report/stock/all?limit=$limit&offset=$offset";
	$json=ms_query($link);
	foreach($json['rows'] as $k=>$v){
		//var_dump($v);exit();
		$qty=null;
//		foreach($v['stockByStore'] as $k2=>$v2){
		$qty=$qty+($v['stock']-$v['reserve']);
//        	}	
		$url=parse_url ($v['meta']['href']);
		$id_arr=explode("/",$url['path']);
		$id=$id_arr[6];
		if($v['meta']['type']=='variant') {
			$SPR_VARIANTS[$id]=$qty;	
		}elseif($v['meta']['type']=='product') {
			$SPR_GOODS[$id]=$qty;

		}
	}
	if(!count($json['rows'])) { $CHECK_MS=false; } 
	if(isset($json['errors'])) { exit(); } 
	$page++;
}








$link='https://online.moysklad.ru/api/remap/1.1/entity/currency';
$json=ms_query($link);
if(isset($json['errors'])) exit();
foreach($json['rows'] as $k=>$v){
	$KURS[$v['meta']['href']]=$v['rate'];
}

if(!count($KURS)) exit();

$ff = fopen("kurs.log","a+");
fwrite($ff, date('d-m-Y H:i:s',time()). ' - '.json_encode($KURS)."\n");
fclose($ff);






$headers = "Content-type: text/html; charset=utf-8 \r\n";
$link = "https://online.moysklad.ru/api/remap/1.1/entity/product";//?updatedFrom=".$updfrom;
$json = ms_query($link);
$good_count = 0;
$ff = fopen('now', "r");
$page = fread($ff, 1000);
fclose($ff);         
$good_limit = $page+10;
if ($json['meta']['size'] < ($page*100)) {
	$good_limit=10;
	$page=0;
}
if (!$page) $page = 0;
$CHECK_MS = true;
$limit = 100;
$kk=0;
while($CHECK_MS) {
	$offset = $limit*$page;
	$link = "https://online.moysklad.ru/api/remap/1.1/entity/product?expand=supplier,images&limit=$limit&offset=$offset";//&updatedFrom=".$updfrom;
	$json = ms_query($link);
	if(isset($json['errors'])) exit();
	foreach($json['rows'] as $k => $v) {


		$updtime=strtotime($v['updated']);
//		if($updtime<time()-(24*3600)) continue;

		$res_gr = ms_query($v['productFolder']['meta']['href']);
		$saleprice = $v['salePrices'][0]['value']/100;

		$stock = $stock_count = 0;
		$stock = 1;

		if(isset($SPR_GOODS[$v['id']])) $stock_count=$SPR_GOODS[$v['id']];
/*
		$link_stock = "https://online.moysklad.ru/api/remap/1.1/report/stock/bystore?limit=10&offset=0&product.id=".$v['id'];
		$json_stock = ms_query($link_stock);
		if (count($json_stock['rows'][0]['stockByStore']) > 0) foreach($json_stock['rows'][0]['stockByStore'] as $ks => $vs) {
//			if ($vs['name'] == 'Интернет-магазин' && $vs['stock'] > 0 && $vs['stock'] != $vs['reserve']) {
				$stock = 1;
				$stock_count = $vs['stock'];
//			}
		}

var_dump($v['id']);
var_dump($stock_count);
exit();
*/
		$kk++;
                $check_product_id=0;
		if (isset($v['name'])) {
			$res = mysqli_query($db,"select product_id from ms_products where ms_id='".$v['id']."'");
			list($product_id) = mysqli_fetch_row($res);
			if(!$product_id) {
				$rj = mysqli_query($db,"select nid from node where title='".addslashes($v['name'])."'");
				list($product_id) = mysqli_fetch_row($rj);

			}

			$rj = mysqli_query($db,"select entity_id from field_data_field_product_type_vendor where field_product_type_vendor_value='".addslashes($v['code'])."' order by entity_id desc");
			list($entid) = mysqli_fetch_row($rj);
			if($entid){
					$rj = mysqli_query($db,"select entity_id from field_data_field_product_type where field_product_type_value='$entid'");
					list($check_product_id) = mysqli_fetch_row($rj);
			}

//echo($v['code']." - ".$product_id." - ".$check_product_id."<BR>");
			if($check_product_id and !$product_id) $product_id=$check_product_id;
			elseif($product_id  and ($product_id != $check_product_id)) { 
//echo("delete from ms_products where product_id='$product_id'");
				 mysqli_query($db,"delete from ms_products where product_id='$product_id'");
				 $product_id=$check_product_id;
//var_dump(mysqli_error($db));
			}

			$xcount='';
			$ycount='';
			foreach($v['attributes'] as $ka=>$va){
				if($va['name']=='X купи') $xcount=$va['value'];
				if($va['name']=='Y получи') $ycount=$va['value'];
			}

			$rj = mysqli_query($db,"select  `type` from node where nid='$product_id'");
			list($product_type) = mysqli_fetch_row($rj);
			$resx = mysqli_query($db,"select site_id from ms_cats where ms_id='{$res_gr['id']}'");
			list($site_id) = mysqli_fetch_row($resx);
			$CURR=$PRICES=$skprice=$kopt=$mopt=$roznica=null;
			foreach($v['salePrices'] as $ks=>$vs){
			 	if($vs['priceType']=='Цена продажи') { $PRICES['ret_old']=$vs['value']/100;  $CURR['ret_old']=$KURS[$vs['currency']['meta']['href']]; }
			 	if($vs['priceType']=='Мелкий опт') {$PRICES['s_opt']=$vs['value']/100; $CURR['s_opt']=$KURS[$vs['currency']['meta']['href']]; }
			 	if($vs['priceType']=='Крупный опт') {$PRICES['l_opt']=$vs['value']/100; $CURR['l_opt']=$KURS[$vs['currency']['meta']['href']]; }
			 	if($vs['priceType']=='Цена со скидкой') {$PRICES['retail']=$vs['value']/100; $CURR['retail']=$KURS[$vs['currency']['meta']['href']]; }

			}
			if(!$PRICES['retail']) { $PRICES['retail']=$PRICES['ret_old']; $PRICES['ret_old']=''; }



			foreach($PRICES as $ks=>$vs){
//					if(!$PRICES['ret_old']) { 
//						$PRICES['retail']=$PRICES['ret_old']; 
//						$PRICES['ret_old']=''; 
//					}
			}


if($v['code']=='134-176') var_dump($PRICES);

			if ($product_id) {

/*				$res_ms = mysqli_query($db,"select nid from node_revision where nid='$product_id'");
				list($rid) = mysqli_fetch_row($res_ms);
				if (!$rid) {
					mysqli_query($db,"insert into node_revision set nid='".$product_id."', vid='".$product_id."', title='".$v['name']."', uid='39', status='1', timestamp='".time()."'");
				} else {
					mysqli_query($db,"update node_revision set title='".$v['name']."', timestamp='".time()."' where nid='".$product_id."'");
				}
*/
				$res_ms = mysqli_query($db,"select id from ms_products where product_id='$product_id'");
				list($chid) = mysqli_fetch_row($res_ms);
				if ($chid) {
					mysqli_query($db,"update ms_products set del='0' where product_id='$product_id'");
				} else {
					mysqli_query($db,"insert into ms_products set xmlId='".$v['externalCode']."', product_id='$product_id', ms_id='".$v['id']."', del='0'");
				}


/*
				mysqli_query($db,"update node set title='".$v['name']."', changed='".time()."' where nid='".$product_id."'");
*/


/*
				$res_ms = mysqli_query($db,"select entity_id from field_data_field_nal where entity_id='$product_id'");
				list($nalid) = mysqli_fetch_row($res_ms);
				if (!$nalid) {
					mysqli_query($db,"insert into field_data_field_nal set entity_type='node', bundle='".$product_type."', entity_id='".$product_id."', revision_id='".$product_id."', language='und', field_nal_value='".$stock."'");
					mysqli_query($db,"insert into field_revision_field_nal set entity_type='node', bundle='".$product_type."', entity_id='".$product_id."', revision_id='".$product_id."', language='und', field_nal_value='".$stock."'");
				} else {
					mysqli_query($db,"update field_data_field_nal set field_nal_value='".$stock."' where entity_id='".$product_id."'");

					mysqli_query($db,"update field_revision_field_nal set field_nal_value='".$stock."' where entity_id='".$product_id."'");
				}
				
				mysqli_query($db,"update field_data_field_product_export_to_mailru set field_product_export_to_mailru_value='".$stock."' where entity_id='".$product_id."'");

				mysqli_query($db,"update field_revision_field_product_export_to_mailru set field_product_export_to_mailru_value='".$stock."' where entity_id='".$product_id."'");
				
				mysqli_query($db,"update field_data_field_product_export_to_market set field_product_export_to_market_value='".$stock."' where entity_id='".$product_id."'");

				mysqli_query($db,"update field_revision_field_product_export_to_market set field_product_export_to_market_value='".$stock."' where entity_id='".$product_id."'");
				
*/


/*
				if (isset($v['image']['meta']['href'])) {
					$image_url = $v['image']['meta']['href'];
					$topath = $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/';
					$file_path = $topath.$v['image']['filename'];
/*					if (!file_exists($file_path)) {// || (filesize($file_path) != $v['image']['size'])

echo "1img ---- {$v['name']} --- $fid --- {$v['image']['title']} --- {$v['image']['filename']}<br>";continue;
*/

//						$rescurl = ms_query_image($image_url);
//if($rescurl){
//var_dump($rescurl);exit();
//}
//}

//}
/*
						$fp = fopen($file_path, 'w');
						fwrite($fp, $rescurl);
						fclose($fp);
						$tmp['tmp_name'] = $file_path;
						if (file_exists($file_path)) {
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/thumbnail/public/', $v['image']['filename'], 55, 100, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/89x89r/public/', $v['image']['filename'], 89, 89, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/100x150r/public/', $v['image']['filename'], 100, 150, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/210x210r/public/', $v['image']['filename'], 210, 210, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/210x330/public/', $v['image']['filename'], 210, 330, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/300x540r/public/', $v['image']['filename'], 300, 540, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/300x540r2/public/', $v['image']['filename'], 300, 540, 100);
						}
						$res_ms = mysqli_query($db,"select field_tovar_photos_fid from field_data_field_images where entity_id='$product_id'");
						list($fid) = mysqli_fetch_row($res_ms);
						if ($fid) {
							mysqli_query($db,"update file_managed set filename='".$v['image']['filename']."', uri= '".'public://'.$v['image']['filename']."' where fid='$fid'");
						} else {
							mysqli_query($db,"insert into file_managed set uid='39', filename='".$v['image']['filename']."', uri= '".'public://'.$v['image']['filename']."', filemime='image/jpeg', filesize='".$v['image']['size']."', status='1', timestamp='".time()."'");
							$fid = mysqli_insert_id($db);
							if ($fid) {
								list($width, $height, $type, $attr) = getimagesize($tmp);
								mysqli_query($db,"insert field_data_field_images set entity_type='node', bundle='product', entity_id='$product_id', language='und', field_images_fid='$fid', field_images_width='$width', field_images_height='$height'");
							}
						}
					}
					$res_ms = mysqli_query($db,"select fid from file_managed where filename='".$v['image']['filename']."'");
					list($fid1) = mysqli_fetch_row($res_ms);
					$res_ms = mysqli_query($db,"select field_tovar_photos_fid from field_data_field_tovar_photos where entity_id='$product_id'");
					list($fid2) = mysqli_fetch_row($res_ms);
					if (file_exists($file_path) && $fid1 && !$fid2) {
						if ($fid1) {
							list($width, $height, $type, $attr) = getimagesize($tmp);
							mysqli_query($db,"insert field_data_field_images set entity_type='node', bundle='product', entity_id='$product_id', language='und', field_images_fid='$fid', field_images_width='$width', field_images_height='$height'");
						}
					}
				}
*/
			} else {

				mysqli_query($db,"insert into node set type='product', language='ru', title='".addslashes($v['name'])."', uid='1', status='1', created='".time()."', changed='".time()."'");
				$product_id = mysqli_insert_id($db);
				if($product_id){
				echo "*** --- $product_id --- $site_id --- {$v['name']}<br>";
				$entity_id=0;
				mysqli_query($db,"insert into field_collection_item set field_name ='field_product_type',revision_id='0',archived='0'");
				$entity_id=mysqli_insert_id($db);
				mysqli_query($db,"insert into field_collection_item_revision set item_id ='$entity_id'");
				$revision_id=mysqli_insert_id($db);

				mysqli_query($db,"update field_collection_item set revision_id ='$revision_id' where item_id='$entity_id'");


				mysqli_query($db,"insert into  field_data_field_product_type_vendor set  field_product_type_vendor_value='".addslashes($v['code'])."' ,
					entity_type='field_collection_item',	bundle='field_product_type',	deleted	='0',entity_id='$entity_id',	revision_id='$revision_id',
					language='und', delta='0', field_product_type_vendor_format =NULL");


				mysqli_query($db,"insert into  field_data_field_product_type set  entity_type='node',bundle='product',
					deleted='0',entity_id='$product_id',revision_id='$product_id', language	='und',delta='0',field_product_type_value='$entity_id',field_product_type_revision_id='$revision_id'");

				mysqli_query($db,"insert into  field_revision_field_product_type set  entity_type='node',bundle='product',
					deleted='0',entity_id='$product_id',revision_id='$product_id', language	='und',delta='0',field_product_type_value='$entity_id',field_product_type_revision_id='$revision_id'");


				mysqli_query($db,"insert into node_revision set nid='".$product_id."', vid='".$product_id."', title='".addslashes($v['name'])."', uid='1', status='1', timestamp='".time()."', log=''");

				mysqli_query($db,"update node set vid='".$product_id."' where nid='".$product_id."'");
//				mysqli_query($db,"insert into field_data_field_price set entity_type='node', bundle='product', entity_id='$product_id', revision_id='$product_id', language='und', field_price_value='$saleprice'");
//				mysqli_query($db,"insert into field_revision_field_price set entity_type='node', bundle='product', entity_id='$product_id', revision_id='$product_id', language='und', field_price_value='$saleprice'");
//				mysqli_query($db,"insert into field_data_field_nal set set entity_type='node', bundle='product', entity_id='".$product_id."', revision_id='".$product_id."', language='und', field_nal_value='".$stock."'");
//				mysqli_query($db,"insert into field_revision_field_nal set set entity_type='node', bundle='product', entity_id='".$product_id."', revision_id='".$product_id."', language='und', field_nal_value='".$stock."'");
				mysqli_query($db,"insert into ms_products set xmlId='".$v['externalCode']."', product_id='$product_id', ms_id='".$v['id']."', del='0'");
				if (isset($v['image']['meta']['href'])) {

					$image_url = $v['image']['meta']['href'];
					$topath = $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/';
					$file_path = $topath.$v['image']['filename'];
					$rescurl = ms_query_image($image_url);

					$fp = fopen($file_path, 'w');
	  		    		fwrite($fp, $rescurl);

					fclose($fp);
					$tmp['tmp_name'] = $file_path;
					if (file_exists($file_path)) {
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/thumbnail/public/', $v['image']['filename'], 55, 100, 100);
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/89x89r/public/', $v['image']['filename'], 89, 89, 100);
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/100x150r/public/', $v['image']['filename'], 100, 150, 100);
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/210x210r/public/', $v['image']['filename'], 210, 210, 100);
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/210x330/public/', $v['image']['filename'], 210, 330, 100);
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/300x540r/public/', $v['image']['filename'], 300, 540, 100);
						resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/300x540r2/public/', $v['image']['filename'], 300, 540, 100);
						mysqli_query($db,"insert into file_managed set uid='39', filename='".$v['image']['filename']."', uri= '".'public://'.$v['image']['filename']."', filemime='image/jpeg', filesize='".$v['image']['size']."', status='1', timestamp='".time()."'");
						$fid = mysqli_insert_id($db);
						if ($fid) {
							list($width, $height, $type, $attr) = getimagesize($tmp);
							mysqli_query($db,"insert field_data_field_images set entity_type='node', bundle='product', entity_id='$product_id', language='und', field_images_fid='$fid', field_images_width='$width', field_images_height='$height'");
						}
					}
		
				}
				}
			}


			if($product_id){
/*
				if(isset($v['image']['meta']['href'])) {
					$image_url=$v['image']['meta']['href'];
					$topath = $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/';
					$file_path = $topath.$v['image']['filename'];

					if(!file_exists($file_path)||(filesize($file_path)!=$v['image']['size'])) {
						var_dump($file_path);
						var_dump(file_exists($file_path));
						var_dump(filesize($file_path));		
						var_dump($v['image']['size']);
			
						var_dumP($v['image']);

						$rescurl = ms_query_image($image_url);

						$fp = fopen($file_path, 'w');
	  		    			fwrite($fp, $rescurl);
	  		
						fclose($fp);
						$tmp['tmp_name'] = $file_path;
						if (file_exists($file_path)) {
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/thumbnail/public/', $v['image']['filename'], 55, 100, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/89x89r/public/', $v['image']['filename'], 89, 89, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/100x150r/public/', $v['image']['filename'], 100, 150, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/210x210r/public/', $v['image']['filename'], 210, 210, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/210x330/public/', $v['image']['filename'], 210, 330, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/300x540r/public/', $v['image']['filename'], 300, 540, 100);
							resizeImage($tmp, $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/styles/300x540r2/public/', $v['image']['filename'], 300, 540, 100);
							mysqli_query($db,"insert into file_managed set uid='39', filename='".$v['image']['filename']."', uri= '".'public://'.$v['image']['filename']."', filemime='image/jpeg', filesize='".$v['image']['size']."', status='1', timestamp='".time()."'");
							$fid = mysqli_insert_id($db);
							if ($fid) {
								list($width, $height, $type, $attr) = getimagesize($tmp);
								mysqli_query($db,"delete from  field_data_field_images where  entity_type='node' and  entity_id='$product_id' and bundle='product'");
								mysqli_query($db,"insert field_data_field_images set entity_type='node', bundle='product', entity_id='$product_id', language='und', field_images_fid='$fid', field_images_width='$width', field_images_height='$height'");
							}
						}


						exit();
					}

				}
*/




				mysqli_query($db,"update node set changed='".time()."' where nid='$product_id'");
				mysqli_query($db,"delete from cache_field where cid='field:node:$product_id'");
				$resp=mysqli_query($db,"select id  from basket_store where   nid='$product_id'");
				list($check)=mysqli_fetch_row($resp);
				if($check) {
					mysqli_query($db,"update  basket_store set 	sclad='$stock_count',sclad_count='$stock_count' where id='$check'");
				}else {
					mysqli_query($db,"insert into  basket_store set sclad='$stock_count',sclad_count='$stock_count', nid='$product_id',sclad='1',order_count='0',weight='0'");
				}

				$res_ms = mysqli_query($db,"select field_product_type_value,field_product_type_revision_id from field_data_field_product_type where entity_id='$product_id' and delta='0'");
				list($price_id,$revision_id) = mysqli_fetch_row($res_ms);


				if(isset($v['description'])) {
					$resp=mysqli_query($db,"select entity_type  from field_data_field_product_descr where   entity_id='$product_id'");
					list($check)=mysqli_fetch_row($resp);
					if($check) {
						mysqli_query($db,"update  field_data_field_product_descr set field_product_descr_value='".addslashes($v['description'])."' where entity_id='$product_id'");
					}
					else mysqli_query($db,"insert into  field_data_field_product_descr set entity_type='node',bundle='product',deleted='0',entity_id='$product_id',revision_id='$product_id',
						language='und',delta='0',field_product_descr_value='".addslashes($v['description'])."', field_product_descr_summary='',field_product_descr_format='full_html'");

					$resp=mysqli_query($db,"select entity_type  from field_revision_field_product_descr where   entity_id='$product_id'");
					list($check)=mysqli_fetch_row($resp);
					if($check) mysqli_query($db,"update  field_revision_field_product_descr set field_product_descr_value='".addslashes($v['description'])."' where entity_id='$product_id'");
					else mysqli_query($db,"insert into  field_revision_field_product_descr set entity_type='node',bundle='product',deleted='0',entity_id='$product_id',revision_id='$product_id',
						language='und',delta='0',field_product_descr_value='".addslashes($v['description'])."', field_product_descr_summary='',field_product_descr_format='full_html'");


				}


				$resp=mysqli_query($db,"select entity_type  from field_data_field_x_count where   entity_id='$price_id'");
				list($check)=mysqli_fetch_row($resp);
//echo("update  field_data_field_x_count set field_x_count_value  ='$xcount' where entity_id='$price_id'<BR>");
				if($check) mysqli_query($db,"update  field_data_field_x_count set field_x_count_value  ='$xcount' where entity_id='$price_id'");
				else {
					mysqli_query($db,"insert into  field_data_field_x_count set field_x_count_value='$xcount',
						entity_type='field_collection_item',bundle='field_product_type',deleted='0',	entity_id='$price_id', revision_id='$revision_id',	language='und',delta='0'");
				}
				
				$resp=mysqli_query($db,"select entity_type  from field_data_field_y_count where   entity_id='$price_id'");
				list($check)=mysqli_fetch_row($resp);
				if($check) mysqli_query($db,"update  field_data_field_y_count set field_y_count_value  ='$ycount' where entity_id='$price_id'");
				else {
					mysqli_query($db,"insert into  field_data_field_y_count set field_y_count_value='$ycount',
						entity_type='field_collection_item',bundle='field_product_type',deleted='0',	entity_id='$price_id', revision_id='$revision_id',	language='und',delta='0'");
				}


				if($price_id){

					mysqli_query($db,"delete from cache_field where cid='field:field_collection_item:$price_id'");

					$resp=mysqli_query($db,"select entity_type  from field_data_field_product_type_count where   entity_id='$price_id'");
					list($check)=mysqli_fetch_row($resp);
					if($check) mysqli_query($db,"update  field_data_field_product_type_count set field_product_type_count_value ='$stock_count' where entity_id='$price_id'");
					else {
						mysqli_query($db,"insert into  field_data_field_product_type_count set field_product_type_count_value='$stock_count',
							entity_type='field_collection_item',bundle='field_product_type',deleted='0',	entity_id='$price_id', revision_id='$revision_id',	language='und',delta='0'");
						}
				


					foreach($PR_NAME as $kpn=>$vpn){

						$resp=mysqli_query($db,"select entity_type  from field_data_field_product_type_price_".$vpn." where   entity_id='$price_id'");
						list($check)=mysqli_fetch_row($resp);

						if(!$PRICES[$vpn]) $PRICES[$vpn]=0;

						if($CURR[$vpn]) $PRICES[$vpn]=ceil($PRICES[$vpn]*$CURR[$vpn]);

						if($check) mysqli_query($db,"update  field_data_field_product_type_price_".$vpn." set field_product_type_price_".$vpn."_value='".$PRICES[$vpn]."' where entity_id='$price_id'");
						else {
							mysqli_query($db,"insert into  field_data_field_product_type_price_".$vpn." set field_product_type_price_".$vpn."_value='".$PRICES[$vpn]."' , entity_id='$price_id', entity_type ='field_collection_item',
							bundle='field_product_type', revision_id='$revision_id', language='und',delta='0'");
						}
				

						$resp=mysqli_query($db,"select entity_type  from field_revision_field_product_type_price_".$vpn."   where entity_id='$price_id'");
						list($check)=mysqli_fetch_row($resp);

						if($check) mysqli_query($db,"update  field_revision_field_product_type_price_".$vpn." set field_product_type_price_".$vpn."_value='".$PRICES[$vpn]."' where entity_id='$price_id'");
						else mysqli_query($db,"insert into  field_revision_field_product_type_price_".$vpn." set field_product_type_price_".$vpn."_value='".$PRICES[$vpn]."' , entity_id='$price_id', entity_type ='field_collection_item',
							bundle='field_product_type', revision_id='$revision_id', language='und',delta='0'");

						if(!$PRICES[$vpn]) {
							mysqli_query($db,"delete from   field_revision_field_product_type_price_".$vpn."  where entity_id='$price_id'");
							mysqli_query($db,"delete from   field_data_field_product_type_price_".$vpn." where entity_id='$price_id'");
						}
/*
					mysqli_query($db,"update  field_data_field_product_type_price_s_opt set field_product_type_price_s_opt_value='".$mopt."' where entity_id='$price_id'");
					mysqli_query($db,"update  field_revision_field_product_type_price_s_opt set field_product_type_price_s_opt_value='".$mopt."' where entity_id='$price_id'");

					mysqli_query($db,"update  field_data_field_product_type_price_l_opt set field_product_type_price_l_opt_value='".$kopt."' where entity_id='$price_id'");
					mysqli_query($db,"update  field_revision_field_product_type_price_l_opt set field_product_type_price_l_opt_value='".$kopt."' where entity_id='$price_id'");

					mysqli_query($db,"update  field_data_field_product_type_price_retail set field_product_type_price_retail_value='".$skprice."' where entity_id='$price_id'");
					mysqli_query($db,"update  field_revision_field_product_type_price_retail set field_product_type_price_retail_value='".$skprice."' where entity_id='$price_id'");
*/

					}
				}


			}

		}
		if (isset($product_id)) {
			ms_query_get('https://b2beauty.store/correct-product/type1/'.$product_id);
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




$QUANTS=array();
$CHECK_MS = true;
$page=0; $limit = 100;
while($CHECK_MS) {
	$offset = $limit*$page;
	$link_stock = "https://online.moysklad.ru/api/remap/1.1/entity/assortment?limit=$limit&offset=$offset&scope=variant";
	$json_stock = ms_query($link_stock);
	foreach($json_stock['rows'] as $k2=>$v2){
		$QUANTS[$v2['id']]=$v2['quantity'];
	}
	$page++;
	if (!count($json_stock['rows']) ) {
		$CHECK_MS = false;
	} 

}
//echo("<pre>");
//var_dump($QUANTS);

$CHECK_MS = true;
$page=0; $limit = 100;
while($CHECK_MS) {
	$offset = $limit*$page;
	$link = "https://online.moysklad.ru/api/remap/1.1/entity/variant?limit=$limit&offset=$offset";
	$json = ms_query($link);
	foreach($json['rows'] as $k => $v) {


		$tmp=explode("/",$v['product']['meta']['href']);
		$ms_id=$tmp[8];
		$stock_count=0;
		if(isset($QUANTS[$ms_id])) $stock_count=$QUANTS[$v['id']];

		if(isset($ALLVAR_COUNT[$ms_id])) $ALLVAR_COUNT[$ms_id]=$ALLVAR_COUNT[$ms_id]+$QUANTS[$v['id']];
		else $ALLVAR_COUNT[$ms_id]=$QUANTS[$ms_id];
//		var_dump($v['name']);


		$PRICES=null;
		foreach($v['salePrices'] as $ks=>$vs){

			 	if($vs['priceType']=='Цена продажи') { $PRICES['ret_old']=$vs['value']/100;  $CURR['ret_old']=$KURS[$vs['currency']['meta']['href']]; }
			 	if($vs['priceType']=='Мелкий опт') {$PRICES['s_opt']=$vs['value']/100; $CURR['s_opt']=$KURS[$vs['currency']['meta']['href']]; }
			 	if($vs['priceType']=='Крупный опт') {$PRICES['l_opt']=$vs['value']/100; $CURR['l_opt']=$KURS[$vs['currency']['meta']['href']]; }
			 	if($vs['priceType']=='Цена со скидкой') {$PRICES['retail']=$vs['value']/100; $CURR['retail']=$KURS[$vs['currency']['meta']['href']]; }


//			 	if($vs['priceType']=='Цена продажи') $PRICES['ret_old']=$vs['value']/100;
///			 	if($vs['priceType']=='Мелкий опт')$PRICES['s_opt']=$vs['value']/100;
///			 	if($vs['priceType']=='Крупный опт')$PRICES['l_opt']=$vs['value']/100;
//			 	if($vs['priceType']=='Цена со скидкой')$PRICES['retail']=$vs['value']/100;
		}

		if(!$PRICES['retail']) { $PRICES['retail']=$PRICES['ret_old']; $PRICES['ret_old']=''; }
		foreach($PRICES as $ks=>$vs){
			if($vs==99999) $PRICES[$ks]=0;
		}
//		var_dump($PRICES);

		$res = mysqli_query($db,"select product_id from ms_products where ms_id='".$ms_id."'");
		list($product_id) = mysqli_fetch_row($res);

		if($product_id){		
				$resp=mysqli_query($db,"select id  from basket_store where   nid='$product_id'");
				list($check)=mysqli_fetch_row($resp);
				if($check) mysqli_query($db,"update  basket_store set 	sclad_count='{$ALLVAR_COUNT[$ms_id]}' where id='$check'");
		}



		$res = mysqli_query($db,"select product_option_id from ms_variants where ms_id='".$v['id']."'");
		list($product_option_id) = mysqli_fetch_row($res);
		if(!$product_option_id) {

			$rj = mysqli_query($db,"select TV.entity_id  from field_data_field_product_type_vendor as TV, field_data_field_product_type as PT where TV.field_product_type_vendor_value='{$v['code']}' and TV.entity_id=PT.field_product_type_value");
			list($product_option_id) = mysqli_fetch_row($rj);
			if($product_option_id) mysqli_query($db,"insert into ms_variants set   product_option_id='$product_option_id' ,ms_id='".$v['id']."'");

		}
		$color='';
		foreach($v['characteristics'] as $kch=>$vch){

		 	$color=$vch['value'];
		}


		if(!$product_option_id) {


			$resf=mysqli_query($db,"select tid from taxonomy_term_data where name='".addslashes($color)."'");

			list($tid)=mysqli_fetch_row($resf);
			if(!$tid){
			 	$rt=mysqli_query($db,"insert into taxonomy_term_data set vid='4',weight='0',name='".addslashes($color)."'");
				$tid=mysqli_insert_id($db);
			}

			$resf=mysqli_query($db,"select entity_type,delta from field_data_field_product_type where entity_id ='$product_id' order by delta desc");
			list($chck,$delta)=mysqli_fetch_row($resf);
			$delta++;
			if($chck){			
				$entity_id=0;
				mysqli_query($db,"insert into field_collection_item set field_name ='field_product_type',revision_id='0',archived='0'");
				$entity_id=mysqli_insert_id($db);

				mysqli_query($db,"insert into field_collection_item_revision set item_id ='$entity_id'");
				$revision_id=mysqli_insert_id($db);

				mysqli_query($db,"insert into field_data_field_product_type_price_color set entity_type='field_collection_item',	bundle='field_product_type',	deleted='0',
					entity_id='$entity_id', revision_id='$revision_id',	language='und', delta='0', field_product_type_price_color_tid='$tid'");

				mysqli_query($db,"insert into field_revision_field_product_type_price_color set entity_type='field_collection_item',	bundle='field_product_type',	deleted='0',
					entity_id='$entity_id', revision_id='$revision_id',	language='und', delta='0', field_product_type_price_color_tid='$tid'");

				echo($entity_id." - $revision_id<BR>");
				mysqli_query($db,"update field_collection_item set revision_id ='$revision_id' where item_id='$entity_id'");

				mysqli_query($db,"insert into  field_data_field_product_type_vendor set  field_product_type_vendor_value='".addslashes($v['code'])."' ,
						entity_type='field_collection_item',	bundle='field_product_type',	deleted	='0',entity_id='$entity_id',	revision_id='$revision_id',
						language='und', delta='0', field_product_type_vendor_format =NULL");

				mysqli_query($db,"insert into  field_data_field_product_type set  entity_type='node',bundle='product',
						deleted='0',entity_id='$product_id',revision_id='$product_id', language	='und',delta='$delta',field_product_type_value='$entity_id',field_product_type_revision_id='$revision_id'");
				mysqli_query($db,"insert into  field_revision_field_product_type set  entity_type='node',bundle='product',
						deleted='0',entity_id='$product_id',revision_id='$product_id', language	='und',delta='0',field_product_type_value='$entity_id',field_product_type_revision_id='$revision_id'");
	

				 mysqli_query($db,"insert into ms_variants set   product_option_id='$product_option_id' ,ms_id='".$v['id']."'");
			
			}
		}

		if($product_option_id){


				mysqli_query($db,"delete from cache_field where cid='field:node:$product_id'");
				mysqli_query($db,"delete from cache_field where cid='field:field_collection_item:$product_option_id'");

				$res_ms = mysqli_query($db,"select field_product_type_value,field_product_type_revision_id from field_data_field_product_type where field_product_type_value='$product_option_id'");
				list($price_id,$revision_id) = mysqli_fetch_row($res_ms);

				if($price_id){

					$resp=mysqli_query($db,"select entity_type  from field_data_field_product_type_count where   entity_id='$price_id'");
					list($check)=mysqli_fetch_row($resp);
					if($check) mysqli_query($db,"update  field_data_field_product_type_count set field_product_type_count_value ='$stock_count' where entity_id='$price_id'");
					else {
						mysqli_query($db,"insert into  field_data_field_product_type_count set field_product_type_count_value='$stock_count',
							entity_type='field_collection_item',bundle='field_product_type',deleted='0',	entity_id='$price_id', revision_id='$revision_id',	language='und',delta='0'");
						}
				


					foreach($PR_NAME as $kpn=>$vpn){
//						var_dump($CURR[$vpn]);
//						var_dump($PRICES[$vpn]);
						if(!$PRICES[$vpn]) $PRICES[$vpn]=0;
						if($CURR[$vpn]) $PRICES[$vpn]=ceil($PRICES[$vpn]*$CURR[$vpn]);

						$resp=mysqli_query($db,"select entity_type  from field_data_field_product_type_price_".$vpn." where   entity_id='$price_id'");
						list($check)=mysqli_fetch_row($resp);

						if($check) mysqli_query($db,"update  field_data_field_product_type_price_".$vpn." set field_product_type_price_".$vpn."_value='".$PRICES[$vpn]."' where entity_id='$price_id'");
						else {
							mysqli_query($db,"insert into  field_data_field_product_type_price_".$vpn." set field_product_type_price_".$vpn."_value='".$PRICES[$vpn]."' , entity_id='$price_id', entity_type ='field_collection_item',
							bundle='field_product_type', revision_id='$revision_id', language='und',delta='0'");
						}
				

						$resp=mysqli_query($db,"select entity_type  from field_revision_field_product_type_price_".$vpn."   where entity_id='$price_id'");
						list($check)=mysqli_fetch_row($resp);

						if($check) mysqli_query($db,"update  field_revision_field_product_type_price_".$vpn." set field_product_type_price_".$vpn."_value='".$PRICES[$vpn]."' where entity_id='$price_id'");
						else mysqli_query($db,"insert into  field_revision_field_product_type_price_".$vpn." set field_product_type_price_".$vpn."_value='".$PRICES[$vpn]."' , entity_id='$price_id', entity_type ='field_collection_item',
							bundle='field_product_type', revision_id='$revision_id', language='und',delta='0'");

						if(!$PRICES[$vpn]) {
							mysqli_query($db,"delete from   field_revision_field_product_type_price_".$vpn."  where entity_id='$price_id'");
							mysqli_query($db,"delete from   field_data_field_product_type_price_".$vpn." where entity_id='$price_id'");
						}
					}
				}

		}
		//Этот запрос на Drupal для коректировки остатков и акций	
		if (isset($product_option_id) && !empty($product_option_id)) {
			ms_query('https://b2beauty.store/correct-product/type2/'.$product_option_id);	
			
			/*if (!isset($product_id_global)) {
				$product_id_global=0;
			}
			//сохраняем айди шаг назад
			//для того чтобы вызывать хук один раз для всех вариаций
			if ($product_id_global!=$product_id) {
				file_get_contents('https://b2beauty.store/correct-product/type2/'.$product_id_global);			
				
			}
			$product_id_global=$product_id;*/
		}

	}
	$page++;
	if (!count($json['rows']) ) {
		$CHECK_MS = false;
	} 
	
}

echo("DONE");