<?php 
//массивы
$arr = [1,2,3];

list($one,$two,$three) = $arr;

// обход массива
 
for ($i = 0; $i < count($arr); $i++) {
    echo $arr[$i]."<br>";
}

$arr2 = [
    'name' => 'Andrey',
    'age' => '31',
    'weight' => 73
];

foreach  ($arr2 as $key => $value) {
    echo $key . "-". $value."<br>"  ;
}
