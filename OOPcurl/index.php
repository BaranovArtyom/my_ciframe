<?php
ini_set('display_errors', 'on');
require_once __DIR__.'/curl.php';
require_once __DIR__.'/functions.php';

$curl = new Curl('admin@sergienko8385:ba3b92ef44');

$store = $curl->getCurl('store');
$org = $curl->getCurl('organization');
$assort = $curl->getCurl('assortment');
// dd($store);
dd($assort->rows);
// foreach ($org->rows as $val) {
//     dd($val->meta);
// }
