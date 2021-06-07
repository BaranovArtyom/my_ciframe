<?php

//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);
//ini_set('memory_limit', '512M');
//ini_set('max_execution_time', '300');
header("Content-Type: text/html;charset=UTF-8");
include("drupal_inc.php");

$headers = "Content-type: text/html; charset=utf-8 \r\n";
//mail('artem.gorinov83@gmail.com', 'Cron runbo', 'runbo product', $headers);

$link = "https://online.moysklad.ru/api/remap/1.1/entity/product";
$json = ms_query($link);
$good_count = 0;
$ff = fopen('now', "r");
$page = fread($ff, 1000);
fclose($ff);
$good_limit = $page+3;
if ($json['meta']['size'] < ($page*100)) {
	$good_limit=3;
	$page=0;
}
if (!$page) $page = 0;
$CHECK_MS = true;
$limit = 100;
while($CHECK_MS) {
	$offset = $limit*$page;
	$link = "https://online.moysklad.ru/api/remap/1.1/entity/product?limit=$limit&offset=$offset";
	$json = ms_query($link);
	foreach($json['rows'] as $k => $v) {

		$res_gr = ms_query($v['productFolder']['meta']['href']);
		$saleprice = $v['salePrices'][0]['value']/100;
		$stock = $stock_count = 0;
		$link_stock = "https://online.moysklad.ru/api/remap/1.1/report/stock/bystore?limit=10&offset=0&product.id=".$v['id'];
		$json_stock = ms_query($link_stock);
		if (count($json_stock['rows'][0]['stockByStore']) > 0) foreach($json_stock['rows'][0]['stockByStore'] as $ks => $vs) {
			if ($vs['name'] == 'Интернет-магазин' && $vs['stock'] > 0 && $vs['stock'] != $vs['reserve']) {
				$stock = 1;
				$stock_count = $vs['stock'];
			}
		}

		// Fresh
		if (isset($v['code']) && $v['code'] != '') {
			$res_fr = mysql_query("select id from fresh_products where code='".$v['code']."'");
			list($fid) = mysql_fetch_row($res_fr);
			if (!$fid) {
				$fr_data = null;
				$fr_data = array(array(
					'code' => $v['code'],
					'artk' => $v['article'],
					'name' => $v['name'],
					'group' => $res_gr['name'],
					'unit' => 1,
					'dsc' => $v['description'],
					'unitsDsc' => array(array(
						'unitType' => 1,
						'exponent' => 1,
						'unitCount' => 1,//$stock_count,
						'price' => $saleprice,
					)),
					'palletsDsc' => array(array(
						'palletType' => 1,
						'unitsPerPallet' => 2560,
					)),
					'barcodes' => array(array(
						'unitType' => '1',
						'barcode' => $v['barcodes'][0],
					)),
				));
				$fr_link = 'http://it.fresh-logic.ru/api/v1/nomenclature/import';
				$result = fresh_query_send($fr_link, $fr_data, 'POST');
				if (!isset($result['Message'])) {
					mysql_query("insert into fresh_products set code='".$v['code']."'");
				}
			}
		}

		if (isset($v['name'])) {
			$res = mysql_query("select product_id from ms_products where ms_id='".$v['id']."'");
			list($product_id) = mysql_fetch_row($res);
			if (!$product_id) {
				$rj = mysql_query("select nid from node where title='".$v['name']."'");
				list($product_id) = mysql_fetch_row($rj);
			}
			$rj = mysql_query("select  from node where title='".$v['name']."'");
			list($product_type) = mysql_fetch_row($rj);
			$resx = mysql_query("select site_id from ms_cats where ms_id='{$res_gr['id']}'");
			list($site_id) = mysql_fetch_row($resx);

			if ($product_id) {

				$res_ms = mysql_query("select nid from node_revision where nid='$product_id'");
				list($rid) = mysql_fetch_row($res_ms);
				if (!$rid) {
					mysql_query("insert into node_revision set nid='".$product_id."', vid='".$product_id."', title='".$v['name']."', uid='39', status='1', timestamp='".time()."'");
				} else {
					mysql_query("update node_revision set title='".$v['name']."', timestamp='".time()."' where nid='".$product_id."'");
				}
				$res_ms = mysql_query("select id from ms_products where product_id='$product_id'");
				list($chid) = mysql_fetch_row($res_ms);
				if ($chid) {
					mysql_query("update ms_products set del='0' where product_id='$product_id'");
				} else {
					mysql_query("insert into ms_products set xmlId='".$v['externalCode']."', product_id='$product_id', ms_id='".$v['id']."', del='0'");
					}
				mysql_query("update node set title='".$v['name']."', changed='".time()."' where nid='".$product_id."'");
				mysql_query("update field_data_field_price set field_price_value='".$saleprice."' where entity_id='$product_id'");
				mysql_query("update field_revision_field_price set field_price_value='".$saleprice."' where entity_id='$product_id'");
				$res_ms = mysql_query("select entity_id from field_data_field_nal where entity_id='$product_id'");
				list($nalid) = mysql_fetch_row($res_ms);
				if (!$nalid) {
					mysql_query("insert into field_data_field_nal set entity_type='node', bundle='".$product_type."', entity_id='".$product_id."', revision_id='".$product_id."', language='und', field_nal_value='".$stock."'");
					mysql_query("insert into field_revision_field_nal set entity_type='node', bundle='".$product_type."', entity_id='".$product_id."', revision_id='".$product_id."', language='und', field_nal_value='".$stock."'");
				} else {
					mysql_query("update field_data_field_nal set field_nal_value='".$stock."' where entity_id='".$product_id."'");

					mysql_query("update field_revision_field_nal set field_nal_value='".$stock."' where entity_id='".$product_id."'");
				}
				
				mysql_query("update field_data_field_product_export_to_mailru set field_product_export_to_mailru_value='".$stock."' where entity_id='".$product_id."'");

				mysql_query("update field_revision_field_product_export_to_mailru set field_product_export_to_mailru_value='".$stock."' where entity_id='".$product_id."'");
				
				mysql_query("update field_data_field_product_export_to_market set field_product_export_to_market_value='".$stock."' where entity_id='".$product_id."'");

				mysql_query("update field_revision_field_product_export_to_market set field_product_export_to_market_value='".$stock."' where entity_id='".$product_id."'");
				
echo "1 --- $product_id --- $site_id --- {$v['name']} --- $saleprice --- $stock<br>";//exit;
				if (isset($v['image']['meta']['href'])) {
					$image_url = $v['image']['meta']['href'];
					$topath = $_SERVER['DOCUMENT_ROOT'].'/sites/default/files/';
					$file_path = $topath.$v['image']['filename'];
					if (!file_exists($file_path)) {// || (filesize($file_path) != $v['image']['size'])
echo "1img ---- {$v['name']} --- $fid --- {$v['image']['title']} --- {$v['image']['filename']}<br>";continue;
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
						}
						$res_ms = mysql_query("select field_tovar_photos_fid from field_data_field_images where entity_id='$product_id'");
						list($fid) = mysql_fetch_row($res_ms);
						if ($fid) {
							mysql_query("update file_managed set filename='".$v['image']['filename']."', uri= '".'public://'.$v['image']['filename']."' where fid='$fid'");
						} else {
							mysql_query("insert into file_managed set uid='39', filename='".$v['image']['filename']."', uri= '".'public://'.$v['image']['filename']."', filemime='image/jpeg', filesize='".$v['image']['size']."', status='1', timestamp='".time()."'");
							$fid = mysql_insert_id();
							if ($fid) {
								list($width, $height, $type, $attr) = getimagesize($tmp);
								mysql_query("insert field_data_field_images set entity_type='node', bundle='product', entity_id='$product_id', language='und', field_images_fid='$fid', field_images_width='$width', field_images_height='$height'");
							}
						}
					}
					$res_ms = mysql_query("select fid from file_managed where filename='".$v['image']['filename']."'");
					list($fid1) = mysql_fetch_row($res_ms);
					$res_ms = mysql_query("select field_tovar_photos_fid from field_data_field_tovar_photos where entity_id='$product_id'");
					list($fid2) = mysql_fetch_row($res_ms);
					if (file_exists($file_path) && $fid1 && !$fid2) {
						if ($fid1) {
							list($width, $height, $type, $attr) = getimagesize($tmp);
							mysql_query("insert field_data_field_images set entity_type='node', bundle='product', entity_id='$product_id', language='und', field_images_fid='$fid', field_images_width='$width', field_images_height='$height'");
						}
					}
				}

			} else {
echo "2 --- $product_id --- $site_id --- {$v['name']}<br>";continue;
				mysql_query("insert into node set type='product', language='ru', title='".$v['name']."', uid='1', status='1', created='".time()."', changed='".time()."'");
				$product_id = mysql_insert_id();
				mysql_query("insert into node_revision set nid='".$product_id."', vid='".$product_id."', title='".$v['name']."', uid='1', status='1', timestamp='".time()."'");
				mysql_query("update node set vid='".$product_id."' where nid='".$product_id."'");
				mysql_query("insert into field_data_field_price set entity_type='node', bundle='product', entity_id='$product_id', revision_id='$product_id', language='und', field_price_value='$saleprice'");
				mysql_query("insert into field_revision_field_price set entity_type='node', bundle='product', entity_id='$product_id', revision_id='$product_id', language='und', field_price_value='$saleprice'");
				mysql_query("insert into field_data_field_nal set set entity_type='node', bundle='product', entity_id='".$product_id."', revision_id='".$product_id."', language='und', field_nal_value='".$stock."'");
				mysql_query("insert into field_revision_field_nal set set entity_type='node', bundle='product', entity_id='".$product_id."', revision_id='".$product_id."', language='und', field_nal_value='".$stock."'");
				mysql_query("insert into ms_products set xmlId='".$v['externalCode']."', product_id='$product_id', ms_id='".$v['id']."', del='0'");
				if (isset($v['image']['meta']['href'])) {
echo "2img ---- $fid --- {$v['image']['title']} --- {$v['image']['filename']}";exit;
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
						mysql_query("insert into file_managed set uid='39', filename='".$v['image']['filename']."', uri= '".'public://'.$v['image']['filename']."', filemime='image/jpeg', filesize='".$v['image']['size']."', status='1', timestamp='".time()."'");
						$fid = mysql_insert_id();
						if ($fid) {
							list($width, $height, $type, $attr) = getimagesize($tmp);
							mysql_query("insert field_data_field_images set entity_type='node', bundle='product', entity_id='$product_id', language='und', field_images_fid='$fid', field_images_width='$width', field_images_height='$height'");
						}
					}
				}
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
mysql_query("truncate cache_field");

// MS + Fresh move
$CHECK_MS = true;
$limit = 100;
$page = 0;
while($CHECK_MS) {
	$offset = $limit*$page;
	$link = "https://online.moysklad.ru/api/remap/1.1/entity/move?limit=$limit&offset=$offset";
	$json = ms_query($link);
	foreach($json['rows'] as $k => $v) {
		$sourceStore = @explode("/", $v['sourceStore']['meta']['href']);
		$sourceStore = $sourceStore[8];
		$targetStore = @explode("/", $v['targetStore']['meta']['href']);
		$targetStore = $targetStore[8];
		//if ($sourceStore == 'f93fc3b5-1b05-11e7-7a31-d0fd00168033' && $targetStore == 'f3f0d540-738a-11e7-7a34-5acf0009eebb') {
			if ($targetStore == 'f3f0d540-738a-11e7-7a34-5acf0009eebb') {
			$res_fr = mysql_query("select id from fresh_move where ms_id='".$v['id']."'");
			list($fid) = mysql_fetch_row($res_fr);
			if (!$fid) {
				$res_pos = ms_query($v['positions']['meta']['href']);
				$items = null; $ip = 0;
				foreach($res_pos['rows'] as $kp => $vp) {
					$res_ass = ms_query($vp['assortment']['meta']['href']);
					$saleprice = $res_ass['salePrices'][0]['value']/100;
					$items[$ip] = array(
						'code' => $res_ass['code'],
						'price' => $saleprice,
						'count' => $vp['quantity'],
						'unit' => 1,
						'itemCondition' => 1,
					);
					$ip++;
				}
				$inDate = strtotime($v['moment']);
				$fr_data = null;
				$fr_data = array(
					'number' => $v['name'],
					'type' => 1,
					'inDate' => date("d.m.Y", $inDate),
					'items' => $items,
					'warehouseTo' => 350,
				);
				$fr_link = 'http://it.fresh-logic.ru/api/v1/acceptances/create';
				$result = fresh_query_send($fr_link, $fr_data, 'POST');
				if (!isset($result['Message'])) {
					mysql_query("insert into fresh_move set name='".$v['name']."', ms_id='".$v['id']."'");
				}
			}
		}
	}
	$page++;
	if (!count($json['rows'])) $CHECK_MS = false;
}
if (isset($json)) unset($json);

?>