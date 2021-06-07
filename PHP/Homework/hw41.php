<?php

class Find {
    private $name;
   
    public function setUrl($name){
        $this->name = 'https://www.php.net/manual/ru/function.'.$name.'.php';
       
    }
    public function getUrl(){
        return $this->name;
    }
   
    // public  function find($name){
    //     $text = file_get_contents('https://www.php.net/manual/ru/function.'.$name.'.php');
    //     echo $text;
    // // }
    public function find(){
        return file_get_contents($this->getUrl());
    }
}

$obj = new Find;
$obj->setUrl('copy');
echo $obj->getUrl('copy');
echo $obj->find();