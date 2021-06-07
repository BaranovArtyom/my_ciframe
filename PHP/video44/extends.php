<?php

class Base {
    protected $secret;
    public $one;
    private $text;//недоступна при наследовании
    
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    public function printHello(){
        echo $this->one;
    }
}

class NewClass extends Base {
    public $two;

    public function by(){
        echo $this->two;
    }

    public function __construct($secret)
    {
        $this->secret = $secret;
    }
}


$obj = new NewClass(11);
$obj->one = 'Sasha';
$obj->two = "by";
$obj->text = "text";

$obj->printHello();
$obj->by();

echo '<pre>';
print_r($obj);
echo '</pre>';