<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/history.class.php";
require_once "../inc/utility.class.php";

@session_start();

$history = Utility::get_history(false);
if ($history != null) {
    if (isset($_GET['clear']))
        $history->clear();

    $history->debug();
} else {
    echo "NULL";
}

?>