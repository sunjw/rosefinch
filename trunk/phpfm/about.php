<?php

require_once "inc/defines.inc.php";
require_once "inc/common.inc.php";
require_once "inc/gettext.inc.php";
require_once "inc/utility.class.php";

@session_start();

$begin_time = microtime(true);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php printf("%s - %s", _("Rosefinch - PHP File Manager"), _("About")); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link href="css/com.css" rel="stylesheet" type="text/css" />
	<link href="css/about.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" language="javascript" src="js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" language="javascript" src="js/about.js"></script>
</head>
<body>
	<div id="header">
        
    </div>
    <div id="nav">
        <?php Utility::html_navigation("about"); ?>
        <div class="clear"></div>
    </div>
    <div id="content">
        <div id="phpfmHelpNav">
			<a href="#Readme"><?php echo _("Readme"); ?></a>&nbsp;|&nbsp;<a href="#Install"><?php echo _("Install"); ?></a>&nbsp;|&nbsp;<a href="#About"><?php echo _("About"); ?></a>
        </div>
        <?php include "inc/" . _("about.lang.php"); ?>
    </div>
    <div id="footer">
        <?php Utility::html_copyright_info($begin_time); ?>
    </div>
</body>
</html>
