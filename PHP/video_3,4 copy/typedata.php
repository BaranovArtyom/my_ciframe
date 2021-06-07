<?php
error_reporting(E_ALL);
ini_set('display_errors',true);

$num1 = 1234;
$num2 = 1234;
$num3 = 0123;//восмиричный
$num5 = 0x1A;//шестнад

echo $num3;

$str = 'hello world';
$str = 'number $num1';

unset($str);//-delete $str