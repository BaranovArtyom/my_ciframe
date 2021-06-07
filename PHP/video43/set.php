<?php
//аксессоры __set() __get()

class Accessor{
    private $one = [];

    public function __get($key)
    {//если содержимое в нашем массиве
        if (array_key_exists($key, $this->one)){
            return $this->one[$key];
        }else{
            return null;
        }
    }

    public function __set($key, $value)
    {
        $this->one[$key] = $value;
    }
}

$obj = new Accessor();

$obj->text = 'test test'.'<br>';
$obj->name = 'sasha'.'<br>';

echo '<pre>';
print_r($obj);
echo '</pre>';

// class MyGroup{
//     public function __construct($name,$age=18,$sex)
//     {
        
//     }
//     public function
// }