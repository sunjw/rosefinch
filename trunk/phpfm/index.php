<?php

require_once "inc/defines.inc.php";
require_once "inc/common.inc.php";
require_once "inc/filemanager.class.php";
require_once "log/log.func.php";

@session_start();

log_to_file($_SERVER['REMOTE_ADDR']." visited.");

$fileManager = new FileManager();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $fileManager->title(); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link href="css/com.css" rel="stylesheet" type="text/css" />
    <?php echo $fileManager->html_include_files(); ?>
</head>
<body>
	<div id="header">
        
    </div>
    <div id="nav">
        <?php $fileManager->html_navigation(); ?>
        <div class="clear"></div>
    </div>
    <div id="content">
        <?php 
        $fileManager->display_full_path(); // 显示全路径
	    $fileManager->display_toolbar();
	    $fileManager->display_main_view();
		
	    // Ajax 部分
	    $fileManager->display_ajax_pre();
        ?>
    </div>
    <div id="footer">
        <?php $fileManager->html_copyright_info(); ?>
    </div>
</body>
</html>
