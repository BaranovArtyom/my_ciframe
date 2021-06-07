<?php 

abstract class Pages{

    abstract public function getAll();
}

class AllSearch extends Pages
{
    public function getAll(){
        echo 'text';
    }
}

$obj = new AllSearch();
$obj->getAll();
// проверяет $obj класс или переменная результат true
echo $obj instanceof AllSearch;

?>