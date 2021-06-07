<?php

// echo $_GET['name'];
// // http://example/video33/get.php?name=3 // 3
// echo '<pre>';
// print_r($_GET);
// echo '</pre>';

echo "<a href='test.php?text=" . urlencode('hello php!') . "'>ссылка</a>";

// parse_url()
$url = "htpps://user:admin@site.com/page/index.php?
id=12&value=qwerty";

$url = parse_url($url);
echo '<pre>';
print_r($url);
echo '</pre>';

echo $url['host'];
