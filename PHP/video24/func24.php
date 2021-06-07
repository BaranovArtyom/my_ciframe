<?php 
// // глобальные переменные

// function getSum() {
//    global $var; //глобальные переменные
//    $var = 2; 
//     // $var = 2; // локальная переменния 
//     return $var;
// }

// $var = 5; //внешняя переменная

// статические переменные

// function calc() {
//      static $start = 0;
//     return ++$start;
// }

// echo  calc();
// echo  calc();
// echo  calc();

function myFriends() {
    $kate = 'Kate';
    $nike = 'Nike';
    $vlad = 'Vlad';

    return [$kate, $nike, $vlad];
}

print_r(myFriends());

