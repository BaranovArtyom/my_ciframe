<?php
include('simple_html_dom.php');

ini_set('display_errors', 1);
ini_set('error_log', 'logger.log');
error_reporting(E_ALL);

function dd($value) {
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

$today = date("H:i:s",strtotime(date("H:i:s")));
$s = date("Y-m-d H:i:s");
$data = $s." ".$today;
// dd($s);exit;

// curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1');

$base = "https://www.ozon.ru/brand/beautific-73401152/";
// $html = "https://www.google.com";


// $base = 'https://play.google.com/store/apps';

$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1');
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_URL, $base);
curl_setopt($curl, CURLOPT_REFERER, $base);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
$str = curl_exec($curl);
curl_close($curl);

// Create a DOM object
$html_base = new simple_html_dom();
// Load HTML from a string
$html_base->load($str);

//get all category links
foreach($html_base->find('a') as $element) {
    echo "<pre>";
    print_r( $element->href );
    echo "</pre>";
}

$html_base->clear(); 
unset($html_base);

// socket_create()