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
$DB_ARG = "database";
$mode = 0;
$arg = get_query("mode");
if($arg == $DB_ARG)
	$mode = 1;
else if($arg == $USERMNG_ARG)
	$mode = 2;

if($mode == 0)
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
else if($mode == 1)
{
	
}
else if($mode == 2)
{
	$settings = array('language' => LOCALE,
					'rose_browser' => ROSE_BROWSER,
					'rose_modify' => ROSE_MODIFY,
					'rose_admin' => ROSE_ADMIN);
}

if(isset($_POST['settingsForm']))
{
	if(!save_settings($settings, $mode))
	{
    	Utility::get_messageboard()->set_message(_("There is something wrong in your settings."), 2);
	}
	else
	{
		Utility::get_messageboard()->set_message(_("Settings have been changed. Go to <a href='../index.php'>index page</a> and see."), 1);
	}
}

if($mode == 0)
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
    <script type="text/javascript" language="javascript" src="../js/dialog.min.js"></script>
    <script type="text/javascript" language="javascript" src="../js/setting.js"></script>
    <script type="text/javascript" language="javascript">
  	//<![CDATA[
  		var Strings = new Array();
  		<?php 
  		echo "Strings['return'] = '".rawurlencode(get_URI())."';";
  		echo "Strings['OK'] = '"._('OK')."';";
  		echo "Strings['Cancel'] = '"._('Cancel')."';";
  		echo "Strings['Never mind...'] = '"._('Never mind...')."';";
  		echo "Strings['Are you sure to logout?'] = '"._('Are you sure to logout?')."';";
  		echo "Strings['User'] = '"._('User')."';";
  		echo "Strings['Username:'] = '"._('Username:')."';";
  		echo "Strings['Password:'] = '"._('Password:')."';";
  		echo "Strings['Permission:'] = '"._('Permission:')."';";
  		echo "Strings['Working...'] = '"._('Working...')."';";
  		echo "Strings['Done'] = '"._('Done')."';";
  		echo "Strings['Add'] = '"._('Add')."';";
  		echo "Strings['Delete'] = '"._('Delete')."';";
  		echo "Strings['Are you sure to delete this user?'] = '"._('Are you sure to delete this user?')."';";
  		echo "Strings['Modify'] = '"._('Modify')."';";
  		echo "Strings['Change Password'] = '"._('Change Password')."';";
  		echo "Strings['Old:'] = '"._('Old:')."';";
  		echo "Strings['New:'] = '"._('New:')."';";
  		echo "Strings['Repeat:'] = '"._('Repeat:')."';";
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
    	<div id="phpfmDocNav">
    		<?php 
    		if($mode == 0)
    		{
    			echo _("Setting"); ?>&nbsp;|&nbsp;<a class="" title="<?php echo _("Database Setting"); ?>" href="setting.php?mode=<?php echo $DB_ARG; ?>"><?php echo _("Database Setting"); ?></a><?php if(USERMNG){?>&nbsp;|&nbsp;<a class="" title="<?php echo _("User Management"); ?>" href="setting.php?mode=<?php echo $USERMNG_ARG; ?>"><?php echo _("User Management"); ?></a><?php }?>
    		<?php 
    		}
    		else if($mode == 1)
    		{
    		?>
    			<a class="" title="<?php echo _("Setting"); ?>" href="setting.php"><?php echo _("Setting"); ?></a>&nbsp;|&nbsp;<?php echo _("Database Setting"); ?><?php if(USERMNG){?>&nbsp;|&nbsp;<a class="" title="<?php echo _("User Management"); ?>" href="setting.php?mode=<?php echo $USERMNG_ARG; ?>"><?php echo _("User Management"); ?></a><?php }?>
      		<?php 
      		}
    		else if($mode == 2 && ($settings['usermng'] || USERMNG))
    		{
    		?>
    			<a class="" title="<?php echo _("Setting"); ?>" href="setting.php"><?php echo _("Setting"); ?></a>&nbsp;|&nbsp;<a class="" title="<?php echo _("Database Setting"); ?>" href="setting.php?mode=<?php echo $DB_ARG; ?>"><?php echo _("Database Setting"); ?></a><?php if(USERMNG){?>&nbsp;|&nbsp;<?php echo _("User Management");  ?><?php }?>
      		<?php 
    		} 
    		?>
      	</div>
    	<div id="phpfmMessage">
    	</div>
    	<?php 
    	if(Utility::allow_to_admin())
    	{
    		if($mode == 0)
				include "settings.form.php";
			else if($mode == 1)
    			include "db.form.php";
			else if($mode == 2)
    			include "usermng.form.php";
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
