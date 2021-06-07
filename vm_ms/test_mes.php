<?php 

ini_set('display_errors', 1);
// require_once "order.php";
require_once "funcs.php";
require_once "config.php";

// $email = 'kulemin_d@mail.ru'; 
$to = 'kulemin_d@mail.ru'; 
$get_order_salesPrice  = 2890.00000;
$getSumshipment = 350;
$get_order_total  = 3150;
// $OrderMs['name'] = "FIZV020652";


$OrderMs['name'] = "S9YY020814";
$getIdVmOrder =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_order_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
$getIdshipmethod =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_shipmentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
$getIdpaymethod = mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_paymentmethod_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];

dd($getIdpaymethod);
dd($getIdVmOrder);
// exit;
// dd($getIdshipmethod);exit;
// $getIdshipmethod = 7;
if ($getIdshipmethod == 90 or $getIdshipmethod == 66 or $getIdshipmethod == 68) {
    $name_ship = $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT shipment_name FROM `rtd_virtuemart_shipment_plg_cdek_pickup` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
    // dd($name_ship);exit;
}else {
    $getTextShip = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_shipment_plg_weight_countries` WHERE `order_number` = '{$OrderMs['name']}' "));
    $name_ship = $getTextShip[4];
    // $sum_ship =  $getTextShip[7];

}


    // dd($name_ship);exit;
if ($getIdpaymethod == 4) {
    $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_yandexapi` WHERE `order_number` = '{$OrderMs['name']}' "));
    $name_pay = $getTextPay[4];
    $pay_sum = $getTextPay[5];
}else {
    $getTextPay = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_payment_plg_standard` WHERE `order_number` = '{$OrderMs['name']}' "));
    $name_pay = $getTextPay[5];
    $pay_sum = $getTextPay[6];
}

$getItems = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' ");
$it = array();
foreach ($getItems as $item){
    // dd($item);
    $item1 = array();
    $item1['order_item_name'] = $item['order_item_name'];
    $item1['order_item_sku'] = $item['order_item_sku'];
    $item1['product_item_price'] = $item['product_item_price'];
    $item1['product_final_price'] = $item['product_final_price'];
    $item1['product_subtotal_discount'] = $item['product_subtotal_discount'];
    $item1['virtuemart_product_id'] = $item['virtuemart_product_id'];
    $item1['product_quantity'] = $item['product_quantity'];
    // $item1['product_quantity'] = 2;

    $item1['sum'] = $item1['product_quantity']*$item1['product_final_price'];
    
    
    $it[] = $item1;
}

// $getItems = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_order_id` = '$getIdVmOrder' "));
dd($it);
exit;
// dd($getItems);
// dd($getTextPay);
// dd($name_pay);
// dd($pay_sum);
$name = 'sasha';
dd($name_ship);
dd($sum_ship);
// function textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery){
function textMessage($name,$name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to){
$mes = '<div bgcolor="#EEEEEE" style="margin:0;padding:15px;background-color:#eeeeee;min-height:100%">
	    <table bgcolor="#FFFFFF" width="100%" cellpadding="10" cellspacing="0" align="center" style="border-collapse:collapse;font-family:Arial,Helvetica,sans-serif;font-size:12px;margin:0 auto;border:1px solid #cccccc">
	    	<tbody><tr><td><span class="im">
			<table width="100%" border="0" cellpadding="5" cellspacing="0" style="border-collapse:collapse;font-family:Arial,Helvetica,sans-serif;font-size:12px;margin:0 auto">
		<tbody><tr>
					<td width="15%"><img src="https://ci4.googleusercontent.com/proxy/vqWAgsywPuD72E6D5QeDFhflAE2Huq1GrTiKiSOKjJJgRzchOv2V1OcwfQ_aPX-VJFv6tgL0sTQBeeBtHZF9k2NhjpQ-X07ne41XGGuRGJ3rt2fopGYMBEfFg3k=s0-d-e1-ft#https://readytodirt.ru/images/stories/virtuemart/vendor/logo-sitepng6.png" style="width:30mm" class="CToWUd"></td>
			<td colspan="1" width="50%">
					<div id="m_-4783675492367914966vmdoc-header" style="font-size:7pt">
			<h1><span style="font-family:Arial,Helvetica,sans-serif;line-height:normal">&nbsp;ReadyToDirt.ru - мотоэкипировка и запчасти</span></h1>			</div>
		</td>
	</tr>
		<tr><td colspan="2" width="100%"></td></tr>
				
		<tr>
		<td colspan="2">
			<strong>Приветствуем,  TESTciframe.com <a href="http://ciframe.com" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://ciframe.com&amp;source=gmail&amp;ust=1616249098452000&amp;usg=AFQjCNHips65Voh7zR56XLXUUn8DBFvV5A">ciframe.com</a>,</strong><br>
		</td>
	</tr>
	<tr><td colspan="2" style="padding:5px"></td></tr>
</tbody></table>

</span><table width="100%" border="0" cellpadding="0" cellspacing="0">

  <tbody><tr>
    <td width="30%">
		Номер Вашего заказа: <br>
		<strong>D5YF020693</strong>

	</td>
    <td width="30%">
		Пароль Вашего заказа: <br>
		<strong>p_FGYKjgUT</strong>
	</td>
    <td width="40%">
    	<p>

		</p>
	</td>
  </tr>
  <tr>
    <td colspan="3"><p>
				</p><p><b>Полная сумма Вашего заказа: 7 282 руб</b></p><p></p></td>
  </tr>
  	<tr>
  <td colspan="3"><p>
				 
</p><p>Состояние Вашего заказа — Заказ подтвержден. Ожидаем оплату (Тинькофф)</p><span class="im">
<hr>
<p><b>Итого к оплате (сумма заказа с доставкой) - 7 282 руб</b></p>
</span><p></p><p>Оплата на карту Тинькофф:</p>
<p><br>Номер карты: 5536 9138 0621 1344<br>Владелец: Раменев Игорь Станиславович</p><div><div class="adm"><div id="q_8" class="ajR h4"><div class="ajT"></div></div></div><div class="h5"><br>Карта привязана к номеру 89095945992 (для системы быстрых платежей)</div></div><p></p><p></p><p></p></td>
  </tr>
    

  
    </tbody></table><div><div class="adm"><div id="q_6" class="ajR h4"><div class="ajT"></div></div></div><div class="h5">	<hr>
	<br>
<table cellspacing="0" cellpadding="0" border="0" width="100%">  <tbody><tr>

	<td width="50%">
	    Детали оплаты	</td>
	
    </tr>
    <tr>
	<td valign="top" width="50%">

	    
	    	    <span><a href="mailto:'.$to.'" target="_blank">'.$to.'</a></span>
						    <br>
			    
	    	   
			    
	    	    <span>'.$name.'</span>
			
	    	    
			
			    
	</td>
	
    </tr>
</tbody></table>
	<br>

<hr>


<table width="100%" cellspacing="0" cellpadding="5" border="0" style="border-collapse:collapse;margin:0 auto;font-family:Arial,Helvetica,sans-serif;font-size:12px">
	<tbody><tr style="text-align:left">
		<th align="left" bgcolor="#EEEEEE" style="border:1px solid #cccccc">Артикул</th>
		<th align="center" bgcolor="#EEEEEE" colspan="2" style="width:45%;border:1px solid #cccccc">Название товара</th>
				<th align="center" bgcolor="#EEEEEE" colspan="2" style="border:1px solid #cccccc">Цена</th>
				<th align="center" bgcolor="#EEEEEE" style="border:1px solid #cccccc">Кол-во</th>
							<th align="center" bgcolor="#EEEEEE" style="border:1px solid #cccccc">Скидка</th>
			
					<th align="right" bgcolor="#EEEEEE" style="border:1px solid #cccccc">Сумма</th>
		
	</tr>';

     foreach($items1 as $val) :
        // print_r($val);
        $mes .= '   
        <tr style="vertical-align:top">
            <td align="left" style="border:1px solid #cccccc">'. $val['order_item_sku'].'</td>
            <td align="left" style="border:1px solid #cccccc" colspan="2">
			    <div><a href="https://readytodirt.ru/index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_category_id=40&amp;virtuemart_product_id='.$val['virtuemart_product_id'].'" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://readytodirt.ru/index.php?option%3Dcom_virtuemart%26view%3Dproductdetails%26virtuemart_category_id%3D39%26virtuemart_product_id%3D6076%26Itemid%3D508&amp;source=gmail&amp;ust=1616249098452000&amp;usg=AFQjCNFsDeNiy-xFfsY_qQgAEAt-gqHbTQ">'.$val['order_item_sku'].' - '.$val['order_item_name'].'</a></div>
			<div></div>		</td>
            <td nowrap="" align="right" style="border:1px solid #cccccc" colspan="2"> '.intval($val['product_final_price']).' руб</td>
            <td align="right" style="border:1px solid #cccccc">	'.$val['product_quantity'].'	</td>
            <td align="right" style="border:1px solid #cccccc">	'.intval($val['product_subtotal_discount']).' руб		</td>
            <td align="right" style="border:1px solid #cccccc">		'.intval($val['sum'])	.'  руб	</td>


        </tr>';
     endforeach; 
        

     $mes .='

	<tr>
		<td colspan="6" align="right" style="border:1px solid #cccccc">Итого</td>
				<td align="right" style="border:1px solid #cccccc"><span> 0 руб</span></td>
		<td align="right" style="border:1px solid #cccccc">'.$get_order_salesPrice.' руб</td>
		
		
	</tr>

	
	
	<tr>
		<td align="right" style="border:1px solid #cccccc" colspan="6">'.$name_ship.'</td>
				<td align="right" style="border:1px solid #cccccc">&nbsp;</td>
		<td align="right" style="border:1px solid #cccccc">'.$getSumshipment.' руб</td>
	</tr>

	<tr>
		<td align="right" style="border:1px solid #cccccc" colspan="6">'.$name_pay.'</td>
				<td align="right" style="border:1px solid #cccccc">&nbsp;</td>
		<td align="right" style="border:1px solid #cccccc"> '.intval($pay_sum).' руб</td>
	</tr>

	<tr>
		<td align="right" style="border:1px solid #cccccc" colspan="6"><strong>Сумма</strong></td>
				<td align="right" style="border:1px solid #cccccc"><span>0 руб</span></td>
		<td align="right" style="border:1px solid #cccccc"><strong>'.$get_order_total.' руб</strong></td>
	</tr>

			<tr>
			<td colspan="7" align="right" style="border:1px solid #cccccc">Налог включает: </td>
						<td align="left" style="border:1px solid #cccccc">&nbsp;</td>
		</tr>				<tr>
					<td colspan="6" align="right" style="border:1px solid #cccccc">НДС </td>
										<td align="right" style="border:1px solid #cccccc">&nbsp;</td>
					<td align="right" style="border:1px solid #cccccc">&nbsp;</td>
				</tr>
				</tbody></table>

<br><br>Спасибо за покупку на <a href="https://readytodirt.ru/index.php?" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://readytodirt.ru/index.php?&amp;source=gmail&amp;ust=1616249098453000&amp;usg=AFQjCNGga_X8R4A9v69Wh3n0JtvpT2dkZw">Readytodirt.ru</a><br>		    </div></div></td></tr>
	    </tbody></table><div class="yj6qo"></div><div class="adL">
</div></div>';

return $mes;
}

$mail = textMessage($name ,$name_ship,$getSumshipment,$name_pay,$pay_sum,$items1,$get_order_salesPrice,$get_order_total,$to);
echo $mail ;