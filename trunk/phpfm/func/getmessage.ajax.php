<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/messageboard.class.php";
require_once "../log/log.func.php";

@session_start();

$messageboard = isset($_SESSION['messageboard']) ? $_SESSION['messageboard'] : null;
if($messageboard != null)
{
	echo $messageboard->get_message();
}
?>