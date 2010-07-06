<?php
$begin_time = microtime(true);

require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/gettext.inc.php";
require_once "../inc/utility.class.php";
require_once "../inc/ez_sql_core.php"; // Include ezSQL core
require_once "../inc/ez_sql_mysql.php"; // Include ezSQL database specific component
require_once "../log/log.func.php";

@session_start();

set_response_utf8();

if(!SEARCH)
{
	redirect("setting.php");
}

function check_table($db)
{
	$query = "SHOW TABLES";
	$rows = $db->get_results($query, ARRAY_N);
	//print_r($rows);
	if($rows != null)
	{	
		foreach($rows as $row)
		{
			//echo $row[0];
			if($row[0] == "fileindex")
			{
				return true;
			}
		}
	}
	
	return false;
}

function save_settings(&$settings)
{
	$settings['db_user'] = post_query("dbUser");
	$settings['db_pswd'] = post_query("dbPswd");
	$settings['db_name'] = post_query("dbName");
	$settings['db_host'] = post_query("dbHost");
	
	if($settings['db_user'] == "" ||
		$settings['db_pswd'] == "" ||
		$settings['db_name'] == "" ||
		$settings['db_host'] == "")
	{
		return false;
	}
	
	// Initialise database object and establish a connection
	// at the same time - db_user / db_password / db_name / db_host
	$db = new ezSQL_mysql($settings['db_user'], $settings['db_pswd'], $settings['db_name'], $settings['db_host']);
	
	$db->hide_errors();
	
	$query = "set names 'utf8'";
	$ret = $db->query($query);
	if((!is_numeric($ret) && !$ret))
	{
		return false; // Cannot connect to database
	}
	
	$has_table = check_table($db);

	if(!$has_table)
	{
		// 表不存在，试着创建
		$query = "CREATE TABLE IF NOT EXISTS `fileindex` (
			  `path_hash` varchar(32) NOT NULL,
			  `path` text NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `size` int(10) unsigned default NULL,
			  `type` varchar(50) NOT NULL,
			  `modified` datetime NOT NULL,
			  `refreshed` tinyint(1) NOT NULL,
			  PRIMARY KEY  (`path_hash`),
			  KEY `name` (`name`),
			  KEY `type` (`type`),
			  KEY `size` (`size`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$ret = $db->query($query);
		
		if((!is_numeric($ret) && !$ret))
		{
			return false; // Cannot connect to database
		}
		
		if(!check_table($db))
		{
			return false; // 数据库表添加失败
		}
	}
	
	// 保存设置
	$file_name = "database.inc.tpl";
	$settings_tpl = fopen($file_name, "r");
	$settings_str = fread($settings_tpl, filesize($file_name));
	fclose($settings_tpl);
	//print_r( $settings);
	
	$templates = array("&&DB_USER&&", 
						"&&DB_PSWD&&", 
						"&&DB_NAME&&",
						"&&DB_HOST&&");
	$values = array($settings['db_user'],
					$settings['db_pswd'],
					$settings['db_name'],
					$settings['db_host']);
			
	$settings_str = str_replace($templates, $values, $settings_str);
	//echo $settings;
	
	$settings_php = fopen("database.inc.php", "w");
	fwrite($settings_php, $settings_str); // 写回配置文件
	fclose($settings_php);
	
	return true;
}

//print_r($_GET);
//print_r($_POST);
$settings = array();

$ok = false;
$display_msg = false;

if(isset($_POST['settingsForm']))
{
	if(save_settings($settings))
	{
		redirect("setting.php");
	}
	$display_msg = true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo _("Database Setting"); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link href="../css/com.css" rel="stylesheet" type="text/css" />
	<link href="../css/message.css" rel="stylesheet" type="text/css" />
	<link href="../css/setting.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" language="javascript" src="../js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" language="javascript" src="../js/setting.js"></script>
</head>
<body>
    <div id="nav">
        <?php Utility::html_navigation("setting"); ?>
        <div class="clear"></div>
    </div>
    <div id="header">
    	<div id="mainTitle">
    		<?php echo _("Database Setting"); ?>
        </div>
        <div id="subTitle">
    		<?php echo _("Set database parameters"); ?>
    	</div>
    </div>
    <div id="content">
    	<div id="phpfmMessage" <?php if($display_msg)print("style='display:block' "); if($wrong)print("class='wrong' "); ?>>
    		<?php 
    		if(!$ok)
    			echo _("There is something wrong in your settings.");
    		else
    			echo "!!!!!";
    		?>
    	</div>
    	<form id="phpfmDatabaseForm" action="<?php echo get_URI(); ?>" method="post">
        	<input type="hidden" name="settingsForm" value="settingsForm" />
        	<fieldset>
        		<legend><?php echo _("Basic Settings"); ?></legend>
        		<div>
	        		<label for="dbUser"><?php echo _("Database user name") . ":"; ?></label>
	        		<input id="dbUser" type="text" maxlength="256" value="<?php echo $settings['db_user']; ?>" name="dbUser"/>
	        		<div class="info">
	        			
	        		</div>
        		</div>
        		<div>
	        		<label for="dbPswd"><?php echo _("Database user password") . ":"; ?></label>
	        		<input id="dbPswd" type="password" maxlength="256" value="" name="dbPswd"/>
	        		<div class="info">
	        			
	        		</div>
        		</div>
        		<div>
	        		<label for="dbName"><?php echo _("Database name") . ":"; ?></label>
	        		<input id="dbName" type="text" maxlength="256" value="<?php echo $settings['db_name']; ?>" name="dbName"/>
	        		<div class="info">
	        			
	        		</div>
        		</div>
        		<div>
	        		<label for="dbHost"><?php echo _("Database host") . ":"; ?></label>
	        		<input id="dbHost" type="text" maxlength="256" value="<?php echo $settings['db_host']; ?>" name="dbHost"/>
	        		<div class="info">
	        			
	        		</div>
        		</div>
        	</fieldset>
        	<input type="submit" value="<?php echo _("OK"); ?>" />
        </form>
    </div>
    <div id="footer">
        <?php Utility::html_copyright_info($begin_time); ?>
    </div>
</body>
</html>
