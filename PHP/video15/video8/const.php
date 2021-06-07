<?php
//классы и объекты

class SomePeople {
    public $age;
    public $name;
}

// $nick = new SomePeople;
// $nick->age = 35;
// echo $nick->age;

// $tom = clone $nick;
// $tom->age = 22;
// echo $tom->age;

// echo $nick->age;

//define();

define('PI',3.14);

$boll = defined('PI');

echo is_bool($boll);

echo 'file = '. __FILE__."<br>";
echo 'file = '. __DIR__."<br>";
