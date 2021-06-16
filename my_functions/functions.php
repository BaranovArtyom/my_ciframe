<?php

/**для проверки файла на размер и чистка его  */
$size = filesize('logger.log');
if ($size>11462000) file_put_contents('logger.log', '');

/**восстановление базы из дампа */
exec("mysql --user=".DB_USER." --password=".DB_PASSWORD." --host=".DB_HOST." ".DB_NAME." < $dir"."/dump/"."2021-01-27_12".".sql");

/**делает дамп базы в папку dir/dump/*.sql */
exec("mysqldump --user=".DB_USER." --password=".DB_PASSWORD." --host=".DB_HOST." ".DB_NAME." > $dir"."/dump/".$name.".sql"); 

/**для вывода и сохранения ошибок */
ini_set('display_errors', 1);
ini_set('error_log', 'logger.log');
error_reporting(E_ALL);

/**для соединения с базой */
define('DB_HOST', 'localhost');
define('DB_USER', 'sasha');
define('DB_PASSWORD', 'пароль');
define('DB_NAME', 'u1048374_mealjoy_db');
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("Ошибка " . mysqli_error($db));

/**function dd */
function dd($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

/**function correct_phone */
function correct_phone($phone)
{
	$phone=str_replace(array("(",")"," ","-","+"),array("","","","",""),$phone);
	if(substr($phone,0,1)=='9') $phone='8'.$phone;
	if(substr($phone,0,2)=='89') $phone='79'.substr($phone,2,strlen($phone)-2);
	if(substr($phone,0,2)=='84') $phone='74'.substr($phone,2,strlen($phone)-2);
	if(strpos(" ".$phone,"+")==0) {
		$phone="+".$phone;
	}
	return $phone;
}

/**function get_id_from_href */
function get_id_from_href($href)
{
    $t = explode('/', $href);
    $id = explode('?', $t[count($t) - 1])[0];
    return $id;
}

//получение товаров из ms для бд 
$ms_products = $all_assortments = [];
$page = 0; $limit = 1000;
$assortments_size = getSizeAssortment();			    // получаем размер ассортимента = 11521
$max_pages = ceil($assortments_size / $limit);		// количество страниц
// $max_pages = 12; 						                // тестовое кол-во страниц

while ($page < $max_pages) {
	$offset = $page * $limit;
	$all_assortments[] = getAllassort($db,$offset);
    $page++;
}

function getSizeAssortment() {
    $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment?limit=1',
  CURLOPT_USERPWD=> "api@manager245:api1111",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
$response = json_decode($response);
curl_close($curl);

return $response->meta->size;

}

function getAllassort($mysql, $offset) {
  $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment?limit=1000&offset='.$offset,
  CURLOPT_USERPWD=> "api@manager245:api1111",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
$response = json_decode($response);

curl_close($curl);
$prod = $items_ms = array();
  	foreach($response->rows as $product_ms) { 
      // dd($product_ms);die();
      $prod['code'] = $product_ms->code;  // выбираем код из ассортимента в мс и если нет то добавляем в таблицу в бд
      // $prod['code'] = 9496;
      $id = mysqli_fetch_row(@mysqli_query($mysql,"SELECT `id` FROM `rtd_ms_assorts`  WHERE `code_ms`= '{$prod['code']}' "))[0];
      dd($id);
      // dd($product_ms->id);
      // 457432f9-728b-11eb-0a80-07b000072ebc
        if (empty($id)) {
          mysqli_query($mysql,"INSERT INTO `rtd_ms_assorts` (`id`, `id_ms`, `code_ms`) VALUES (NULL, '{$product_ms->id}', '{$product_ms->code}')");
          $prod['id'] = $product_ms->id;
          $prod['code'] = $product_ms->code;
          $items_ms[] = $prod;
        }
        // else{
        //   $s = mysqli_query($mysql,"UPDATE `rtd_ms_assorts` SET `id_ms`= '{$product_ms->id}' , `code_ms`= '{$product_ms->code}' WHERE `id`= '$id'");
        // dd($s);
        // }
        // exit;
    }
  
return $items_ms;
}