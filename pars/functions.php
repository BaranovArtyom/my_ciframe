<?php 
require_once 'connect.php';

function dd($value) {
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

function getCode($db, $sql = "SELECT code FROM `news` WHERE type = 'qwe'") {
    
    $result = mysqli_query($db, $sql) or die("Ошибка " . mysqli_error($db));
     
    $code = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)){
            $code[] = $row;
        }
    }
    return $code;
}
