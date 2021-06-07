<?php

class Base {
    public function stars(){
        echo "I am parent class";
    }
}

class Second extends Base {
    public function stars(){
        parent::stars();
        echo "I am child class";
    }
}

$obj = new Second();
$obj->stars();