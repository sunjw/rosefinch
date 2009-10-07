<?php
//完成集成
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/gettext.inc.php";
require_once "../inc/messageboard.class.php";
require_once "../inc/utility.class.php";
require_once "../log/log.func.php";

@session_start();

$sub_dir = rawurldecode(post_query("subdir"));
$name = post_query("newname");

$files_base_dir = Utility::get_file_base_dir();//$_SESSION['base_dir'];
$messageboard = Utility::get_messageboard();

//echo "$sub_dir\n";
//echo "$name\n";
//echo "$files_base_dir\n";

$success = false;
if(false === strpos($sub_dir, "..") && Utility::check_name($name)) // 过滤
{
	$name = $files_base_dir . $sub_dir . $name;
	log_to_file("mkdir: $name");
	$name = convert_toplat($name);
	if(!file_exists($name))
		$success = mkdir($name);
}

if($success)
	$messageboard->set_message(_("Make new folder:") . "&nbsp;" . post_query("newname") . "&nbsp;" . _("succeed"));
else
	$messageboard->set_message(_("Make new folder:") . "&nbsp;" . post_query("newname") . "&nbsp;<strong>" . _("failed") . "</strong>");


Utility::redirct_after_oper(false, 1);

?>