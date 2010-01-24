<?php

require_once "../inc/common.inc.php";
require_once "progress.php";
require_once "../log/log.func.php";

@session_start();

log_to_file($_SERVER['REMOTE_ADDR']." installing...");

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

//print_r($_GET);
//print_r($_POST);

$settings['root_type'] = "absolute";
$settings['root_path'] = "";
$settings['charset'] = "UTF-8";
$settings['timezone'] = "";
$settings['language'] = "en_US";
$settings['title_name'] = "Rosefinch";
$settings['lightbox'] = 1;

$wrong = false;

if(isset($_POST['installForm']))
{
	if(save_settings($settings))
	{	
		redirect("../");
	}
	else
	{
		$wrong = true;
	}
}

$settings['root_path'] = str_replace("\\\\", "\\", $settings['root_path']); // 修正显示

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo _("Install"); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link href="../css/com.css" rel="stylesheet" type="text/css" />
	<link href="../css/message.css" rel="stylesheet" type="text/css" />
	<link href="../css/install.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" language="javascript" src="../js/jquery-1.3.2.min.js"></script>
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
