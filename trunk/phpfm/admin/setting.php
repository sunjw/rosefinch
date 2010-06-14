<?php
$begin_time = microtime(true);

require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/utility.class.php";
require_once "progress.php";
require_once "../log/log.func.php";

@session_start();

log_to_file($_SERVER['REMOTE_ADDR']." setting...");

//print_r($_GET);
//print_r($_POST);
$settings = array();

$timezone = date_default_timezone_get();

$settings = array('root_type' => FILE_POSITION,
				'root_path' => FILES_DIR,
				'charset' => PLAT_CHARSET,
				'timezone' => $timezone,
				'language' => LOCALE,
				'title_name' => TITLENAME,
				'lightbox' => LIGHTBOX,
				'audioPlayer' => AUDIOPLAYER);

$wrong = false;
$display_msg = false;

if(isset($_POST['settingsForm']))
{
	if(!save_settings($settings))
	{
		$wrong = true;
	}
	$display_msg = true;
}

$settings['root_path'] = str_replace("\\\\", "\\", $settings['root_path']); // 修正显示

$locale = $settings['language'];
putenv("LANG=" . $locale);
setlocale(LC_ALL, $locale);

$lang_dir = "locales";

$directory = get_base_dir() . $lang_dir;

$domain = "phpfm";

bindtextdomain($domain, $directory);
bind_textdomain_codeset($domain, get_encoding()); // 让 gettext 以 utf-8 读取 mo
textdomain($domain);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo _("Setting"); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link href="../css/com.css" rel="stylesheet" type="text/css" />
	<link href="../css/message.css" rel="stylesheet" type="text/css" />
	<link href="../css/setting.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" language="javascript" src="../js/jquery-1.3.2.min.js"></script>
</head>
<body>
    <div id="nav">
        <?php Utility::html_navigation("setting"); ?>
        <div class="clear"></div>
    </div>
    <div id="header">
    	<div id="mainTitle">
    		<?php echo _("Setting"); ?>
        </div>
        <div id="subTitle">
    		<?php echo _("Set preferences of your Rosefinch."); ?>
    	</div>
    </div>
    <div id="content">
    	<div id="phpfmMessage" <?php if($display_msg)print("style='display:block' "); if($wrong)print("class='wrong' "); ?>>
    		<?php 
    		if($wrong)
    			echo _("There is something wrong in your settings.");
    		else
    			echo _("Settings have been changed. Go to <a href='../index.php'>index page</a> and see.");
    		?>
    	</div>
    	<?php include "settings.form.php"; ?>
    </div>
    <div id="footer">
        <?php Utility::html_copyright_info($begin_time); ?>
    </div>
</body>
</html>
