<?php
//классы и объекты


class SomePeople {
    public $age;
    public $name;
}

$nick = new SomePeople;
$nick->age = 30;
$nick->name = 'Nick';

// echo $nick->name;
// unset($nick);
// $dasha = new SomePeople();
// echo  $nick;

class Location {
    public $x;
    private $y;
    public $z;
}

$loc = new Location();
$loc->x=23.22;
$loc->z=33.22;
echo $loc->z;


?>