<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/messageboard.class.php";
require_once "../inc/utility.class.php";
require_once "../log/log.func.php";

//print_r($_POST);
//print_r($_FILES);

@session_start();

$messageboard = Utility::get_messageboard();

$sub_dir = rawurldecode(post_query("subdir"));

$files_base_dir = Utility::get_file_base_dir();//$_SESSION['base_dir'];

if(isset($_FILES['uploadFile']))
{
	$uploadfile = $files_base_dir. $sub_dir . $_FILES['uploadFile']['name'];
	
	if (Utility::phpfm_move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadfile)) {
		$messageboard->set_message("上传:&nbsp;" . $_FILES['uploadFile']['name'] . " 成功");
		log_to_file("upload success: " . $uploadfile);
	} else {
		$messageboard->set_message("上传:&nbsp;" . $_FILES['uploadFile']['name'] . " <strong>失败<strong>");
		log_to_file("upload failed: " . $uploadfile);
	}
}

//echo "$sub_dir\n";
//echo "$name\n";
//echo "$files_base_dir\n";

Utility::redirct_after_oper(false, 1);

?>