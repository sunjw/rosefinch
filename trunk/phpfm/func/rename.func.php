<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/messageboard.class.php";
require_once "../inc/utility.class.php";
require_once "../log/log.func.php";

@session_start();

$sub_dir = rawurldecode(post_query("subdir"));
$oldname = post_query("oldname");
$newname = post_query("newname");

$files_base_dir = Utility::get_file_base_dir();//$_SESSION['base_dir'];
$messageboard = Utility::get_messageboard();

$success = false;
if(false === strpos($sub_dir, "..") &&
	Utility::check_name($newname) && Utility::check_name($oldname)) // 过滤
{
	if($newname != $oldname)
	{
		$oldname = $files_base_dir . $sub_dir . $oldname;
		$newname = $files_base_dir . $sub_dir . $newname;
		
		log_to_file("Try to rename: $oldname to $newname");
		
		$success = Utility::phpfm_rename($oldname, $newname);
	}
	
}
if($success)
	$messageboard->set_message("重命名:&nbsp;" . post_query("oldname") . " 为 " . post_query("newname") . " 成功");
else
	$messageboard->set_message("重命名:&nbsp;" . post_query("oldname") . " 为 " . post_query("newname") . " <strong>失败<strong>");

Utility::redirct_after_oper(false, 1);

?>