<?php

ini_set('display_errors', 'on');
require_once 'vendor/autoload.php';   

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
// use Monolog\Processor\WebProcessor;
use Monolog\Handler\FirePHPHandler;
use Monolog\Formatter\HtmlFormatter;

// create a log channel
$log = new Logger('log');   // название 
$log->pushHandler(new StreamHandler('logs/my_log.log', Logger::WARNING));
$log->pushHandler(new FirePHPHandler());
// $log->pushProcessor(new WebProcessor());
$log->info('User registered', ['username'=>'jmendez']);
$log->pushProcessor(function ($record) {
    $record['extra']['dummy'] = 'Hello world!';

    return $record;
});
$s = 3;
if ( $s == 3 ){
    $log->warning($s);
    $log->error('Bar',['test']);
    $log->info('Test'); 
}else 
    $log->info("не равна трем", ['s'=>$s]);
// add records to the log
// $log->warning('Foo', ['s'=>$s]);
// $log->error('Bar');
echo $s;