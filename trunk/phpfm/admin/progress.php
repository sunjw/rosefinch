<?php

require_once "../inc/common.inc.php";
require_once "../inc/utility.class.php";

function check_table($db)
{
	if($db == null)
		return false;
	$query = "SHOW TABLES";
	$rows = $db->get_results($query, ARRAY_N);
	//print_r($rows);
	$has_fileindex = false;
	$has_users = false;
	if($rows != null)
	{	
		foreach($rows as $row)
		{
			//echo $row[0];
			if($row[0] == "fileindex")
			{
				$has_fileindex = true;
			}
			if($row[0] == "users")
			{
				$has_users = true;
			}
		}
	}
	
	return ($has_fileindex && $has_users);
}

/**
 * 将设置保存到 settings.inc.php 中
 * @param $settings settings 数组
 * @param $mode 0, 普通；1, 数据库；2, 用户
 * @return true, 完成；false, 失败
 */
function save_settings(&$settings, $mode)
{
	if(!Utility::allow_to_admin())
		return false;
	
	$old_settings = $settings;
	$ret = false;
	$save_func = 'save_general';
	if($mode == 1)
	{
		$save_func = 'save_db';
	}
	else if($mode == 2)
	{
		$save_func = 'save_usermng';
	}
	
	if(!($ret = $save_func($settings)))
		$settings = $old_settings;
	
	return $ret;
}

function save_general(&$settings)
{
	$settings['root_type'] = post_query("rootType");
	$settings['root_path'] = post_query("rootPath");
	$settings['charset'] = post_query("charset");
	$settings['timezone'] = post_query("timezone");
	$settings['language'] = post_query("language");
	$settings['title_name'] = post_query("titleName");
	$settings['lightbox'] = post_query("lightbox");
	$settings['audioPlayer'] = post_query("audioPlayer");
	if(isset($settings['install']) && $settings['install'])
	{
		$settings['search'] = "0";
		$settings['usermng'] = "0";
	}
	else
	{
		$settings['search'] = post_query("search");
		$settings['usermng'] = post_query("usermng");
	}
	
	if($settings['root_type'] == "" ||
		$settings['root_path'] == "" ||
		$settings['charset'] == "" ||
		$settings['timezone'] == "" ||
		$settings['language'] == "" ||
		$settings['title_name'] == "" ||
		$settings['lightbox'] == "" ||
		$settings['audioPlayer'] == "" ||
		$settings['search'] == "" ||
		$settings['usermng'] == "")
	{
		return false;	
	}
	
	// prepare $settings['root_path']
	if(strpos($settings['root_path'], "\\\\") === false)
	{
		$settings['root_path'] = str_replace("\\", "\\\\", $settings['root_path']);
	}
	
	$plat_root_path = @iconv(get_encoding(), $settings['charset'], $settings['root_path']);
	if($settings['root_type'] == "relative")
	{
		$plat_root_path = get_base_dir() . $plat_root_path;
	}
	if(file_exists($plat_root_path))
	{
		// 指定的路径存在
		//echo 1;
		$file_name = "settings.inc.tpl";
		$settings_tpl = fopen($file_name, "r");
		$settings_str = fread($settings_tpl, filesize($file_name));
		fclose($settings_tpl);
		//print_r( $settings);
		
		if($settings['search'] || $settings['usermng'])
		{
			if(!check_table(Utility::get_ezMysql()))
			{
				$settings['search'] = 0;
				$settings['usermng'] = 0;
			}
		}
		
		$templates = array("&&FILE_POSITION&&", 
							"&&FILES_DIR&&", 
							"&&PLAT_CHARSET&&",
							"&&TIME_ZONE&&",
							"&&LOCALE&&",
							"&&TITLENAME&&",
							"&&LIGHTBOX&&",
							"&&AUDIOPLAYER&&",
							"&&SEARCH&&",
							"&&USERMNG&&");
		$values = array($settings['root_type'],
						$settings['root_path'],
						$settings['charset'],
						$settings['timezone'],
						$settings['language'],
						$settings['title_name'],
						$settings['lightbox'],
						$settings['audioPlayer'],
						$settings['search'],
						$settings['usermng']);
		
		$settings_str = str_replace($templates, $values, $settings_str);
		//echo $settings;
		
		$settings_php = fopen("settings.inc.php", "w");
		fwrite($settings_php, $settings_str); // 写回配置文件
		fclose($settings_php);
		
		if($settings['usermng'] && !USERMNG && !file_exists("usermng.inc.php"))
		{
			// 打开了用户管理
			$file_name = "usermng.inc.tpl";
			$settings_tpl = fopen($file_name, "r");
			$settings_str = fread($settings_tpl, filesize($file_name));
			fclose($settings_tpl);
			//print_r( $settings);
				
			$templates = array("&&ROSE_BROWSER&&", 
								"&&ROSE_MODIFY&&", 
								"&&ROSE_ADMIN&&");
			$values = array(0, 100, 100);
				
			$settings_str = str_replace($templates, $values, $settings_str);
			//echo $settings;
				
			$settings_php = fopen("usermng.inc.php", "w");
			fwrite($settings_php, $settings_str); // 写回配置文件
			fclose($settings_php);
		}
		
		return true;
	}
	return false;
}

function save_usermng(&$settings)
{
	$settings['rose_browser'] = post_query("roseBrowser");
	$settings['rose_modify'] = post_query("roseModify");
	$settings['rose_admin'] = post_query("roseAdmin");
	
	if($settings['rose_browser'] == "" ||
		$settings['rose_modify'] == "" ||
		$settings['rose_admin'] == "")
	{
		return false;	
	}
	
	if($settings['rose_admin'] < User::$ADMIN)
		$settings['rose_admin'] = User::$ADMIN;

	//echo 1;
	$file_name = "usermng.inc.tpl";
	$settings_tpl = fopen($file_name, "r");
	$settings_str = fread($settings_tpl, filesize($file_name));
	fclose($settings_tpl);
	//print_r( $settings);
		
	$templates = array("&&ROSE_BROWSER&&", 
						"&&ROSE_MODIFY&&", 
						"&&ROSE_ADMIN&&");
	$values = array($settings['rose_browser'],
					$settings['rose_modify'],
					$settings['rose_admin']);
		
	$settings_str = str_replace($templates, $values, $settings_str);
	//echo $settings;
		
	$settings_php = fopen("usermng.inc.php", "w");
	fwrite($settings_php, $settings_str); // 写回配置文件
	fclose($settings_php);
	
	return true;
}

function save_db(&$settings)
{
	$settings['db_user'] = post_query("dbUser");
	$settings['db_pswd'] = post_query("dbPswd");
	$settings['db_name'] = post_query("dbName");
	$settings['db_host'] = post_query("dbHost");
	$settings['root_password'] = post_query("rootPassword");
	
	if($settings['db_user'] == "" ||
		$settings['db_pswd'] == "" ||
		$settings['db_name'] == "" ||
		$settings['db_host'] == "" ||
		$settings['root_password'] == "")
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
		$db->query($query);
		
		$query = "CREATE TABLE IF NOT EXISTS `users` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `username` varchar(128) NOT NULL,
			  `password` varchar(32) NOT NULL,
			  `permission` smallint(5) unsigned NOT NULL,
			  PRIMARY KEY  (`id`),
			  KEY `username` (`username`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		$db->query($query);
		
		if(!check_table($db))
		{
			return false; // 数据库表添加失败
		}
		
		$query = "INSERT INTO `users` SET username='root', password='".md5($settings['root_password'])."', permission=100";
		$db->query($query);
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

?>