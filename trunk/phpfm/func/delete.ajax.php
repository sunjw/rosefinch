<?php

require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/messageboard.class.php";
require_once "../inc/utility.class.php";
require_once "../log/log.func.php";

@session_start();

//print_r($_POST);

//$subdir = rawurldecode(post_query("subdir"));
$items = post_query("items");

$items = split("[|]", $items);

$items = Utility::filter_paths($items);

//echo "$subdir\n";
//print_r($files);

$messageboard = Utility::get_messageboard();
$message = "";

$files_base_dir = Utility::get_file_base_dir();
$count = count($items);
for($i = 0; $i < $count; $i++)
{
	$success = false;
	$item = $items[$i];
	$path = $files_base_dir . $item;
	log_to_file("try to delete: $path");
	$message .= ("删除 $item ");
	$path = convert_toplat($path);
	if(file_exists($path))
	{
		if(is_dir($path))
		{
			$success = Utility::phpfm_rmdir($path);
		}
		else
		{
			$success = unlink($path);
		}
	}
	if($success)
		$message .= "成功<br />";
	else
		$message .= "<strong>失败</strong><br />";
}

$messageboard->set_message($message);

echo "ok";

?>