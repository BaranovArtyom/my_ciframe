<?php

date_default_timezone_set('Europe/Kiew');

$time = date("y-m-d h:i:s",1615463785);
echo $time."<br>";
echo strtotime($time);