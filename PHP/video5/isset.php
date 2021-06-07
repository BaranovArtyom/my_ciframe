<?php 

$var = 'some txt';
$num = 123;
//isset();empty()

//$status = isset($var);//1 
//echo $status;

// если пуста переменная , "0",null,false =true
//$status2 = empty($var1);//1
//echo $status2;

if (isset($str)){
    echo '$str true';
}else 
    echo 'no $str'.'<br>';

//определяем тип переменной

echo gettype($var);