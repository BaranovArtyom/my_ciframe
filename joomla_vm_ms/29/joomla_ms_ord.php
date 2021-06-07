<?php

//date_default_timezone_set('Europe/Moscow');

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
header('Content-Type: text/html; charset=utf-8');
include("joomla_inc.php");

$res = mysqli_query($db, "SELECT orders FROM ms_system");
list($orders) = mysqli_fetch_row($res);
if ($orders > (time() - 60*4)) exit;
else {
	mysqli_query($db, 'UPDATE ms_system SET orders="'.time().'"');
}

$headers = "Content-type: text/html; charset=utf-8 \r\n";
//mail('artem.gorinov83@gmail.com', 'Cron bytdet', 'Начало bytdet order', $headers);


echo date("d.m.Y H:i:s",time())."<br><br>";


$time = time();
$crt = date("Y-m-d H:i:s", time()-3600*24);
$reso = mysqli_query($db, "SELECT order_id, order_number, email, order_date, order_total, phone, f_name, l_name, m_name, state, city, street, home, apartment, zip, order_add_info, shipping_method_id, order_shipping, payment_method_id, firma_name, zip FROM kpfdj_jshopping_orders WHERE order_created='1' AND order_date>'$crt' ORDER BY order_id ASC");
while(list($order_id, $order_number, $email, $date_added, $total, $phone, $fname, $lname, $mname, $state, $city, $street, $home, $apartment, $zip, $order_add_info, $shipping_method_id, $order_shipping, $payment_method_id, $firma_name, $zip) = mysqli_fetch_row($reso)) {

	$name = $adr = $com = '';
	if ($lname != '') $name = $lname;
	if ($fname != '') $name .= ' '.$fname;
	if ($mname != '') $name .= ' '.$mname;
	$name = trim($name);
	if ($email == '') $email = $order_id.'@bytdetali.com';
	if ($state != '') $adr = $state.',';
	if ($city != '') $adr .= ' '.$city.',';
	if ($street != '') $adr .= ' ул.'.$street.',';
	if ($home != '') $adr .= ' д.'.$home.',';
	if ($apartment != '') $adr .= ' кв.'.$apartment;
	$adr = trim($adr);
	$com = $order_add_info;
	$ms_data = null;
	$res = mysqli_query($db, "SELECT ms_id FROM ms_contacts WHERE email='$email'");
	list($ms_id) = mysqli_fetch_row($res);
	$ms_data = array("name" => $name, "phone" => $phone, "email" => $email, "actualAddress" => $adr);
	$send_data = json_encode($ms_data);
	if (!$ms_id && $firma_name == '') {
		$link = 'https://online.moysklad.ru/api/remap/1.1/entity/counterparty';
		$result = ms_query_send($link, $ms_data, 'POST');
		if ($result['meta']['href']) {
			$time = time();
			$link_arr = explode("/", $result['meta']['href']);
			$ms_id = $link_arr[8];
			mysqli_query($db, "INSERT INTO ms_contacts SET ms_id='$ms_id', email='$email'");
		}
	}
	//if ($firma_name == '') $ms_id = '32f61349-dfeb-11e7-7a69-8f5500039723';
	$ms_data = null;
	$ms_data["name"] = $order_number;
	$ms_data["description"] = $com;
	$ms_data["moment"] = $date_added;
	$ms_data["organization"] = array("meta" => array(
		      "href" => "https://online.moysklad.ru/api/remap/1.1/entity/organization/ec3e722d-623c-11e7-7a31-d0fd000a6e46",
		      "type" => "organization",
		      "mediaType" => "application/json"
	));
	$ms_data["owner"] = array("meta" => array(
		      "href" => "https://online.moysklad.ru/api/remap/1.1/entity/employee/ec35186d-623c-11e7-7a31-d0fd000a6e1f",
		      "type" => "employee",
		      "mediaType" => "application/json"
	));		
	$ms_data["agent"] = array("meta" => array(
		      "href"=> "https://online.moysklad.ru/api/remap/1.1/entity/counterparty/$ms_id",
		      "type"=> "counterparty",
		      "mediaType"=> "application/json"
	));
	$ms_data["store"] = array("meta" => array(
		      "href" => "https://online.moysklad.ru/api/remap/1.1/entity/store/d3aacf89-12eb-11e8-9109-f8fc000244ad",
		      "type" => "store",
		      "mediaType" => "application/json"
	));
	/*$shipping = $payment = '';
	$res = mysqli_query($db, "SELECT `name_ru-RU` FROM kpfdj_jshopping_shipping_method WHERE shipping_id='$shipping_method_id'");
	list($shipping) = mysqli_fetch_row($res);
	$res = mysqli_query($db, "SELECT `name_ru-RU` FROM kpfdj_jshopping_payment_method WHERE payment_id='$payment_method_id'");
	list($payment) = mysqli_fetch_row($res);
	$ms_data["attributes"]['0'] = array("id" => "62fc34e0-0596-11e8-6b01-4b1d0005271f", "value" => $shipping);
	$ms_data["attributes"]['1'] = array("id" => "62fc3878-0596-11e8-6b01-4b1d00052720", "value" => $payment);
	if ($firma_name == '') {
		$res = mysqli_query($db, "SELECT country_id FROM lke0q_jshopping_states WHERE `name_ru-RU`='$state'");
		list($country_id) = mysqli_fetch_row($res);
		$res = mysqli_query($db, "SELECT `name_ru-RU` FROM lke0q_jshopping_countries WHERE country_id='$country_id'");
		list($state1) = mysqli_fetch_row($res);
		$ms_data["attributes"]['2'] = array("id" => "3886c2e9-0680-11e8-7a31-d0fd001d26f2", "value" => $name);
		$ms_data["attributes"]['3'] = array("id" => "c3287c5c-0a7c-11e8-7a34-5acf002baa4f", "value" => $zip);
		$ms_data["attributes"]['4'] = array("id" => "b140d58e-0a7c-11e8-6b01-4b1d002cb72b", "value" => $adr);
		$ms_data["attributes"]['5'] = array("id" => "9fafcc91-0a7d-11e8-7a31-d0fd002da82d", "value" => $state1);
		$ms_data["attributes"]['6'] = array("id" => "42ecc9a2-0a7e-11e8-6b01-4b1d002cf034", "value" => $phone);
		$ms_data["attributes"]['7'] = array("id" => "ceec8706-0a7c-11e8-7a34-5acf002bac21", "value" => $state);
		$ms_data["attributes"]['8'] = array("id" => "63b50592-0a7e-11e8-7a31-d0fd002dc6bf", "value" => $email);
	}*/

	$s_comm = NULL;
	$res = mysqli_query($db, "SELECT product_id, product_quantity, product_item_price, attributes FROM kpfdj_jshopping_order_item WHERE order_id='$order_id' ORDER BY order_item_id ASC");
	while(list($product_id, $product_quantity, $product_item_price, $attributes) = mysqli_fetch_row($res)) {
		$attr_id = 0;
		if ($attributes != '') {
			$attributes = unserialize($attributes);
			if (isset($attributes) && count($attributes) > 0) {
				$wh = '';
				foreach($attributes as $k => $v) {
					$wh = " AND attr_".$k."='$v'";
				}
				$resa = mysqli_query($db, "SELECT product_attr_id, count, price FROM kpfdj_jshopping_products_attr WHERE product_id='$product_id'$wh");
				list($attr_id, $product_quantity, $product_item_price) = mysqli_fetch_row($resa);
			}
		}
		$ms_pr_id = '';
		if ($attr_id > 0) $resm = mysqli_query($db, "SELECT ms_id FROM ms_products WHERE product_id='$product_id' AND attr_id='$attr_id'");
		else $resm = mysqli_query($db, "SELECT ms_id FROM ms_products WHERE product_id='$product_id'");
		list($ms_pr_id) = mysqli_fetch_row($resm);
		if ($ms_pr_id != '') {
			$s_comm[] = array(
					"quantity" => (int)$product_quantity,
					"price" => (int)$product_item_price*100,
					"discount" => 0,
				    	"vat" => 0,
					"assortment" => array("meta" => array(
						"href" => "https://online.moysklad.ru/api/remap/1.1/entity/product/".$ms_pr_id, 
						"type" => "product",
						"mediaType" => "application/json"
					)),
					"reserve" => (int)$product_quantity,
			);
		}
	}
	/*$order_shipping = (int) $order_shipping;
	if ($order_shipping > 0) {
		$s_comm[] = array(
					"quantity" => 1,
					"price" => $order_shipping*100,
					"discount" => 0,
				    	"vat" => 0,
					"assortment" => array("meta" => array(
						"href" => "https://online.moysklad.ru/api/remap/1.1/entity/service/faa41f38-0599-11e8-7a34-5acf0005ed26", 
						"type" => "service",
						"mediaType" => "application/json"
					)),
		);
	}*/
	$ms_data["positions"] = $s_comm;

	$res = mysqli_query($db, "SELECT ms_id FROM ms_leads WHERE order_id='$order_id'");
	list($ms_lead_id) = mysqli_fetch_row($res);
	if (!$ms_lead_id) {
		$link = 'https://online.moysklad.ru/api/remap/1.1/entity/customerorder';
		$result = ms_query_send($link, $ms_data, 'POST');
		//print_r($result);
		if ($result['meta']['href']) {
			$time = time();
			$link_arr = explode("/",$result['meta']['href']);
			$ms_lead_id = $link_arr[8];
			mysqli_query($db, "INSERT INTO ms_leads SET ms_id='$ms_lead_id', order_id='$order_id'");
		}
	} elseif ($ms_lead_id != '') {

	}

	echo "<b>$order_id</b> = $ms_lead_id, $email = $ms_id<br><br>";
}

echo date("d.m.Y H:i:s",time())."<br><br>";

?>

