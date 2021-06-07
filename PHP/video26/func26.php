<?php
//вложенные функции
// $arr = [1,2,3];
global $arr;
$arr = [1, 2, 3];
function box($some) {
    // $arr = [1,2,3];
    function inn(){
        echo "test";
    }
    // inn();
    print_r($some);
}

//print_r
// box($arr);

// динамическое имя функции

// function hello() {
//     return 'hello';
// }

// $var = hello();
// echo $var;

//анонимные функции

$some = function (...$arr){
    foreach ($arr as $val) {
        echo $val . "<br>";
    }
};

$some(12,23,45,34,242);

$var2 = function (){
    echo "Запуск аноним функции";
};
$var2();