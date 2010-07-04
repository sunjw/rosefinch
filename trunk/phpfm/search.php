<?php
$begin_time = microtime(true);

require_once "inc/defines.inc.php";
require_once "inc/common.inc.php";
require_once "inc/filemanager.class.php";
require_once "inc/utility.class.php";
require_once "log/log.func.php";

@session_start();

log_to_file($_SERVER['REMOTE_ADDR']." visited.");

$fileManager = new FileManager(true);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $fileManager->title()." - "._("Search")." - ".$fileManager->get_search(); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link href="css/com.css" rel="stylesheet" type="text/css" />
    <?php echo $fileManager->html_include_files(DEBUG); ?>
</head>
<body>
    <div id="nav">
        <?php Utility::html_navigation(); ?>
        <div class="clear"></div>
    </div>
    <div id="header">
    	<div id="mainTitle">
    		<?php printf(_("Search \"%s\" in \"%s\""), $fileManager->get_search(), $fileManager->get_current_dir()); ?>
        </div>
        <div id="subTitle">
    		<?php 
    		//echo " - " . $fileManager->get_current_path(); 
    		?>
    	</div>
    </div>
    <div id="content">
        <?php 
        $fileManager->display_full_path(); // 显示全路径
	    $fileManager->display_toolbar();
	    $fileManager->display_main_view();
		
	    // 准备部分
	    $fileManager->display_func_pre();
        ?>
    </div>
    <div id="footer">
        <?php Utility::html_copyright_info($begin_time); ?>
    </div>
</body>
</html>
