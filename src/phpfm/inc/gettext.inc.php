<?php

require_once "defines.inc.php";
require_once "common.inc.php";

/*
 * 初始化 gettext
 */
if (!defined("LOCALE"))
    define("LOCALE", "en-us");
putenv("LANG=" . LOCALE);
setlocale(LC_ALL, LOCALE);

$lang_dir = "locales";

$directory = get_base_dir() . $lang_dir;

if (function_exists("bindtextdomain")) {
    bindtextdomain(DOMAIN, $directory);
    bind_textdomain_codeset(DOMAIN, get_encoding()); // 让 gettext 以 utf-8 读取 mo
    textdomain(DOMAIN);
} else {
    function bindtextdomain($arg1, $arg2) {}

    function bind_textdomain_codeset($arg1, $arg2) {}

    function textdomain($arg1) {}

    function _($str)
    {
        return $str;
    }
}

//echo _("Hello World!");

?>