<?php 
/*Написать три  класса
Люди, животные, планеты
В каждом минимум по 5 переменных
Вывести минимум три объекта для каждого по одному
Задать всем свойствам объектов значение
поиграться с областью видимости*/

class People {
    public $man_name;
    public $women_name;
    private $childname;

}

$kate = new People;
$kate->women_name = 'Kate';
$kate->man_name = 'Serg';

echo $kate->women_name;




?>