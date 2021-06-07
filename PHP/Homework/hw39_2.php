<?php

print_r($_SERVER['HTTP_USER_AGENT']);
$user = explode(' ',$_SERVER['HTTP_USER_AGENT']);
echo "Текущий посетитель имеет браузер: ".$user[8].'<br>'.' Операционку: '.$user[2].' '.$user[3];