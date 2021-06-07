<?php

$str = "Имя, Фамилия, email, mobile";
$newArr = explode(',',$str);

echo "<pre>";
print_r($newArr);
echo "</pre>"."<br>";

$newStr = implode(';',$newArr);
echo $newStr."<br>";

//Сериализация объектов и массивов
// serialize()

$num = [12,12,24,35,45];

$str = serialize($num);
echo $str."<br>";

$arr = unserialize($str);
echo "<pre>";
print_r($arr);
echo "</pre>"."<br>";
