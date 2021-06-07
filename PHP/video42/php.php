<?php
// конструктор

class Constr {
    private $y;
    private $x;

    public function __construct($x = 0)
    {
        echo "consructor method"."<br>";
        $this->y = 22;
        $this->x = $x;
    }

    public function __destruct()
    {
        echo "destructor method"."<br>";
    }

    public function getY(){
        return $this->y;
    }

    public function getX(){
        return $this->x;
    }

}

$obj = new Constr();
echo $obj->getY();
echo $obj->getX();

 