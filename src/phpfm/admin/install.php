<?php

define("INSTALL", 1);

require_once "../inc/common.inc.php";
require_once "progress.php";
require_once "../log/log.func.php";

@session_start();

set_response_utf8();

log_to_file($_SERVER['REMOTE_ADDR']." installing...");

//print_r($_GET);
//print_r($_POST);

$settings = array('root_type' => "absolute",
				'root_path' => "",
				'charset' => "UTF-8",
				'timezone' => "",
				'language' => "en_US",
				'title_name' => "Rosefinch",
				'lightbox' => 1,
				'audioPlayer' => 1,
				'search' => 0,
				'usermng' => 0,
				'install' => 1);

$wrong = false;

if(isset($_POST['settingsForm']))
{
	if(save_settings($settings, 0))
	{	
		redirect("../");
	}
	else
	{
		$wrong = true;
	}
}

$settings['root_path'] = str_replace("\\\\", "\\", $settings['root_path']); // 修正显示

$locale = get_query("lang");
if($locale == "")
{
	$locale = "en_US";
}

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
    <title><?php echo _("Install"); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link href="../css/com.css" rel="stylesheet" type="text/css" />
	<link href="../css/message.css" rel="stylesheet" type="text/css" />
	<link href="../css/setting.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" language="javascript" src="../js/jquery-1.8.1.min.js"></script>
</head>
<body>
    <div id="nav">
        
    </div>
    <div id="header">
    	<div id="mainTitle">
    		<?php echo _("Install"); ?>
        </div>
        <div id="subTitle">
    		<?php echo _("Prepare Rosefinch for first time using."); ?>
    	</div>
    </div>
    <div id="content">
    	<div id="phpfmMessage" <?php if($wrong)print("style='display:block' class='wrong'"); ?>>
    		<?php 
    		echo _("There is something wrong in your settings.");
    		?>
    	</div>
    	<form id="installPrefer" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
	    	<label for="lang">Language for install:&nbsp;</label>
	    	<select id="lang" name="lang">
	    		<option value="en_US" <?php if($locale == "en_US")print("selected='selected'"); ?>>English</option>
	    		<option value="zh_CN" <?php if($locale == "zh_CN")print("selected='selected'"); ?>>简体中文</option>
	    	</select>
	    	<input type="submit" value="Change"/>
    	</form>
    	<div class="clear"></div>
    	<?php include "settings.form.php"; ?>
    </div>
    <div id="footer">
        
    </div>
</body>
</html>
