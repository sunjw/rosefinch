<?php

define("LOG_CODE", "UTF-8");
define("LOG_FILE_NAME", "phpfm.log");

/**
 * 将信息记录到日志文件
 * 日志文件位于 /log/LOG_FILE_NAME
 * @param $content 记录的内容
 */
function log_to_file($content)
{
    $log_path = dirname(__FILE__); // 获得 log.php 文件夹的路径
    $log_file = @fopen($log_path . "/" . LOG_FILE_NAME, "a+");
    if ($log_file !== FALSE) {
        $len = mb_strlen($content, LOG_CODE);
        $last_char = mb_substr($content, $len - 1, 1, LOG_CODE);
        if ($last_char != "\n") {
            $content .= "\n";
        }
        fwrite($log_file, @date("Y-m-d H:i:s") . " " . $_SERVER['REMOTE_ADDR'] . ": " . $content);
        fclose($log_file);
    }
}

?>
