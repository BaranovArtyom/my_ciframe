$logger = __DIR__.'/logger.log';
$size = filesize($logger);
if ($size>1) file_put_contents($logger, '');