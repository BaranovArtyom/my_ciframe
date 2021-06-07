<?php
session_start();
// echo "Session";
// сессии 
$_SESSION['name'] = 'sasha';
$_SESSION['arr'] = [1,'2','test'];

if (ini_get('session.use_cookies')) {
    //создали переменную и записали все куки этой сессии
        $params = session_get_cookie_params();
        
        setcookie(session_name(), '',time() - 42000,
            $params["path"], $params["domain"],
            $params['secure'], $params["httponly"]
        );
    }

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// unset($_SESSION['name']);
// session_destroy();
$_SESSION = [];

echo "<pre>";
print_r($_SESSION);
echo "</pre>";
var_dump(session_destroy());







