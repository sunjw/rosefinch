<?php

/*
 * 如果是相对路径，定义 FILE_POSITION 为 relative，
 * FILES_DIR 是 PHP File Manager 根目录下的指定目录；
 * 绝对路径，定义 FILE_POSITION 为 absolute，
 * FILES_DIR 是指定目录的完整路径，最后的 '\\' 或 '/' 不需要；
 */
define("FILE_POSITION", "absolute");
define("FILES_DIR", "E:\\temp\\phpfm_文件");

/* 
 * PLAT_CHARSET 平台文件系统的编码
 * Windows 用的是 GB2312
 * *nix 大多为 UTF-8
 */
define("PLAT_CHARSET", "GB2312");

date_default_timezone_set("Asia/Shanghai"); // 设置时区

define("LOCALE", "zh_CN"); // 定义使用语言，如zh_CN, en_US

// 搜索
define("SEARCH", 1);

// 名称
define("TITLENAME", "Rosefinch");

// 控制 lightbox
define("LIGHTBOX", 1);
// 控制 AudioPlayer
define("AUDIOPLAYER", 1);

?>