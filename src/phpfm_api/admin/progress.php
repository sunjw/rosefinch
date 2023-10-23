<?php

require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../clazz/utility.class.php';

/**
 * Save settings into settings.inc.php.
 * @param array $settings settings
 * @param number $mode 0, general；2, user
 * @param array $new_settings if null, read from POST, or read from this
 * @return bool
 */
function save_settings(&$settings, $mode, $new_settings) {
    if (!Utility::allow_to_admin()) {
        return false;
    }

    $old_settings = $settings;
    $ret = false;
    $save_func = 'save_general';
    if ($mode == 2) {
        $save_func = 'save_usermng';
    }

    if (!($ret = $save_func($settings, $new_settings))) {
        $settings = $old_settings;
    }

    return $ret;
}

function save_general(&$settings, $new_settings) {
    if ($new_settings == null) {
        $settings['root_type'] = post_query('rootType');
        $settings['root_path'] = post_query('rootPath');
        $settings['charset'] = post_query('charset');
        $settings['language'] = post_query('language');
        $settings['title_name'] = post_query('titleName');
        $settings['usermng'] = post_query('usermng');
    } else {
        $settings['root_type'] = $new_settings['root_type'];
        $settings['root_path'] = $new_settings['root_path'];
        $settings['charset'] = $new_settings['charset'];
        $settings['language'] = $new_settings['language'];
        $settings['title_name'] = $new_settings['title_name'];
        $settings['usermng'] = $new_settings['usermng'];
    }

    if (isset($settings['install']) && $settings['install']) {
        $settings['usermng'] = '0';
    }

    if (empty($settings['root_type']) ||
        empty($settings['root_path']) ||
        empty($settings['charset']) ||
        empty($settings['language']) ||
        empty($settings['title_name']) ||
        empty($settings['usermng'])) {
        return false;
    }

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
            '&&USERMNG&&'
        );
        $values = array(
            $settings['root_type'],
            $settings['root_path'],
            $settings['charset'],
            $settings['language'],
            $settings['title_name'],
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

        if ($settings['usermng'] && !USERMNG && !file_exists('usermng.inc.php')) {
            // enabled usermng
            $file_name = dirname(__FILE__) . '/usermng.inc.tpl';
            $settings_tpl = fopen($file_name, 'r');
            $settings_str = fread($settings_tpl, filesize($file_name));
            fclose($settings_tpl);
            //print_r( $settings);

            $templates = array(
                '&&ROSE_VIEW&&',
                '&&ROSE_MODIFY&&',
                '&&ROSE_ADMIN&&'
            );
            $values = array(0, 100, 100);

            $settings_str = str_replace($templates, $values, $settings_str);
            //echo $settings;

            $settings_php_filename = dirname(__FILE__) . '/usermng.inc.php';
            $settings_php = fopen($settings_php_filename, 'w');
            fwrite($settings_php, $settings_str); // write back
            fclose($settings_php);
            // force refresh opcode
            opcache_invalidate_safe($settings_php_filename, true);
        }

        return true;
    }
    return false;
}

function save_usermng(&$settings, $new_settings) {
    $settings['rose_view'] = post_query('roseView');
    $settings['rose_modify'] = post_query('roseModify');
    $settings['rose_admin'] = post_query('roseAdmin');

    if ($settings['rose_view'] == '' ||
        $settings['rose_modify'] == '' ||
        $settings['rose_admin'] == '') {
        return false;
    }

    if ($settings['rose_admin'] < User::$ADMIN) {
        $settings['rose_admin'] = User::$ADMIN;
    }

    //echo 1;
    $file_name = 'usermng.inc.tpl';
    $settings_tpl = fopen($file_name, 'r');
    $settings_str = fread($settings_tpl, filesize($file_name));
    fclose($settings_tpl);
    //print_r( $settings);

    $templates = array(
        '&&ROSE_VIEW&&',
        '&&ROSE_MODIFY&&',
        '&&ROSE_ADMIN&&'
    );
    $values = array(
        $settings['rose_view'],
        $settings['rose_modify'],
        $settings['rose_admin']
    );

    $settings_str = str_replace($templates, $values, $settings_str);
    //echo $settings;

    $settings_php = fopen('usermng.inc.php', 'w');
    fwrite($settings_php, $settings_str); // write back
    fclose($settings_php);

    return true;
}

?>