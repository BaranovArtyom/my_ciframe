<?php

$file = date('Y-m-d-h-i-s').'.txt';

if (true) {
    $num = rand(0,PHP_INT_MAX);
    file_put_contents($file,$num,FILE_APPEND);
}