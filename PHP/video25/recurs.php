<?php
//рекурсия

function recurs($num) {
    // если параметр $num меньше 10, продлжаем рекурсию
    if ( $num >= 10) {
        //уменьшать значение параметра $num и его выводим в браузер
        echo ($num--) . '<br>';
        //производим рекурсивный вызов функции
        recurs($num);

    }else return;
}
// recurs(15);

function factorial($num) {
    if ($num <= 1) {
        return 1;
    }else {
        return $num * factorial($num - 1);
    }
}

echo factorial(5);