<?php
//методы

class Hi
{
    public function hello(){
        return 'hello anybody';
    }
    public function by(){
        return 'by';

    }
}

$obj = new Hi;
// echo $obj->hello();
// echo $obj->by();

class Family 
{
    private $boy;
    private $girl;

    public function setNameBoy($boy){
        $this->boy = $boy;
    }
    public function setNameGirl($girl){
        $this->girl = $girl;
    }
    public function getNameBoy(){
        return $this->boy;
    }
    public function getNameGirl(){
        return $this->girl;
    }
    public function friend(){
        return $this->getNameBoy() . " и ". $this->getNameGirl()."= friend!";
    }
}

$family = new Family;

$family->setNameBoy('Nike');
$family->setNameGirl('Nina');

echo $family->friend();
echo $family->getNameGirl();
