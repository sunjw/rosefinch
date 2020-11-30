<?php
$begin_time = microtime(true);

require_once "inc/defines.inc.php";
require_once "inc/common.inc.php";
require_once "inc/filemanager.class.php";
require_once "inc/utility.class.php";
require_once "log/log.func.php";

@session_start();

get_logger()->info("index.php visited.");

$fileManager = new FileManager("index.php", "index.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>
        <?php
        echo $fileManager->title()." - ".$fileManager->get_current_dir();
        ?>
    </title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <meta name="viewport" content="width=540" />
    <link href="css/com.css" rel="stylesheet" type="text/css" />
    <?php echo $fileManager->html_include_files(DEBUG); ?>
</head>
<body>
    <div id="header">
        <div id="nav">
            <?php Utility::html_navigation(); ?>
            <div class="clear"></div>
        </div>
        <div id="loginStatus">
        <?php
        echo Utility::display_user();
        ?>
        </div>
    </div>
    <div id="content">
        <?php
        $fileManager->display_toolbar();
        $fileManager->display_full_path(); // display full path.
        $fileManager->display_main_view();

        // 准备部分
        $fileManager->display_func_pre();
        ?>
    </div>
    <?php
    if(!is_mobile_browser())
    {
    ?>
    <div id="footer">
        <?php Utility::html_copyright_info($begin_time); ?>
    </div>
    <?php
    }
    ?>
</body>
</html>
