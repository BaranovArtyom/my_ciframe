<?php

header('Content-Type: text/html; charset=utf-8');

$db = mysqli_connect('localhost','panovv9y_bytdet','','');
mysqli_query($db, "SET NAMES 'utf8'");

$AUTH_DATA = '';


function ms_query($link) {
	global $AUTH_DATA;
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
	curl_setopt($curl, CURLOPT_URL, $link);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($curl, CURLOPT_POST, 0);
	curl_setopt($curl, CURLOPT_USERPWD, $AUTH_DATA);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	$out = curl_exec($curl);
	curl_close($curl);
	$json = json_decode($out, JSON_UNESCAPED_UNICODE);
	return $json;
}

function ms_query_send($link, $data, $request) {
	global $AUTH_DATA;
	$send_data = json_encode($data);
	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_URL, $link);
	curl_setopt($curl, CURLOPT_POST, 0);
	curl_setopt($curl, CURLOPT_USERPWD, $AUTH_DATA);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $send_data);
	curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie2.txt');
	curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie2.txt');
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	$out = curl_exec($curl);
	curl_close($curl);
	$json = json_decode($out, JSON_UNESCAPED_UNICODE);
	return $json;
}

function ms_query_image($link) {
	global $AUTH_DATA;
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1');
	curl_setopt($curl, CURLOPT_URL, $link);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($curl, CURLOPT_POST, 0);
	curl_setopt($curl, CURLOPT_USERPWD, $AUTH_DATA);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	$content = curl_exec($curl);
	$info = curl_getinfo($curl);
	$cerrorno = curl_errno($curl);
	$cerrorinfo = curl_error($curl);
	curl_close($curl);

	$response = $info;
	if ($response['http_code'] == 301 || $response['http_code'] == 302) {
		ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
		$headers = get_headers($response['url']);
		$headers = get_headers($response['redirect_url']);
		$location = "";
		foreach($headers as $value) {
			$out = file_get_contents($response['redirect_url']);
			return($out);
		}
	}
    return $content;
}

function resizeImage($imgObject, $savePath, $imgName, $imgMaxWidth, $imgMaxHeight, $imgQuality) {

    $source = imagecreatefromjpeg($imgObject['tmp_name']);
    list($imgWidth, $imgHeight) = getimagesize($imgObject['tmp_name']);

    $imgAspectRatio = $imgWidth / $imgHeight;
    if ($imgMaxWidth / $imgMaxHeight > $imgAspectRatio) {
        $imgMaxWidth = $imgMaxHeight * $imgAspectRatio;
    } else {
        $imgMaxHeight = $imgMaxWidth / $imgAspectRatio;
    }
    $image_p = imagecreatetruecolor($imgMaxWidth, $imgMaxHeight);
    $image = imagecreatefromjpeg($imgObject['tmp_name']);
    imagecopyresampled($image_p, $source, 0, 0, 0, 0, $imgMaxWidth, $imgMaxHeight, $imgWidth, $imgHeight);
    imagejpeg($image_p, $savePath. $imgName, $imgQuality);
    unset($imgObject);
    unset($source);
    unset($image_p);
    unset($image);
}

