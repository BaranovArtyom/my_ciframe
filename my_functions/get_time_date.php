<?php

date_default_timezone_set('Europe/Berlin');

$today = date("H:i:s",strtotime(date("H:i:s")." - 15 minutes"));
$s = date("Y-m-d");
$data = $s." ".$today;