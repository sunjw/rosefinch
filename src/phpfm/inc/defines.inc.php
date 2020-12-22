<?php

require_once "common.inc.php";

// DO NOT EDIT

define("VERSION", "3.2012.0");

define("INC_DIR_NAME", "inc"); // include 文件夹
define("DEBUG", true); // Debug 模式

define("DIR_PARAM", "dir"); // 请求子目录的查询参数
define("SORT_PARAM", "s"); // 排序元素的查询参数
define("ORDER_PARAM", "o"); // 排序方向的查询参数
define("VIEW_PARAM", "view"); // 视图模式的查询参数
define("TOOLBAR_PARAM", "toolbar"); // 视图模式的查询参数

// 定义 gettext 参数
define("DOMAIN", "phpfm"); // 不要修改

/**
 * 将指定字符串从<strong>定义的平台字符串</strong>转换成 UTF-8
 * @param $str 目标字符串
 * @return 转换后的 UTF-8 字符串
 */
function convert_toutf8($str)
{
    return @iconv(PLAT_CHARSET, "UTF-8", $str);
}

/**
 * 将指定字符串从  UTF-8 转换成<strong>定义的平台字符串</strong>
 * @param $str 目标  UTF-8 字符串
 * @return 转换后的本地编码字符串
 */
function convert_toplat($str)
{
    return @iconv("UTF-8", PLAT_CHARSET, $str);
}

$settings = dirname(__FILE__) . "/../admin/settings.inc.php";
$database_inc = dirname(__FILE__) . "/../admin/database.inc.php";
$user_managment = dirname(__FILE__) . "/../admin/usermng.inc.php";
if (file_exists($settings)) {
    require_once $settings;
    if (file_exists($database_inc)) {
        require_once $database_inc;
    }
    if (file_exists($user_managment)) {
        require_once $user_managment;
    }
} else if (!defined('INSTALL')) {
    // 没有配置文件，跳转至安装
    redirect("admin/install.php");
}

// DO NOT EDIT

?>