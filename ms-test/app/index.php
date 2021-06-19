<?php 
namespace app;
ini_set('display_errors', 'on');
ini_set('log_errors',1);
require_once __DIR__.'/../classes/CustomerOrder.php';

var_dump(__DIR__);


use classes\CustomerOrder;
$s = new CustomerOrder();
$s->getURL('qwer');