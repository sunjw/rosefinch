<?php

require_once "defines.inc.php";
require_once "common.inc.php";

/*
 * 初始化 gettext
 */
putenv("LANG=" . LOCALE);
setlocale(LC_ALL, LOCALE);

$lang_dir = "locales";

$directory = get_base_dir() . $lang_dir;

bindtextdomain(DOMAIN, $directory);
bind_textdomain_codeset(DOMAIN, get_encoding()); // 让 gettext 以 utf-8 读取 mo
textdomain(DOMAIN);

//echo _("Hello World!");

?>