<?php
//классы и объекты


class SomePeople {
    public $age;
    public $name;
    public static $people = 1;
}

$nick = new SomePeople;
$nick->age = 33;
$bob = $nick;
echo $nick->age.'<br>';
$nick->age = 35;
echo $bob->age.'<br>';

// $nick = new SomePeople;
// $nick->age = 30;
// $nick->name = 'Nick';

echo SomePeople::$people;


