<?php

$arr1 = ['my','text'];
$arr2 = ['our','some'];

// $sum = $arr1 + $arr2;
//обЪединение массивов
$sum = array_merge($arr1,$arr2);


echo "<pre>";
print_r($sum);
echo "</pre>";

// сравнение массивов

$a1 = [1,2,3,4,5];
$a2 = [1,2,3,4,5];
$a3 = [1,2,3,4];
$a4 = [1,2,4,4,5];
// проверка на существования массива

for ($i=0 ; $i<=6; $i++) {
    if (isset($a1[$i])) {
        echo "Элемент массива \$a1[$i] существует"."<br>";
    }else {
        echo "Элемент массива \$a1[$i] не существует"."<br>";
    }
}

// is_array()

if (is_array($a1)) {
    echo "переменная \$a1 является массивом";
}else {
    echo "переменная \$a1 не является массивом";
}

// in_array , если надо строгое равенство === 
//надо добавить true  в in_array(7,$a1,true))

if (in_array(7,$a1)) {
    echo "цифра 7 существует в массиве";
} else {
    echo "цифра 7 не существует в массиве";
}

// array_key_exists(key,array)
//удаление элементов массивов;
// unset ($a2);
unset ($a2[2]); //- второй элемент массива

echo "<pre>";
print_r($a2);
echo "</pre>";

