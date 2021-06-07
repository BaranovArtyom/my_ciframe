<?php
//кол-во символов

$str = 'hello-people';
echo strlen($str);

//поиск по строке
echo substr($str,5,2);//-p

echo strpos($str, '-'); // 5
echo strpos($str, 'P'); // false

$text = "PHP - довольно простой язык";
echo substr($text,strpos($text,'дов'));

//замена текста в строке

echo str_replace("-", "=", $text);

//удаление пробелов и переносов
$str2 = '  hello  -  people 
        12';
echo trim($str2);


