<?php
require_once "funcs.php";
require_once "config.php";

//получение товаров из ms для бд 
$ms_products = $all_assortments = [];
$page = 0; $limit = 1000;
$assortments_size = getSizeAssortment();			    // получаем размер ассортимента = 11521
$max_pages = ceil($assortments_size / $limit);		// количество страниц
// $max_pages = 12; 						                // тестовое кол-во страниц

while ($page < $max_pages) {
	$offset = $page * $limit;
	$all_assortments[] = getAllassort($db,$offset);
    $page++;
}

