<?php

/*
 * If using relative path, define 'FILE_POSITION' as 'relative',
 * then 'FILES_DIR' is a directory under PHP File Manager root.
 * If using absolute path, define 'FILE_POSITION' as 'absolute',
 * then 'FILES_DIR' is a full path to a directory, NOT ending with '\\' or '/'.
 */
define('FILE_POSITION', '&&FILE_POSITION&&');
define('FILES_DIR', '&&FILES_DIR&&');

/* 
 * PLAT_CHARSET is file system encoding.
 * Windows: GB2312
 * *nix: UTF-8
 */
define('PLAT_CHARSET', '&&PLAT_CHARSET&&');

/**
 * Locale
 */
define('LOCALE', '&&LOCALE&&'); // language, like 'zh_CN', 'en_US'

/**
 * Title
 */
define('TITLENAME', '&&TITLENAME&&');

/**
 * JWT key
 */
define('JWT_KEY', '&&JWT_KEY&&');

/**
 * SU mode password.
 */
define('SU_PASSWORD', '&&SU_PASSWORD&&');

?>
