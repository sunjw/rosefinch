<?php
require_once dirname(__FILE__) . "/../inc/defines.inc.php";
require_once dirname(__FILE__) . "/../inc/common.inc.php";
require_once "../clazz/messageboard.class.php";
require_once "../log/log.func.php";

@session_start();

$messageboard = isset($_SESSION['messageboard']) ? $_SESSION['messageboard'] : null;
if ($messageboard != null) {
    $messageboard->debug();
}
?>