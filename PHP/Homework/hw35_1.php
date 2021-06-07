<?php

if (!empty($_POST)){
    if (empty($_POST['fio'])){
        $errors[] = "pole fio empty";
    }elseif (is_numeric($_POST['fio'])){
        $errors[] = "pole fio not string";
    }
    if (empty($_POST['age'])){
        $errors[] = "pole age empty";
    }elseif (!is_numeric($_POST['age'])){
        $errors[] = "pole age not number";
    }
    if (empty($_POST['quastion'])){
        $errors[] = "pole quastion empty";
    }elseif (!is_numeric($_POST['quastion'])){
        $errors[] = "pole age not number";
    
    }elseif (($_POST['quastion'])!= 4){
        $errors[] = "pole quastion wrong";
    }
}
if(!empty($errors)) {
    foreach ($errors as $err) {
        echo "<b>$err</b><br>";
    }
}


$sd = json_encode($_POST,true);
echo $sd;

