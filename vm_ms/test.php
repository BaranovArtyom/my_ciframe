<?php
ini_set('display_errors', 1);
require_once  "funcs.php";
require_once "config.php";

// $dir = __DIR__."/../templates/gk_bikestore/html/com_virtuemart/invoice/mail_html_shopper.php";

// dd($dir);
// $file = file_get_contents($dir);
// // dd($file);
// $hi = "hi";

// $change = str_replace('SELECT * FROM `#__virtuemart_shipment_plg_cdek_pickup` \'
// . \'WHERE `virtuemart_order_id` = \' . $this->orderDetails[\'details\'][\'BT\']->virtuemart_order_id', 'hi',$file);
// $change  = str_replace("COM_VIRTUEMART_MAIL_SHOPPER_YOUR_ORDER", "S9YY020814", $file);
// $change = str_replace('. $this->orderDetails[\'details\'][\'BT\']->virtuemart_order_id', '12573',$file);

// dd($file);
// $UpdateOrders = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_interamind_vm_emails` WHERE `id` = 3"));

// dd($db);
// $getOrdersVM = mysqli_query($db,"SELECT * FROM `rtd_virtuemart_orders` WHERE `created_on` > '$data' "); // получений заказов за день

// dd($getOrdersVM);
$virtuemart_order_id = 26707;
$virtuemart_product_id = 8379;
$order_status = 'S' ;
// echo "SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '$virtuemart_product_id'  ";

// exit();
$getAttData = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_order_items` WHERE `virtuemart_product_id` = '$virtuemart_product_id' and `virtuemart_order_id` = '$virtuemart_order_id' "));
dd($getAttData);
dd(($getAttData[20]));
$attr = json_decode($getAttData[20]);

$Atr=array();
    foreach ($attr as $key=>$val) {
        dd(($val));
        dd(($key));
        if (is_numeric($val)){
            $getNameAtr = mysqli_fetch_row(mysqli_query($db,"SELECT * FROM `rtd_virtuemart_customs` WHERE `virtuemart_custom_id` = $key"))[11];
            $getAtt = mysqli_fetch_row(mysqli_query($db,"SELECT customfield_value FROM `rtd_virtuemart_product_customfields` WHERE `virtuemart_customfield_id` = '$val'  and `virtuemart_custom_id` = '$key' "))[0];
            echo "true";
            dd($getNameAtr);
            dd($getAtt);
            $textAtr = $getNameAtr." ".$getAtt;
            $Atr[] = $textAtr;
        }else {
            echo "false";
            $textAtr = $val;
            // $textAtr = preg_replace('|(</span>)|Uis', ' $1 $2 ', $val);
            // echo  $textAtr;
            $Atr[] = $textAtr;
        }
        
        // dd($getAtt);
        // dd($getNameAtr);
      
    }
    // dd($getAtt);
    // dd($getNameAtr);
    // dd($textAtr);
    dd($Atr);
    foreach ($Atr as $v){
        
    }


    // $attrs = json_decode("87591":" \u0420\u0430\u0437\u043c\u0435\u0440 \u0434\u0436\u0435\u0440\u0441\u0438:<\/span>L (\u0411\u043e\u043b\u044c\u0448\u043e\u0439)<\/span>");