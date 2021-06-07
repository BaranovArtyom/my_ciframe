<?php

class Basket{

    private $bread;
    private $milk;
    private $cehes;
    private $eggs;
    private $kartovel;

    public function __construct( $bread=1, $milk=1, $cehes=1, $eggs=1, $kartovel=1 )
    {
        $this->bread = $bread;
        $this->milk = $milk;
        $this->cehes = $cehes;
        $this->eggs = $eggs;
        $this->kartovel = $kartovel;
    }

    public function __destruct()
    {
        $this->bread = 0;
        $this->milk = 0;
        $this->cehes = 0;
        $this->eggs = 0;
        $this->kartovel = 0;
    }

    public function getAmount(){
        return $this->bread + $this->milk + $this->cehes +$this->eggs+$this->kartovel;
    }
}

$obj = new Basket(2,3);
echo $obj->getAmount();