<?php

// $friends = ['Vitalik', 'Roma', 'Maks'];

// function getHello (...$arr) {
//     foreach ($arr as $val) {
//         echo 'hello '.$val."<br>" ;
//     }
// }

// getHello(...$friends);

function getResult (int $num1, int $num2, $oper) {
    switch ($oper) {
        case '-':
            return $num1 - $num2;
        case '+':
            return $num1 + $num2;
        case '*':
            return $num1 * $num2;
        default:
            return $num1 / $num2;
    }
}

echo getResult(2, 1, '+');