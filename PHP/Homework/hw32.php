<?php 

$arr = [
    "fio" => ['sergirnko','kochevenko','pavlienko'],
    "mob" => ['0950342909',"123456789","987654321"]

];

$json = json_encode($arr);

file_put_contents("json.txt", $json , FILE_APPEND);
highlight_string("Hello world! <?php echo 'hello' ?>");
echo "hello";
ini_set("highlight.keyword", "#0000BB; font-weight: bold");

?>

