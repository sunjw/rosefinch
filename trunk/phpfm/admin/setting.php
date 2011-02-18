<?php
$begin_time = microtime(true);

require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/utility.class.php";
require_once "progress.php";
require_once "../log/log.func.php";

@session_start();

set_response_utf8();

log_to_file($_SERVER['REMOTE_ADDR']." setting...");

//print_r($_GET);
//print_r($_POST);
$settings = array();

$timezone = date_default_timezone_get();

$USERMNG_ARG = "usermng";
$usermng = 0;
$arg = get_query("mode");
if($arg == $USERMNG_ARG)
	$usermng = 1;

if($usermng)
{
	$settings = array('language' => LOCALE,
					'rose_browser' => ROSE_BROWSER,
					'rose_modify' => ROSE_MODIFY,
					'rose_admin' => ROSE_ADMIN);
}
else
{
	$settings = array('root_type' => FILE_POSITION,
					'root_path' => FILES_DIR,
					'charset' => PLAT_CHARSET,
					'timezone' => $timezone,
					'language' => LOCALE,
					'title_name' => TITLENAME,
					'lightbox' => LIGHTBOX,
					'audioPlayer' => AUDIOPLAYER,
					'search' => SEARCH,
					'usermng' => USERMNG);
}

$wrong = false;
$display_msg = false;

if(isset($_POST['settingsForm']))
{
	if(!save_settings($settings, $usermng))
	{
		$wrong = true;
	}
	$display_msg = true;
}

if($usermng)
{
	
}
else
{
	$settings['root_path'] = str_replace("\\\\", "\\", $settings['root_path']); // 修正显示
}

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
	<link href="../css/func.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" language="javascript" src="../js/jquery-1.4.4.min.js"></script>
    <script type="text/javascript" language="javascript" src="../js/setting.js"></script>
    <script type="text/javascript" language="javascript">
  	//<![CDATA[
  		var Strings = new Array();
  		<?php 
  		echo "Strings['OK'] = '"._('OK')."';\n";
  		echo "Strings['Cancel'] = '"._('Cancel')."';\n";
  		echo "Strings['Never mind...'] = '"._('Never mind...')."';\n";
  		echo "Strings['Are you sure to logout?'] = '"._('Are you sure to logout?')."';\n";
  		echo "Strings['User'] = '"._('User')."';\n";
  		echo "Strings['Username:'] = '"._('Username:')."';\n";
  		echo "Strings['Password:'] = '"._('Password:')."';\n";
  		echo "Strings['return'] = '".rawurlencode(get_URI())."';\n";
  		?>
  	//]]>
    </script>
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
    	<div id="loginStatus">
    		<?php 
    		echo Utility::display_user();
    		?>
    	</div>
    </div>
    <div id="content">
    	<?php 
    	if($settings['usermng'] || ($usermng && USERMNG))
    	{
    	?>
    	<div id="phpfmDocNav">
    		<?php 
    		if($usermng)
    		{
    		?>
    			<a class="" title="<?php echo _("Setting"); ?>" href="setting.php"><?php echo _("Setting"); ?></a>&nbsp;|&nbsp;<?php echo _("User Management");  ?>
    		<?php }
    		else
    		{
    			echo _("Setting"); ?>&nbsp;|&nbsp;<a class="" title="<?php echo _("User Management"); ?>" href="setting.php?mode=<?php echo $USERMNG_ARG; ?>"><?php echo _("User Management"); ?></a>
      		<?php } ?>
      	</div>
      	<?php } ?>
    	<div id="phpfmMessage" <?php if($display_msg)print("style='display:block'"); if($wrong)print("class='wrong' "); ?>>
    		<?php 
    		if($wrong)
    			echo _("There is something wrong in your settings.");
    		else
    			echo _("Settings have been changed. Go to <a href='../index.php'>index page</a> and see.");
    		?>
    	</div>
    	<?php 
    	if(Utility::allow_to_admin())
    	{
    		if($usermng)
				include "usermng.form.php";
			else
    			include "settings.form.php";
    	} 
    	else
    	{
    	?>
    	<script type="text/javascript">
		//<![CDATA[
			$(function(){
					Setting.displayLogin();
				});
		//]]>
		</script>
    	<?php 
    	}
    	?>
    </div>
    <div id="footer">
        <?php Utility::html_copyright_info($begin_time); ?>
    </div>
</body>
</html>
