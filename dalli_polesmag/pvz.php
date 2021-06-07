<?php 

ini_set('display_errors', 'on');
set_time_limit(600);
require_once "funcs.php";


$getPDF = getPVZ();
$getPDF = createOrdersInDally( $url = 'https://api.dalli-service.com/v1/index.php', $getPDF ); // получение файла pdf
// dd($getPDF);
$pvz = simplexml_load_string($getPDF);
// dd($pvz);
$otd = array();
foreach ($pvz as $otdel) {
    // dd($otdel);
    $otd['attributes'] = $otdel->attributes()->code;
    $otd['town'] = $otdel->town;
    $otd['address'] = $otdel->address;
    $otd['phone'] = $otdel->phone;
    $otd['worktime'] = $otdel->worktime;
    $otd['GPS'] = $otdel->GPS;
    $otd['description'] = $otdel->description;

    $pvz_otd[] = $otd;
    $createPVZ = createPVZ($otd['address'], $otd['attributes'], $otd['worktime']);
    // exit;
}
dd($createPVZ);