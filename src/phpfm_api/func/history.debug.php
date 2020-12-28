<?php
require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../clazz/history.class.php';
require_once dirname(__FILE__) . '/../clazz/utility.class.php';

@session_start();

$history = Utility::get_history(false);
if ($history != null) {
    if (isset($_GET['clear']))
        $history->clear();

    $history->debug();
} else {
    echo 'NULL';
}

?>
