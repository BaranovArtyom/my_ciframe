<?php
ini_set('display_errors', 'on');
require_once 'functions.php';
require_once __DIR__.'/phpQuery/phpQuery.php';


$urlMain = 'http://sdvor.pro/';                  //1.Создаем страницу
$main = curlGet($urlMain);                      // получение основной страницы
$doc = phpQuery::newDocument($main);            // инициализация библиотеки phpQuery

foreach($doc->find('.catalog-column a') as $category){ //
    // die(dd($category));
    $exist['category'][] = pq($category)->text();
    $exist['categoryHref'][] = pq($category)->attr('href');
}
$prod[]= $exist;
dd($exist);