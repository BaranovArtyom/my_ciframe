<?php

ini_set('display_errors', 1);
// require_once "order.php";
require_once "funcs.php";
require_once "config.php";

dd($OrderMs['name'] );
dd($getIdVmOrder);

// $OrderMs['name'] = 'D5YF020693'; // номер заказа
// $getIdVmOrder = 26690;			// id заказа

// $getIdVmOrder =  mysqli_fetch_row(mysqli_query($db,"SELECT virtuemart_order_id FROM `rtd_virtuemart_orders` WHERE `order_number` = '{$OrderMs['name']}' "))[0];
// echo (float)$getTotalSumm['7'];

function textMessage($name,$number_order,$pass,$sum_order,$total_sum_order,$order_status_name,$order_status_desc,$num_delivery,
$name_ship,$getSumshipment, $name_pay, $pay_sum, $items1,$get_order_salesPrice,$get_order_total,$to,$last_name,$first_name,$city,$address,$phone,$descp){

	$total_sum_order = intval($total_sum_order);
	$total_sum_order = str_replace(',',' ',number_format($total_sum_order));

	$get_order_salesPrice = intval($get_order_salesPrice);
	$get_order_salesPrice = str_replace(',',' ',number_format($get_order_salesPrice));
	
	$getSumshipment = intval($getSumshipment);
	$getSumshipment = str_replace(',',' ',number_format($getSumshipment));

	$pay_sum = intval($pay_sum);
	$pay_sum = str_replace(',',' ',number_format($pay_sum));

	$get_order_total = intval($get_order_total);
	$get_order_total = str_replace(',',' ',number_format($get_order_total));


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
				<strong>Приветствуем,  '.$name.'</strong><br>
			</td>
		</tr>
		<tr><td colspan="2" style="padding:5px"></td></tr>
	</tbody></table>

	</span><table width="100%" border="0" cellpadding="0" cellspacing="0">

	<tbody><tr>
		<td width="30%">
			Номер Вашего заказа: <br>
			<strong>'.$number_order.'</strong>

		</td>
		<td width="30%">
			Пароль Вашего заказа: <br>
			<strong>'.$pass.'</strong>
		</td>
		<td width="40%">
			<p>

			</p>
		</td>
	</tr>
	<tr>
		<td colspan="3"><p>
					</p><p><b>Полная сумма Вашего заказа: '.$total_sum_order.' руб</b></p><p></p></td>
	</tr>
		<tr>
	<td colspan="3"><p>
					
	</p><p>Состояние Вашего заказа — '.$order_status_name.'</p><span class="im">
	<hr>
	<p><b>Итого к оплате (сумма заказа с доставкой) - '.$total_sum_order.' руб</b></p>
	</span><p></p>'.$order_status_desc.'<p></p><p><b>'.$num_delivery.'</b></p><p></p></td>
	</tr>
	<tr>
    <td colspan="3">
		Ваш комментарий: <br> '.$descp.'</td>
  	</tr>
	
	</tbody></table>
		<br>

		<table cellspacing="0" cellpadding="0" border="0" width="100%">  <tbody><tr>

		<td width="50%">
			Детали оплаты	</td>
		
		</tr>
		<tr>
		<td valign="top" width="50%">
	
			
					<span><a href="mailto:'.$to.'" target="_blank">'.$to.'</a></span>
								<br>
					
					<span>'.$name.'</span><br>
					<span>'.$last_name.'</span><br>
					<span>'.$first_name.'</span><br>
					<span>'.$city.'</span><br>
					<span>'.$address.'</span><br>
					<span>'.$phone.'</span><br>
				
					
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
			$product_final_price = intval($val['product_final_price']);
			$product_final_price = str_replace(',',' ',number_format($product_final_price));

			$product_subtotal_discount = intval($val['product_subtotal_discount']);
			$product_subtotal_discount = str_replace(',',' ',number_format($product_subtotal_discount));

			$sum = intval($val['sum']);
			$sum = str_replace(',',' ',number_format($sum));
			$atr = $val['attr'];

			$mes .= '   
			<tr style="vertical-align:top">
				<td align="left" style="border:1px solid #cccccc">'. $val['order_item_sku'].'</td>
				<td align="left" style="border:1px solid #cccccc" colspan="2">
					<div><a href="https://readytodirt.ru/index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_category_id=40&amp;virtuemart_product_id='.$val['virtuemart_product_id'].'" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://readytodirt.ru/index.php?option%3Dcom_virtuemart%26view%3Dproductdetails%26virtuemart_category_id%3D39%26virtuemart_product_id%3D6076%26Itemid%3D508&amp;source=gmail&amp;ust=1616249098452000&amp;usg=AFQjCNFsDeNiy-xFfsY_qQgAEAt-gqHbTQ">'.$val['order_item_name'].'</a>
					
					</div>
					<div>';
					foreach($atr as $v) :
						$mes .= '
						<span>'.$v.'</span>
						<br>';
					endforeach; 
				$mes .=
					'</div>		
				</td>
				<td nowrap="" align="right" style="border:1px solid #cccccc" colspan="2"> '.$product_final_price.' руб</td>
				<td align="right" style="border:1px solid #cccccc">	'.$val['product_quantity'].'	</td>
				<td align="right" style="border:1px solid #cccccc">	'.$product_subtotal_discount.' руб		</td>
				<td align="right" style="border:1px solid #cccccc">		'.$sum	.'  руб	</td>
	
	
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
			<td align="right" style="border:1px solid #cccccc"> '.$pay_sum.' руб</td>
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

function subjectText($name_order, $total_sum_order,$text_sub) {

	$total_sum_order = intval($total_sum_order);
	$total_sum_order = str_replace(',',' ',number_format($total_sum_order));

	$text = "[".$name_order."], ".$text_sub.$total_sum_order." руб, ReadyToDirt.ru - мотоэкипировка и запчасти";
	return $text;
}


