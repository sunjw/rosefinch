<?php

define('INSTALL', 1);

require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../inc/gettext.inc.php';
require_once 'progress.php';
require_once dirname(__FILE__) . '/../log/log.func.php';

@session_start();

set_response_utf8();

get_logger()->info($_SERVER['REMOTE_ADDR'] . ' installing...');

//print_r($_GET);
//print_r($_POST);

$settings = array('root_type' => 'absolute',
    'root_path' => '',
    'charset' => 'UTF-8',
    'timezone' => '',
    'language' => 'en_US',
    'title_name' => 'Rosefinch',
    'usermng' => 0,
    'install' => 1);

$wrong = false;

if (isset($_POST['settingsForm'])) {
    if (save_settings($settings, 0)) {
        redirect('../');
    } else {
        $wrong = true;
    }
}

$settings['root_path'] = str_replace('\\\\', '\\', $settings['root_path']); // fix path display

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo _('Install'); ?></title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
    <link href="../css/com.css" rel="stylesheet" type="text/css"/>
    <link href="../css/message.css" rel="stylesheet" type="text/css"/>
    <link href="../css/setting.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" language="javascript" src="../js/jquery-1.8.1.min.js"></script>
</head>
<body class="install">
<div id="nav">

</div>
<div id="header">
    <div id="mainTitle">
        <?php echo _('Install'); ?>
    </div>
    <div id="subTitle">
        <?php echo _('Prepare Rosefinch for first time using.'); ?>
    </div>
</div>
<div id="content">
    <div id="phpfmMessage" <?php if ($wrong) print('style="display:block" class="wrong"'); ?>>
        <?php
        echo _('There is something wrong in your settings.');
        ?>
    </div>
    <div class="clear"></div>
    <?php include 'settings.form.php'; ?>
</div>
<div id="footer">

</div>
</body>
</html>
