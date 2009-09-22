<?php

define("VERSION", "1.2.8");

define("INC_DIR_NAME", "inc");

/*
 * 如果是相对路径，定义 FILE_POSITION 为 relative，
 * FILES_DIR 是 PHP File Manager 根目录下的指定目录；
 * 绝对路径，定义 FILE_POSITION 为 absolute，
 * FILES_DIR 是指定目录的完整路径，最后的 '\\' 或 '/' 不需要；
 */
//define("FILE_POSITION", "relative");
define("FILE_POSITION", "absolute");
define("FILES_DIR", "E:\\temp\\phpfm_文件");
//define("FILES_DIR", "F:\\Sun Junwen Documents\\Programs\\php\\phpdl\\files");

define("PLAT_CHARSET", "GB2312"); // Windows 用的是 GB2312

define("DIR_PARAM", "dir"); // 请求子目录的查询参数
define("SORT_PARAM", "s"); // 排序元素的查询参数
define("ORDER_PARAM", "o"); // 排序方向的查询参数
define("VIEW_PARAM", "view"); // 视图模式的查询参数

date_default_timezone_set("Asia/Shanghai"); // 设置时区

// 定义 gettext 参数
define("DOMAIN", "phpfm");
define("LOCALE", "zh_CN"); // 定义使用语言，如zh_CN, en_US
//define("LOCALE", "en_US");


?>