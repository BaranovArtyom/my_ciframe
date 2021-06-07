<?php

class MyGroup {
    private $name;
    private $age;
    private $sex;

    public function __construct($name=null, $age=18, $sex=null)
    {
        $this->name = $name;
        $this->age = $age;
        $this->sex = $sex;
    }

    public function __get($key)
    {
    
        return $this->key;
    }

    public function __set($key, $value)
    {
        
        if (is_numeric($value)){       
            $this->key=$value+1;
        }
        else {
            $this->key=$value;
        }
      
    }
}

$obj = new MyGroup('sasha','','qwe');

$obj->name = 'sasha';
$obj->sex = 'man';
$obj->age = 7;
echo $obj->age;
echo $obj->sex;
// $obj1 = new MyGroup();

// echo '<pre>';
// print_r($obj);
// echo '</pre>';