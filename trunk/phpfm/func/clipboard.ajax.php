<?php
//完成集成
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/gettext.inc.php";
require_once "../inc/clipboard.class.php";
require_once "../inc/messageboard.class.php";
require_once "../inc/utility.class.php";
require_once "../log/log.func.php";

@session_start();

//print_r($_POST);
$messageboard = Utility::get_messageboard();

$clipboard = Utility::get_clipboard(false); 
if($clipboard != null)
{
	$oper = post_query("oper");
	$items = post_query("items");
//	echo "$oper\n";
//	echo "$subdir\n";
//	echo "$files\n";
	
	$items = split("[|]", $items);
	$items = Utility::filter_paths($items);
	//print_r($files);
	
	$clipboard->set_items($oper, $items);
	
	if($clipboard->have_items())
	{
		$message = _("Add items to clipboard:") . "&nbsp;<br />";//"向剪贴板添加项目:&nbsp;<br />";
		$message .= (join("<br />", $items));
		$messageboard->set_message($message);
		echo "ok";
	}
}

?>