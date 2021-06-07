<?php
//функции 
// function muFunc(){
//     $sum = 1 + 2;
//     return $sum;
// }

function muFunc(int $num1, $num2) : int  {
    $sum = $num1 + $num2;
    return $sum;
}

echo muFunc(11,32.2424);