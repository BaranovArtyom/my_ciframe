<?php

$years = [];

for ($i=2000; $i < 2021; $i++){
    $years[] = $i;
}

// echo "<pre>";
// print_r($years);
// echo "</pre>";

// $mounth = [];
$mounth = ['jan','feb', 'mart' , 'aprel' ,'may' ,'june', 'jule' , 'august' , 'semp' , 'oct','nov','dec'];
$week = ['sun','mon','tue','wen','tho','fri','sat'];

echo $years[count($years) -1]." ".$mounth[count($mounth) -1]." ".$week[count($week) -1];