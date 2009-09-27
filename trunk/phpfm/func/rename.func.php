<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/gettext.inc.php";
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
	$messageboard->set_message(sprintf(_("Rename %s to %s ") . _("succeed"), post_query("oldname"), post_query("newname")));
else
	$messageboard->set_message(sprintf(_("Rename %s to %s ") . " <strong>" . _("failed") . "<strong>", post_query("oldname"), post_query("newname")));

Utility::redirct_after_oper(false, 1);

?>