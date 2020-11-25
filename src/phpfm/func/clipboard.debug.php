<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/clipboard.class.php";
require_once "../log/log.func.php";

@session_start();

$clipboard = Utility::get_clipboard(false);
if ($clipboard != null) {
    $clipboard->debug();
} else {
    echo "NULL";
}

?>