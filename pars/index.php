<?php

// ini_set('display_errors', 'on');
require_once 'functions.php';
require_once 'connect.php';
require_once __DIR__.'/phpQuery/phpQuery.php'; 

/**
 * первый фид
 */

$xml_file = 'https://seekingalpha.com/feed.xml';              // ссылка с фидом

$xml = simplexml_load_file($xml_file);
    $type = stristr($xml->channel->author->name, '.', true);  // получение type из базы
    $sql = "SELECT code FROM `news` WHERE type = '$type'";    // запрос в бд для выбора поля code по $type 
   
    $Code_arr = getCode($db,$sql);                          // получение массива поля code из бд для сравнения 
    foreach ($Code_arr as $val){
        $code[] = $val['code'];
    }
    if (empty($code))
        echo "кодов нет с таким type"."<br>";
    // dd($code);
    // die();
    foreach ($xml->channel->item as $item) {                 // перебор новостей     
        // dd($xml);
        $ticker = ' ';
        $tickers = ' ';
        $ticks = array();
        foreach ($item->category as $key=>$tic) {         // получение тикеров и автора
            // dd($ticker)."<br>";
            // dd((string)$ticker[0]);
            if ((string)$tic->attributes()['type'] == 'symbol'){
                $ticks[] = addslashes((string)$tic);                  // проверка если тикер или автор
            }else {
                $author =  addslashes((string)$tic);
                
            }
        
        }
        if ( count($ticks)>1 ) {
            $tickers = (implode(",", $ticks));                  
        }else {
            $ticker = $ticks[0];
        }
       
        $news =  addslashes($item->title);                      // получили название новости
        // echo $news;
        date_default_timezone_set('America/New_York'); 
  
        $dtime = strtotime($item->pubDate);
        $otime = date('Y-m-d H:i:s', $dtime);
       
        if (!in_array($item->guid, $code)) {                   // проверка новости по коду в базе, если нет то вставляем в базу
            // echo  $item->guid.'<br>';
            $sql = "INSERT INTO `news` (`id`, `type`, `code`, `news`, `dtime`, `otime`, `author`, `ticker`, `tickers`, `lang`, `fav`) 
                    VALUES (NULL, '$type', '$item->guid', '$news', '$dtime', '$otime', '$author', '$ticker', '$tickers', 'en', '0');";
            $result = mysqli_query($db, $sql) or die("Ошибка " . mysqli_error($db));
            
        }
        
        // надо поменять полю tickers в бд с varchar на text 
        if($result)
                {
                    echo "Выполнение запроса прошло успешно".'<br>';
                }
 
    }
 

/**
 * второй фид 
 */

$xml_file_marketwatch = 'http://feeds.marketwatch.com/marketwatch/topstories/'; 
    $xml = simplexml_load_file($xml_file_marketwatch);
    $type = stristr($xml->channel->title, '.', true);         // получение type из базы

    $sql = "SELECT code FROM `news` WHERE type = '$type'";    // запрос в бд для выбора поля code по $type 
    $Code_arr = getCode($db,$sql);                            // получение массива поля code из бд для сравнения 
    foreach ($Code_arr as $val){
        $code[] = $val['code'];
    }
        if (empty($code))
            echo "кодов нет с таким type"."<br>";
    // dd($code);
    // dd($xml);
    // die();
    
    foreach ($xml->channel->item as $item) {               // перебор новостей     
        
        // dd($item);
        $news =  addslashes($item->title);                 // название новости
                         // название новости 
        date_default_timezone_set('Europe/London');        // установка английского времени
        
        $dtime = strtotime($item->pubDate);                
        $otime = date('Y-m-d H:i:s', $dtime);
        $guid = $item->guid;                                
        // dd($dtime);
        $replace = array("{", "}");                         //получение guid без фиг.скобок
        $guid = str_replace($replace, "", $guid);
       
        $url = $item->link;                                 //cсылки для получения автора и символов
        $file = file_get_contents($url);

        $doc = phpQuery::newDocument($file);                // подключаем библиотеку
        $author = $doc->find('.byline .author h4')->text(); // получаем автора
        
        $tickers = ' ';
        $ticks = array();
            foreach($doc->find('.list--tickers span') as $tic){
                $ticks[] = $tic->textContent;               // получаем тикеры
            }
                if ( count($ticks)>1 ) {
                    $tickers = (implode(",", $ticks));                  
                }else {
                    $ticker = $ticks[0];                    // получаем тикер
                }

        if (!in_array($guid, $code)) {                   // проверка новости по коду в базе, если нет то вставляем в базу
            // echo  $item->guid.'<br>';
            $sql = "INSERT INTO `news` (`id`, `type`, `code`, `news`, `dtime`, `otime`, `author`, `ticker`, `tickers`, `lang`, `fav`) 
                    VALUES (NULL, '$type', '$guid', '$news', '$dtime', '$otime', '$author', '$ticker', '$tickers', 'en', '0');";
            $result = mysqli_query($db, $sql) or die("Ошибка " . mysqli_error($db));
            // var_dump($sql);
        }
        if($result)
        {
            echo "Выполнение запроса прошло успешно".'<br>';
        }
        $ticker = ' ';
        
    }   


/**
 * третьий фид 
 */


$xml_file_investing = 'https://www.investing.com/rss/news_25.rss'; 
$xml = simplexml_load_file($xml_file_investing);
$type = $xml->channel->title;

$sql = "SELECT code FROM `news` WHERE type = '$type'";    // запрос в бд для выбора поля code по $type 
$Code_arr = getCode($db,$sql);                            // получение массива поля code из бд для сравнения 
foreach ($Code_arr as $val){
    $code[] = $val['code'];
}
    if (empty($code))
        echo "кодов нет с таким type"."<br>";
      
    foreach ($xml->channel->item as $item) {               // перебор новостей     
    
        // dd($item);die();
        $news =  addslashes($item->title);     
        $author = $item->author;
        // date_default_timezone_set('Europe/London');        // установка английского времени
        
        $dtime = strtotime($item->pubDate);                
        $otime = date('Y-m-d H:i:s', $dtime);
        // dd($dtime);
        // dd($otime);
        $url = $item->link; 
        $guid = substr($url, -7);
        // echo  $guid;

        if (!in_array($guid, $code)) {                   // проверка новости по коду в базе, если нет то вставляем в базу
            // echo  $item->guid.'<br>';
            $sql = "INSERT INTO `news` (`id`, `type`, `code`, `news`, `dtime`, `otime`, `author`, `ticker`, `tickers`, `lang`, `fav`) 
                    VALUES (NULL, '$type', '$guid', '$news', '$dtime', '$otime', '$author', ' ', ' ', 'en', '0');";
            $result = mysqli_query($db, $sql) or die("Ошибка " . mysqli_error($db));
            // var_dump($sql);
        }
            if($result)
            {
                echo "Выполнение запроса прошло успешно".'<br>';
            }
      

    }
    

/**
 * четвертый фид 
 */


$xml_file_wallstreetsurvivor = 'https://blog.wallstreetsurvivor.com/feed/'; 

$xml = simplexml_load_file($xml_file_wallstreetsurvivor);
$type = $xml->channel->title;
// dd($xml);
// die();
$sql = "SELECT code FROM `news` WHERE type = '$type'";    // запрос в бд для выбора поля code по $type 
$Code_arr = getCode($db,$sql);                            // получение массива поля code из бд для сравнения 
foreach ($Code_arr as $val){
    $code[] = $val['code'];
}
    if (empty($code))
        echo "кодов нет с таким type"."<br>";
        foreach ($xml->channel->item as $item) {               // перебор новостей     
            // dd($item);
            $news =  addslashes($item->title); 
            // dd($item->category);
            date_default_timezone_set('Europe/London');        // установка английского времени
            $dtime = strtotime($item->pubDate);                
            $otime = date('Y-m-d H:i:s', $dtime);
            $link = $item->link; 
            $guid = substr($item->guid, -5);
          
            $file = file_get_contents($link);

            $doc = phpQuery::newDocument($file); 
            $author = $doc->find('.post-meta span:first-child a')->text(); // получаем автора
            
            $author = addslashes($author);
         
            if (!in_array($guid, $code)) {                   // проверка новости по коду в базе, если нет то вставляем в базу
                // echo  $item->guid.'<br>';
                $sql = "INSERT INTO `news` (`id`, `type`, `code`, `news`, `dtime`, `otime`, `author`, `ticker`, `tickers`, `lang`, `fav`) 
                        VALUES (NULL, '$type', '$guid', '$news', '$dtime', '$otime', '$author', ' ', ' ', 'en', '0');";
                $result = mysqli_query($db, $sql) or die("Ошибка " . mysqli_error($db));
                // var_dump($sql);
            }
            if($result)
            {
                echo "Выполнение запроса прошло успешно".'<br>';
            }
            $ticker = ' ';
        }
        

/**
 * пятый фид 
 */
    
$xml_file_wallstreetsurvivor = 'https://www.reddit.com/r/stocks/.rss'; 
$xml = simplexml_load_file($xml_file_wallstreetsurvivor);
$type = $xml->title;

// dd($xml);
// die();
$sql = "SELECT code FROM `news` WHERE type = '$type'";      // запрос в бд для выбора поля code по $type 
$Code_arr = getCode($db,$sql);                              // получение массива поля code из бд для сравнения 
    foreach ($Code_arr as $val){
        $code[] = $val['code'];
    }
    if (empty($code))
        echo "кодов нет с таким type"."<br>";

        foreach ($xml->entry as $item) {                    // перебор новостей     
            // dd($item->author->name);
            $replace = '/u/';                        
            $author = str_replace($replace, "", $item->author->name);
            // dd($item);
            $guid = $item->id;
                    
            $news = addslashes($item->title); 
            // $news  = preg_replace('/\xF0\x9F\x9A\x80/', ' ', $news);
            // $news  = preg_replace('/[\x00-\x99\xAA-\xZZ]+/', ' ', $news);

            date_default_timezone_set('Europe/London');        // установка английского времени
            $dtime = strtotime($item->updated);                
            $otime = date('Y-m-d H:i:s', $dtime);
            // dd($dtime);
            // dd($otime);
            if (!in_array($guid, $code)) {                   // проверка новости по коду в базе, если нет то вставляем в базу
                // echo  $item->guid.'<br>';
                mysqli_query($db,"set names utf8mb4");
                $sql = "INSERT INTO `news` (`id`, `type`, `code`, `news`, `dtime`, `otime`, `author`, `ticker`, `tickers`, `lang`, `fav`) 
                        VALUES (NULL, '$type', '$guid', '$news', '$dtime', '$otime', '$author', ' ', ' ', 'en', '0');";
                
                $result = mysqli_query($db, $sql) or die("Ошибка " . mysqli_error($db));
                
                // var_dump($sql);
            }
            if($result)
            {
                echo "Выполнение запроса прошло успешно".'<br>';
            }
        
        
        }