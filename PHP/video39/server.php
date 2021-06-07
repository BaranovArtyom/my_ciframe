<?php

//Дерево документа

echo $_SERVER['DOCUMENT_ROOT'].'<br>';
// некие параметры относительно типов документа
echo $_SERVER['HTTP_ACCEPT'].'<br>';

echo $_SERVER['HTTP_ACCEPT_LANGUAGE'].'<br>';

echo $_SERVER['HTTP_HOST'].'<br>';

echo $_SERVER['HTTP_REFERER'].'<br>';

echo $_SERVER['HTTP_USER_AGENT'].'<br>';
// ip адрес клиента
echo $_SERVER['REMOTE_ADDR'].'<br>';
//абсолютный путь
echo $_SERVER['SCRIPT_FILENAME'].'<br>';
//хранится имя сервера/ домен
echo $_SERVER['SERVER_NAME'].'<br>';
//считываем метод запроса
echo $_SERVER['REQUEST_METHOD'].'<br>';
//параметры с запроса страницы
echo $_SERVER['QUERY_STRING'].'<br>';
//содержит имя скрипта
echo $_SERVER['REQUEST_URI'].'<br>';

echo '<pre>';
print_r($_SERVER);
echo '</pre>';

echo date('H:i:s', $_SERVER['REQUEST_TIME']);
