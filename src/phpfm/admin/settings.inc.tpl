<?php

/*
 * 如果是相对路径，定义 FILE_POSITION 为 relative，
 * FILES_DIR 是 PHP File Manager 根目录下的指定目录；
 * 绝对路径，定义 FILE_POSITION 为 absolute，
 * FILES_DIR 是指定目录的完整路径，最后的 '\\' 或 '/' 不需要；
 */
define("FILE_POSITION", "&&FILE_POSITION&&");
define("FILES_DIR", "&&FILES_DIR&&");

/* 
 * PLAT_CHARSET 平台文件系统的编码
 * Windows 用的是 GB2312
 * *nix 大多为 UTF-8
 */
define("PLAT_CHARSET", "&&PLAT_CHARSET&&");

date_default_timezone_set("&&TIME_ZONE&&"); // 设置时区

define("LOCALE", "&&LOCALE&&"); // 定义使用语言，如zh_CN, en_US

// 搜索
define("SEARCH", &&SEARCH&&);
// 用户管理
define("USERMNG", &&USERMNG&&);

// 名称
define("TITLENAME", "&&TITLENAME&&");

// 控制 lightbox
define("LIGHTBOX", &&LIGHTBOX&&);
// 控制 AudioPlayer
define("AUDIOPLAYER", &&AUDIOPLAYER&&);

?>