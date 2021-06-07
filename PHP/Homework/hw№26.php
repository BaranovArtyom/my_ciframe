<?php

// function getName($name) {
//     return "hello ".$name;

// }

// $name = getName('Sasha');
// echo $name;

// function getHello() {
//     return "hello ";

// }

// $hello = getHello();
// echo $hello;

$sum = function (...$arr) {
    $r = 0;
    foreach ($arr as $val) {
        $r = $r + $val;
        

    }
    return $r;
};

echo $sum(2,3,4,5);
