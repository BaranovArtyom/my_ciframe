<?php

// $arr = [1,2,3,4,5,6];
// $ran = rand(0,5);

// echo $arr[$ran];

// $arr1 = [1,2,3,4,5,6,
//         7,8,9,10,11,12,
//         13,14,15,16,17,
//         18,19,20
// ];
// $arr2 = [];
// foreach ($arr1 as $val) {
//     if ( $val%2 == 0 ) {
//         $arr2[] = $val;
//     }
// }

// echo "<pre>";
// print_r($arr2);
// echo "</pre>";

//4 задача
// $arr1 = file('hw№21_add.php');
// $arr2 = implode(",", $arr1);
// $arr3 = explode(',',$arr2);
// print_r($arr3);

//5 задача

$arr = [];

for ($i = 0 ; $i < rand(5,10); $i++) {
    $arr[] = rand(0,100);
    
};
asort($arr);

print_r($arr);

