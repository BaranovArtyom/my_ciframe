<?php   

if (!empty($_POST)) {
    if (empty($_POST['name'])){
        $errors[] = 'pole name empty';
    }

    if (empty($_POST['age'])){
        $errors[] = 'pole age empty';
    }elseif (!is_numeric($_POST['age'])){
        $errors[] = 'pole age not integer';
    }

    if(!empty($errors)) {
        foreach ($errors as $err) {
            echo "<b>$err</b><br>";
        }
    }
}