<?php
$begin_time = microtime(true);

require_once "inc/defines.inc.php";
require_once "inc/common.inc.php";
require_once "inc/gettext.inc.php";
require_once "inc/utility.class.php";

@session_start();

set_response_utf8();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php printf("%s - %s", _("Rosefinch - PHP File Manager"), _("Help")); ?></title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link href="css/com.css" rel="stylesheet" type="text/css" />
	<link href="css/document.css" rel="stylesheet" type="text/css" />
	<link href="css/jquery.lightbox-0.5.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" language="javascript" src="js/jquery-1.8.1.min.js"></script>
	<script type="text/javascript" language="javascript" src="js/jquery.tabs.min.js"></script>
	<script type="text/javascript" language="javascript" src="js/jquery.lightbox-0.5.plus.pack.js"></script>
	<script type="text/javascript" >
		//<![CDATA[
		// Initial LightBox
		$(function (){
			$('a.lightboxImg').lightBox( {
				overlayOpacity :0.5,
				autoAdapt :true
			});

			jqTabs.setup({
					prefix : "#phpfmDoc",
					splitor : "&nbsp;|&nbsp;"
				});
			jqTabs.init();
		});
		//]]>
	</script>
</head>
<body>
	<div id="header">
	    <div id="nav">
	        <?php Utility::html_navigation("help"); ?>
	        <div class="clear"></div>
	    </div>
	    <div id="loginStatus">
	    <?php 
	    echo Utility::display_user();
	    ?>
	    </div>
    </div>
    <div id="content">
        <div id="phpfmDocNav">
        	<a class="selected" title="MainUI" href="#MainUI"><?php echo _("Main UI"); ?></a>&nbsp;|&nbsp;<a class="" title="Setting" href="#Setting"><?php echo _("Setting"); ?></a>&nbsp;|&nbsp;<a class="" title="Issue" href="#Issue"><?php echo _("Knowing Issue"); ?></a>
      	</div>
        <?php 
        	if(file_exists("help/help." . LOCALE . ".php"))
        		require "help/help." . LOCALE . ".php";
        ?>
    </div>
    <div id="footer">
        <?php Utility::html_copyright_info($begin_time); ?>
    </div>
</body>
</html>
