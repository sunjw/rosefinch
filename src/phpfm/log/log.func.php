<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

define('LOG_CODE', 'UTF-8');
define('LOG_FILE_NAME', 'phpfm.log');

$logger = new Logger('phpfm');
$logger->pushHandler(new StreamHandler(dirname(__FILE__) . LOG_FILE_NAME));

function get_logger()
{
    global $logger;
    return $logger;
}

?>
