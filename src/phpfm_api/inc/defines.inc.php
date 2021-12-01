<?php

require_once 'common.inc.php';

// DO NOT EDIT

define('VERSION', '3.2101.10');

define('INC_DIR_NAME', 'inc'); // include directory
define('DEBUG', true); // debug mode

define('DIR_PARAM', 'dir'); // request dir param
define('SORT_PARAM', 's'); // request sort param
define('ORDER_PARAM', 'o'); // request order param

define('DOMAIN', 'phpfm'); // gettext param

/**
 * Convert platform encoding string to UTF-8.
 * @param string $str
 * @return string UTF-8 encoded string
 */
function convert_toutf8($str) {
    return @iconv(PLAT_CHARSET, 'UTF-8', $str);
}

/**
 * Convert UTF-8 encoding string to platform encoding.
 * @param string $str
 * @return string platform encoded string
 */
function convert_toplat($str) {
    return @iconv('UTF-8', PLAT_CHARSET, $str);
}

$settings = dirname(__FILE__) . '/../admin/settings.inc.php';
$database_inc = dirname(__FILE__) . '/../admin/database.inc.php';
$user_managment = dirname(__FILE__) . '/../admin/usermng.inc.php';
if (file_exists($settings)) {
    require_once $settings;
    if (file_exists($database_inc)) {
        require_once $database_inc;
    }
    if (file_exists($user_managment)) {
        require_once $user_managment;
    }
} else if (!defined('INSTALL')) {
    // No setting file, jump to install.
    redirect('admin/install.php');
}

// DO NOT EDIT

?>
