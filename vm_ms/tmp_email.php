<?php 
echo 1;

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body>
<table width="100%" border="0" cellpadding="5" cellspacing="0" style="border-collapse:collapse;font-family:Arial,Helvetica,sans-serif;font-size:12px;margin:0 auto">

  <tr>
    <td width="30%">
		<?php echo "Номер Вашего заказа:"; ?><br />
		<strong><?php echo $this->orderDetails['details']['BT']->order_number ?></strong>

	</td>
    <td width="30%">
		<?php echo JText::_('COM_VIRTUEMART_MAIL_SHOPPER_YOUR_PASSWORD'); ?><br />
		<strong><?php echo $this->orderDetails['details']['BT']->order_pass ?></strong>
	</td>
    <td width="40%">
    	<p>

		</p>
	</td>
  </tr>
  <tr>
    <td colspan="3"><p>
				<?php echo JText::sprintf('COM_VIRTUEMART_MAIL_SHOPPER_TOTAL_ORDER',$this->currency->priceDisplay($this->orderDetails['details']['BT']->order_total,$this->currency) ); ?></p></td>
  </tr>
  <?php
   if($this->orderDetails['details']['BT']->virtuemart_paymentmethod_id==4 && ($this->orderDetails['details']['BT']->order_status == 'R' || $this->orderDetails['details']['BT']->order_status == 'U')):?>
  <tr>
    <td colspan="3">
        <a target="_blank" style="border:1px solid red; padding:5px 10px;color:#000;text-decoration: none;font-weight:bold" href="http://readytodirt.ru/payment.php?order_number=<?php echo $this->orderDetails['details']['BT']->order_number ?>&order_pass=<?php echo $this->orderDetails['details']['BT']->order_pass ?>">Оплатить заказ</a>
    </td>
  </tr>
      <?php endif;?>
	<tr>
  <td colspan="3"><p>
				<?php
$orderStatus = $this->orderDetails['details']['BT']->order_status; // текущий статус товара

$model = VmModel::getModel ('Orderstatus');
$listStatus = $model->getOrderStatusList(); // массив со всеми статусами товара
$order_desc = '';
foreach($listStatus as $item){
    if($orderStatus == $item->order_status_code && $item->order_status_description){
        $order_desc = $item->order_status_description;
    }
}
?>
 
<p><?php echo vmText::sprintf('COM_VIRTUEMART_MAIL_ORDER_STATUS',vmText::_($this->orderDetails['details']['BT']->order_status_name)) ; ?></p>
<hr>
<p><b><?php echo JText::_('COM_VIRTUEMART_MAIL_SHOPPER_TOTAL_INSTATUS'); ?>
<?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_total, $this->currency); ?></p></b>
<?php echo '<p>'.htmlspecialchars_decode($order_desc).'</p>'; // вывод описания статуса заказа ?></p></td>
  </tr>
  <?php
 if (!$shipinfo->dispatchnumber=='0'){  
    if( $this->orderDetails['details']['BT']->virtuemart_shipmentmethod_id=='90' && $this->orderDetails['details']['BT']->order_status == 'S'):?>
  <tr>
    <td colspan="3">
        <a target="_blank" style="border:1px solid green; padding:5px 10px;color:#000;text-decoration: none;font-weight:bold" href="https://cdek.ru/tracking?order_id=<?php echo $shipinfo->dispatchnumber; ?>">Отследить груз</a>
    </td>
  </tr>
      <?php endif;?>
 <?php }?>
  

  
  <?php $nb=count($this->orderDetails['history']);
  if($this->orderDetails['history'][$nb-1]->customer_notified && !(empty($this->orderDetails['history'][$nb-1]->comments))) { ?>
  <tr>
    <td colspan="3">
		<?php echo  nl2br($this->orderDetails['history'][$nb-1]->comments); ?>
	</td>
  </tr>
  <?php } ?>
  <?php if(!empty($this->orderDetails['details']['BT']->customer_note)){ ?>
  <tr>
    <td colspan="3">
		<?php echo JText::sprintf('COM_VIRTUEMART_MAIL_SHOPPER_QUESTION',nl2br($this->orderDetails['details']['BT']->customer_note)) ?>

	</td>
  </tr>
  <?php } ?>
</table>
	
</body>
</html>

