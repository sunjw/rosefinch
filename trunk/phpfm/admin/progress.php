<?php

require_once "../inc/common.inc.php";
require_once "../inc/utility.class.php";

/**
 * 将设置保存到 settings.inc.php 中
 * @param $settings settings 数组
 * @param $usermng 是否是用户管理
 * @return true, 完成；false, 失败
 */
function save_settings(&$settings, $usermng)
{
	if(!Utility::allow_to_admin())
		return false;
	
	$old_settings = $settings;
	$ret = false;
	$save_func = 'save_general';
	if($usermng)
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
	$settings['search'] = post_query("search");
	$settings['usermng'] = post_query("usermng");
	
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

?>