<?$updfrom=date("Y-m-d%20H:i:s",time()-(3600*5));
$link = "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/?expand=state&limit=100&updatedFrom=".$updfrom;
$result=ms_query($link);
if(isset($result['rows']))  foreach($result['rows'] as $k=>$v){
	$stmt = $pdo->prepare("SELECT order_id FROM ms_leads WHERE ms_id='{$v['id']}'");
	$stmt->execute();
	list($order_id) = $stmt->fetch(PDO::FETCH_LAZY);
//	$stmt = $pdo->prepare("select order_status from c9o6s_virtuemart_orders  where virtuemart_order_id='$order_id'");
	$stmt = $pdo->prepare("SELECT VO.order_status,VO.order_number, VU.email, VU.first_name, VU.last_name, VU.middle_name, VU.gorod, VU.address_1, VU.customer_note, VO.virtuemart_shipmentmethod_id, VO.order_shipment , VU.customer_note3, VU.zip FROM c9o6s_virtuemart_orders AS VO, c9o6s_virtuemart_order_userinfos AS VU WHERE VO.virtuemart_order_id=VU.virtuemart_order_id AND VO.virtuemart_order_id='$order_id' ORDER BY VO.virtuemart_order_id  ");
	$stmt->execute();
	list($old_stat,$ordnum,$to,$fname,$lname) = $stmt->fetch(PDO::FETCH_LAZY);
	$stat='';
/*
	if($v['state']['name']=='Новый') $stat='X';	
	if($v['state']['name']=='Отменен') $stat='X';	
	if($v['state']['name']=='Ожидает оплату') $stat='X';	
	if($v['state']['name']=='Нужна корректировка') $stat='X';	
	if($v['state']['name']=='Оплачен') $stat='X';	
	if($v['state']['name']=='На сборке') $stat='X';	
	if($v['state']['name']=='На сборке самовывоз') $stat='X';	
	if($v['state']['name']=='Собран') $stat='X';	
	if($v['state']['name']=='Собран самовывоз') $stat='X';	
	if($v['state']['name']=='На вязании') $stat='X';	
	if($v['state']['name']=='Отправлен') $stat='X';	
	if($v['state']['name']=='Выдан Самовывоз') $stat='X';	
Отменен в МС = Отменен в Джумле
Ожидает оплату в МС = Ожидает оплату в Джумле
Нужна корректировка в МС = Нужна корректировка в Джумле
Оплачен в МС = На сборке в Джумле
На сборке в МС = На сборке в Джумле
На сборке самовывоз в МС = На сборке в Джумле
Собран в МС = Собран в Джумле
Собран самовывоз в МС = Собран самовывоз в Джумле
На вязании в МС = На вязании в Джумле
Отправлен в МС = Отправлен в Джумле
Выдан Самовывоз = Выдан в Джумле
*/
	
	if($v['state']['name']=='Ожидает оплату') {
			$stat='P';	
	}
	if($v['state']['name']=='Отгружен') { $stat='C'; $v['state']['name']='Оплачен'; }
	if($v['state']['name']=='Нужна корректировка') $stat='R';	
	if($v['state']['name']=='Отменен') $stat='X';	
	if($v['state']['name']=='На вязании') $stat='F';	
//$old_stat='';
//var_dump($order_id);
//var_dump($old_stat);
//var_dump($stat);
	if($stat and ($old_stat!=$stat)){
		$stmt = $pdo->prepare("SELECT email_subject, email_body  FROM c9o6s_interamind_vm_emails WHERE name='orderStatusChangedEmail'");
		$stmt->execute();
		list($subject,$message) = $stmt->fetch(PDO::FETCH_LAZY);
		$message=str_replace("[ORDER_NUMBER]",$ordnum,$message);
		$message=str_replace("[ORDER_STATUS]",$v['state']['name'],$message);
		$message=str_replace("[CUSTOMER_FIRST_NAME]",$fname,$message);
		$message=str_replace("[CUSTOMER_LAST_NAME],",$lname,$message);
		$message=str_replace("[ORDER_STATUS_DESCRIPTION]","",$message);
		$message=str_replace("[COMMENT]","",$message);
		$message=str_replace("[VENDOR_NAME]","",$message);
		$message=str_replace('src="images/lam-.jpg"','src="https://xn--d1aegojhh4j.xn--p1ai/images/lam-.jpg"',$message);
		$subject=str_replace("[ORDER_NUMBER]",$ordnum,$subject);
		$subject=str_replace("[CUSTOMER_FIRST_NAME]",$lname,$subject);
		//$to='lightice777@gmail.com';
		$headers = "From: info@wooldom.ru\r\n";
		$headers .= "Reply-To: info@wooldom.ru\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$success = mail($to, $subject, $message, $headers);
//var_dump($to);
//var_dump($subject);
//var_dump($message);
//var_dump($headers);
//var_dump($success);
		$stmt = $pdo->prepare("update c9o6s_virtuemart_orders SET order_status='$stat' where virtuemart_order_id='$order_id'");
		$stmt->execute();
	}
/*
	$modif=$v['updated'];
	$msOrderRes = mysqli_query($db,"SELECT * FROM ms_orders WHERE ms_id = '" . $v["id"] . "'");
        if($msOrderRes->num_rows>0){
		$date_modified=strtotime( $modif);
		mysqli_query($db,"update ms_orders set ms_upd='$date_modified', last='$date_modified' WHERE ms_id = '" . $v["id"] . "'");
	}
*/
}
