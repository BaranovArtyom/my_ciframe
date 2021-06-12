<?php
ini_set('display_errors', 'on');
require_once "funcs.php";
require_once "config.php";

$size = filesize('logger.log');
dd($size);
if ($size>11462000) file_put_contents('logger.log', '');
dd($size);
exit;
/**получение размер продаж розницы*/

$all_retailDemands = array();
$page = 0; $limit = 1000;
$demand_size = getSizeRetailDemand();			    // получаем размер продаж розницы
$max_pages = ceil($demand_size / $limit);           // количество страниц

/**перебор всех  продаж розницы*/
while ($page < $max_pages) {
	$offset = $page * $limit;
	$all_retailDemands = getRDemand($offset);            // все 
        foreach ($all_retailDemands as $retailDemand) {
            /**заполнение таблицы */
            $date = explode(' ', $retailDemand->moment); // получение даты 
            $data = strtotime($date[0]);
            $number_order = $retailDemand->name;
            $kol_pos = $retailDemand->positions->meta->size;
            $sum_order = $retailDemand->sum;
            $id_seller = $retailDemand->owner->meta->href;
            $id_agent = $retailDemand->agent->meta->href;
            $positions = $retailDemand->positions->meta->href;

            if (!$check=@mysqli_fetch_row(@mysqli_query($db,"SELECT `number_order` FROM `retail_demand`  WHERE `number_order`= '$number_order'"))[0]){
                /**заполнение таблицы в retail_demand в бд */
                $insertDB = mysqli_query($db,"INSERT INTO `retail_demand` (`id`, `number_order`, `moment`,`kol_pos`,`sum_order`,`id_seller`,`id_agent`,`href_positions`) 
                VALUES (NULL, '{$number_order}','{$data}','{$kol_pos}','{$sum_order}','{$id_seller}','{$id_agent}','{$positions}') ");
                file_put_contents('logger.log',date('Y-m-d H:i:s').'  создания розницы - '.$insertDB."\n",FILE_APPEND);
                dd($insertDB);
            }
            file_put_contents('logger.log',date('Y-m-d H:i:s').'  нет  новой розницы - '."\n",FILE_APPEND);
        }
    $page++;
}

/**отрузки */
$all_Demands = array();
$page = 0; $limit = 1000;
$d_size = getSizeD();			                      // получаем размер отгузок
$max_pages_demand = ceil($d_size / $limit);           // количество страниц
// dd($d_size);
// dd($max_pages_demand);
// exit;

/**перебор всех  отгрузок*/
while ($page < $max_pages_demand) {
	$offset = $page * $limit;
	$all_Demands = getDemand_bd($offset);            // все 
        foreach ($all_Demands as $Demand) {
            /**заполнение таблицы */
            // dd($Demand);exit;
            $date_d = explode(' ',$Demand->moment); // получение даты 
            $data = strtotime($date_d[0]);
            // dd($data);exit;
            $number_order_d = $Demand->name;
            $kol_pos_d = $Demand->positions->meta->size;
            $sum_order_d = $Demand->sum;
            $id_seller_d = $Demand->owner->meta->href;
            $id_agent_d = $Demand->agent->meta->href;
            $positions_d = $Demand->positions->meta->href;

            if (!$check_d=@mysqli_fetch_row(@mysqli_query($db,"SELECT `number_order` FROM `demand`  WHERE `number_order`= '$number_order_d'"))[0]){
                /**заполнение таблицы в retail_demand в бд */
                $insert_d = mysqli_query($db,"INSERT INTO `demand` (`id`, `number_order`, `moment`,`kol_pos`,`sum_order`,`id_seller`,`id_agent`,`href_positions`) 
                VALUES (NULL, '{$number_order_d}','{$data}','{$kol_pos_d}','{$sum_order_d}','{$id_seller_d}','{$id_agent_d}','{$positions_d}') ");
                dd($insert_d);
            }
           
        }
    $page++;
}

/**получение всех продавцов */
$getSels = getSel();
    foreach($getSels as $sel) {
        if (!$check_sel=@mysqli_fetch_row(@mysqli_query($db,"SELECT `id_seller` FROM `seller`  WHERE `id_seller`= '{$sel->meta->href}'"))[0]){
            /**заполнение таблицы в seller в бд */
            $insert_sel_DB = mysqli_query($db,"INSERT INTO `seller` (`id`, `id_seller`,`name`) 
            VALUES (NULL, '{$sel->meta->href}','{$sel->name}') ");
            dd($insert_sel_DB);
        }
    }
// dd($getSel);
/**получение всех агентов */

$all_getAgent = array();
$page = 0; $limit = 1000;
$getAgent = getAgent();
$agent_size = $getAgent->meta->size;			    // получаем размер агентов
// dd($agent_size);exit;
$max_pages_agent = ceil($agent_size / $limit);           // количество страниц
// $getAgent = getAgent();
// dd($getAgent);exit;
// /**перебор всех  продаж розницы*/
while ($page < $max_pages_agent) {
	$offset = $page * $limit;
	$all_getAgent = getAllagent($offset);            // все 
        foreach($all_getAgent as $agent) {
            if (!$check_agent=@mysqli_fetch_row(@mysqli_query($db,"SELECT `id_agent` FROM `agent`  WHERE `id_agent`= '{$agent->meta->href}'"))[0]){
                /**заполнение таблицы в seller в бд */
                $insert_agent_DB = mysqli_query($db,"INSERT INTO `agent` (`id`, `id_agent`,`name`) 
                VALUES (NULL, '{$agent->meta->href}','{$agent->name}') ");
                dd($insert_agent_DB);
            }
        }
    $page++;
}

