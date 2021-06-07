<?php
setcookie ('test',plus());

function plus(){
    if (isset($_COOKIE['test'])){
        $_COOKIE['test']++;

    }else {
        $_COOKIE['test'] = 1;
    }
    return $_COOKIE['test'];
}

echo "счетчик посещения страницы {$_COOKIE['test']}";