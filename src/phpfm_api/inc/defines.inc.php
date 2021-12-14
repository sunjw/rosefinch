<?php

require_once 'common.inc.php';

define('VERSION', '3.2112.5');

define('DEBUG', true); // debug mode

define('DIR_PARAM', 'dir'); // request dir param
define('SORT_PARAM', 's'); // request sort param
define('ORDER_PARAM', 'o'); // request order param

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
}

/**
 * Get some public config value.
 * @return array some key-value
 */
function get_public_config() {
    $installed = false;
    $title_name = '';

    if (defined('FILES_DIR')) {
        $installed = true;
    }
    if ($installed) {
        $title_name = TITLENAME;
    }
    return [
        'installed' => $installed,
        'version' => VERSION,
        'title_name' => $title_name
    ];
}

?>
