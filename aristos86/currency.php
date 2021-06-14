<?php 
ini_set('display_errors', 'on');
require_once 'funcs.php';

/**получение курса валют*/
$getCurrency = getCurrency();

$UAH = $getCurrency->UAH;               // получение курса гривны
$GBR = $UAH/$getCurrency->GBP;          // получение курса фунта в грн
$RUB = $UAH/$getCurrency->RUB;          // получение курса фунта в грн
$UAH = number_format($UAH, 2, '.', ''); // округление до 2 цифр после запятой
echo $UAH.'   '.$GBR.'  '.$RUB;


