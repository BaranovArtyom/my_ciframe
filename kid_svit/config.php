<?php
require_once 'funcs.php';

/**соединения с базой */
define('DB_HOST', 'localhost');
define('DB_USER', 'sasha');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'c_integr');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("Ошибка " . mysqli_error($db));
mysqli_set_charset($db, 'utf8');

/**доступы по хорошоп  */
$config_horoshop = array();
$horoshop['login'] = 'ciframe'; 
$horoshop['password'] = 'sxfuoacfw';
$horoshop['token'] = getToken($horoshop['login'],$horoshop['password']);
 
$config_horoshop = $horoshop;
// dd($config_horoshop);exit;

/**пароли для 'https://bot.kiddisvit.ua/KiddisvitServices/hs/ImportDataProductsFile/?format=xml' */
$NAME_SITE = 'kiddisvit.ua';

$config_kiddisvit = array();
$conf['password'] ='BvgjhnAfqkjd@2020';     // пароль 
$conf['user'] = 'IamClient';                // user

$config_kiddisvit = $conf;

/**Добавление в базу config kidd_svit */
    foreach ($config_kiddisvit as $key=>$data) {
        $check_config = '';
        // dd($data);
        $check_config= mysqli_query($db,"SELECT `name` FROM `ci_config`  WHERE `value`='{$data}' "); //проверка на существование конфига
        $check_key = mysqli_query($db,"SELECT `key` FROM `ci_config`  WHERE `key`='{$key}' ");       // проверка на значение конфига кеу существовал если нет добавляем иначе обновляем
        
        if($check_config->num_rows==0 and $check_key->num_rows==0 ){
            
            $insert_ci_config = mysqli_query($db,"INSERT INTO `ci_config` (`id`,`name`,`key`,`value`,`default`,`type`)
                                            VALUES (NULL, '{$NAME_SITE}','{$key}','{$data}',NULL,NULL) ");
                
                if (mysqli_error($db)) {                        // проверка на  ошибку в запросе mysql записью в лог
                    file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$NAME_SITE."\n",FILE_APPEND);
                }
        }else{
            $update_ci_config = mysqli_query($db,"UPDATE `ci_config` SET `name` = '{$NAME_SITE}',`key` = '{$key}',`value`= '{$data}',`default`= NULL,`type`= NULL WHERE `key`= '{$key}'");
                if (mysqli_error($db)) {                            // проверка на  ошибку в запросе mysql записью в лог
                    file_put_contents('ci_log.log',date('Y-m-d H:i:s').'  ошибка в бд - '.mysqli_error($db).'  '.$NAME_SITE."\n",FILE_APPEND);
                }
        }
    }
