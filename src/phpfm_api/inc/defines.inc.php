<?php

require_once 'common.inc.php';

define('VERSION', '3.2411.0');

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
if (file_exists($settings)) {
    require_once $settings;
}

function is_installed() {
    return defined('FILES_DIR');
}

/**
 * @return bool has set su mode
 */
function has_su_mode() {
    return (SU_PASSWORD != '');
}

/**
 * Get some public config value.
 * @return array some key-value
 */
function get_public_config() {
    $installed = false;
    $title_name = '';
    $has_su_mode = false;

    if (is_installed()) {
        $installed = true;
    }
    if ($installed) {
        $title_name = TITLENAME;
        $has_su_mode = has_su_mode();
    }
    return [
        'installed' => $installed,
        'version' => VERSION,
        'title_name' => $title_name,
        'has_su_mode' => $has_su_mode
    ];
}

?>
