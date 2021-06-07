<?php
//замыкание

$mess = "Текст до момента создание функции <br>";

$test = function (array $some) use ($mess) {
    if (isset($some) && count(($some))>0 ) {
        echo $mess;
        foreach ($some as $li) {
            echo $li . "<br>";
        }
    }
};

$test([]);
$some[] = 'текст типа';
$test($some);

//изменение окружения
$mess = "измененный текст";
//переменная не изменится при замыкании

$some = [12,33,434,535];
$test($some);