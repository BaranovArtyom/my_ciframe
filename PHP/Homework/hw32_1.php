<?php

$arr = file_get_contents('json.txt');

$arr2 = json_decode($arr, true);
print_r($arr2);