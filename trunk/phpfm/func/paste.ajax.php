<?php
//完成集成
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/clipboard.class.php";
require_once "../inc/utility.class.php";
require_once "../log/log.func.php";

@session_start();

$returnURL = rawurldecode(post_query("return"));

if($returnURL == "")
{
	$returnURL = "../index.php";
}

$target_subdir = rawurldecode(post_query("subdir"));

$clipboard = Utility::get_clipboard(false); //isset($_SESSION['clipboard']) ? $_SESSION['clipboard'] : null;
if($clipboard != null)
{
	$clipboard->paste($target_subdir);
}

//print_r($_GET);

echo "ok";
?>