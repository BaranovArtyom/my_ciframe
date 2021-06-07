<?php 

$host = 'spey.ru';// где name_host - название вашего хоста.

$database = 'dev_test3';

$user = 'dev_test3'; 

$password = 'dev_test3'; 



// Чтобы разрешить удаленное root-подключение к MySQL-базе нужно передать конструктору следующий код:

// require_once 'connection.php'; // Считываем нужные данные подключения

// выполняем подключение к серверу

$link = mysqli_connect($host, $user, $password, $database)

or die("Ошибка подключения " . mysqli_error($link));

// выполняем различные операции с базой данных

$sql = "show tables";
        $result = mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link));
        echo $result;
// закрываем подключение к серверу

mysqli_close($link);

?>