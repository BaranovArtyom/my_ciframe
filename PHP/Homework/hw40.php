<?php

class one 
{
    private $a;
    private $b;

    public function setValue($a,$b){
        $this->a = $a;
        $this->b = $b;

    }
  
    public function mul(){
    return $this->a*$this->b;
    }
    public function div(){
        return $this->a/$this->b;
    }
    public function nul(){
        $this->a = 0;
        $this->b = 0;
    }
}

$one = new one;
$one->setValue(5,6);

var_dump($one);
echo $one->mul()."<br>";
echo $one->div()."<br>";
echo $one->nul()."<br>";
echo $one->mul()."<br>";
var_dump($one);

