<?php

require_once "../inc/common.inc.php";

function save_settings(&$settings)
{
	$settings['root_type'] = post_query("rootType");
	$settings['root_path'] = post_query("rootPath");
	$settings['charset'] = post_query("charset");
	$settings['language'] = post_query("language");
	$settings['title_name'] = post_query("titleName");
	$settings['lightbox'] = post_query("lightbox");
	
	$plat_root_path = @iconv(get_encoding(), $settings['charset'], $settings['root_path']);
	if($settings['root_type'] == "relative")
	{
		$plat_root_path = get_base_dir() . $plat_root_path;
	}
	if(file_exists($plat_root_path))
	{
		// 指定的路径存在
		//echo 1;
		$file_name = "settings.tpl";
		$settings_tpl = fopen($file_name, "r");
		$settings_str = fread($settings_tpl, filesize($file_name));
		fclose($settings_tpl);
		//echo $settings;
		
		$templates = array("&&FILE_POSITION&&", 
							"&&FILES_DIR&&", 
							"&&PLAT_CHARSET&&",
							"&&TIME_ZONE&&",
							"&&LOCALE&&",
							"&&TITLENAME&&",
							"&&LIGHTBOX&&");
		$values = array($settings['root_type'],
						$settings['root_path'],
						$settings['charset'],
						$settings['timezone'],
						$settings['language'],
						$settings['title_name'],
						$settings['lightbox']);
		
		$settings_str = str_replace($templates, $values, $settings_str);
		//echo $settings;
		
		$settings_php = fopen("settings.php", "w");
		fwrite($settings_php, $settings_str); // 写回配置文件
		fclose($settings_php);
		
		return true;
	}
	return false;
}

?>