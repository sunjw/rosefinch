<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php'; // absolute path.

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

define('LOG_CODE', 'UTF-8');
define('LOG_FILE_NAME', 'phpfm.log');

$logger = new Logger('phpfm');
$log_file_path = dirname(__FILE__) . '/' . LOG_FILE_NAME;
$stream_handler = new StreamHandler($log_file_path);
$stream_handler->setFormatter(new LineFormatter(null, null, false, true));
$logger->pushHandler($stream_handler);

function get_logger() {
    global $logger;
    return $logger;
}

?>
