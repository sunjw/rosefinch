<?php
$begin_time = microtime(true);

require_once dirname(__FILE__) . '/inc/defines.inc.php';
require_once dirname(__FILE__) . '/inc/common.inc.php';
require_once dirname(__FILE__) . '/clazz/filemanager.class.php';
require_once dirname(__FILE__) . '/clazz/utility.class.php';
require_once dirname(__FILE__) . '/log/log.func.php';

@session_start();

get_logger()->info('index.php visited.');

$file_manager = new FileManager('index.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>
        <?php
        echo $file_manager->title() . ' - ' . $file_manager->get_current_dir();
        ?>
    </title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <meta name="viewport" content="width=540" />
    <link href="css/com.css" rel="stylesheet" type="text/css" />
    <?php echo $file_manager->html_include_files(DEBUG); ?>
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
        $file_manager->display_toolbar();
        $file_manager->display_full_path(); // display full path
        $file_manager->display_main_view();

        // function preparation
        $file_manager->display_func_pre();
        ?>
    </div>
    <?php
    if (!is_mobile_browser())
    {
    ?>
    <div id="footer">
        <?php Utility::html_footer($begin_time); ?>
    </div>
    <?php
    }
    ?>
</body>
</html>
