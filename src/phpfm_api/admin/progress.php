<?php

require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../clazz/utility.class.php';

/**
 * Save settings into settings.inc.php.
 * @param array $settings settings
 * @param array $new_settings new settings
 * @return bool
 */
function save_settings(&$settings, $new_settings) {
    $old_settings = $settings;
    $ret = false;

    if (!($ret = save_general($settings, $new_settings))) {
        $settings = $old_settings;
    }

    return $ret;
}

function save_general(&$settings, $new_settings) {
    $settings['root_type'] = $new_settings['root_type'];
    $settings['root_path'] = $new_settings['root_path'];
    $settings['charset'] = $new_settings['charset'];
    $settings['language'] = $new_settings['language'];
    $settings['title_name'] = $new_settings['title_name'];
    $settings['su_password'] = $new_settings['su_password'];
    $settings['usermng'] = $new_settings['usermng'];

    if (isset($settings['install']) && $settings['install']) {
        $settings['usermng'] = 0;
    }
    if ($settings['su_password'] === 0) {
        $settings['su_password'] = SU_PASSWORD;
    }

    if (empty($settings['root_type']) || empty($settings['root_path']) ||
        empty($settings['charset']) || empty($settings['language']) ||
        empty($settings['title_name']) || $settings['usermng'] == '') {
        return false;
    }

    // Update JWT_KEY.
    $settings['jwt_key'] = generate_random_string(16, true);

    // prepare $settings['root_path']
    $settings['root_path'] = trim_last_slash($settings['root_path']);
    if (strpos($settings['root_path'], '\\\\') === false) {
        $settings['root_path'] = str_replace('\\', '\\\\', $settings['root_path']);
    }

    $plat_root_path = @iconv(get_encoding(), $settings['charset'], $settings['root_path']);
    if ($settings['root_type'] == 'relative') {
        $plat_root_path = get_base_dir() . $plat_root_path;
    }
    if (file_exists($plat_root_path)) {
        // path exists.
        //echo 1;
        $file_name = dirname(__FILE__) . '/settings.inc.tpl';
        $settings_tpl = fopen($file_name, 'r');
        $settings_str = fread($settings_tpl, filesize($file_name));
        fclose($settings_tpl);
        //print_r( $settings);

        if ($settings['usermng']) {
            $settings['usermng'] = 0;
        }

        $templates = array(
            '&&FILE_POSITION&&',
            '&&FILES_DIR&&',
            '&&PLAT_CHARSET&&',
            '&&LOCALE&&',
            '&&TITLENAME&&',
            '&&JWT_KEY&&',
            '&&SU_PASSWORD&&',
            '&&USERMNG&&'
        );
        $values = array(
            $settings['root_type'],
            $settings['root_path'],
            $settings['charset'],
            $settings['language'],
            $settings['title_name'],
            $settings['jwt_key'],
            $settings['su_password'],
            $settings['usermng']
        );

        $settings_str = str_replace($templates, $values, $settings_str);
        //echo $settings;

        $settings_php_filename = dirname(__FILE__) . '/settings.inc.php';
        $settings_php = fopen($settings_php_filename, 'w');
        fwrite($settings_php, $settings_str); // write back
        fclose($settings_php);
        // force refresh opcode
        opcache_invalidate_safe($settings_php_filename, true);

        return true;
    }
    return false;
}

?>