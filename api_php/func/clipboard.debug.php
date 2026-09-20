<?php
require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../inc/clipboard.class.php';
require_once dirname(__FILE__) . '/../log/log.func.php';

@session_start();

$clipboard = Utility::get_clipboard(false);
if ($clipboard != null) {
    $clipboard->debug();
} else {
    echo 'NULL';
}

?>
