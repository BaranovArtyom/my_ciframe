<?php
if (empty($_POST['name']) and empty($_POST['age'])){
    exit ('Одно либо два поля не заполнены');
}else{
    $name = htmlspecialchars($_POST['name']);
    $age = htmlspecialchars($_POST['age']);
}

echo $name. ' ' . $age;