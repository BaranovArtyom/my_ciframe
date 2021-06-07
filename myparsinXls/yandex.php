<?php
declare(strict_types=1);

require_once 'functions1.php';
require_once 'config.php';

ini_set('display_errors', 'on');

if (!$_GET['code']){							 // проверка на получения code
    exit('error code');
}

$token = myTokenCurl("https://oauth.yandex.ru/token");// получение токена для скачивания файла из яндекс диска

$pathFile = '/Загрузки/Склады/Казань/20201017111928_admin@info7290_1142007626.xlsx';	 // путь к файлу для скачивания
$path = __DIR__;								 // Директория, куда будет сохранен файл.

$urlLinkDownload = 'https://cloud-api.yandex.net/v1/disk/resources/download?path='.  urlencode($pathFile); // $url для myLinkDownloadCurl

$res = myLinkDownloadCurl($urlLinkDownload,$method='GET',$token); // получение ссылки для скачивания

if (empty($res['error'])) {

	$href = $res['href'];
	$file = DownloadCurl($href, $method='GET', $token, $pathFile, $path); // скачивания файла с яндекс диска
	echo "success"; 
}

?>