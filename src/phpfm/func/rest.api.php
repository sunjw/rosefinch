<?php
require_once dirname(__FILE__) . "/../inc/defines.inc.php";
require_once dirname(__FILE__) . "/../inc/common.inc.php";
require_once dirname(__FILE__) . "/../clazz/rest.class.php";

// For HTML5 upload.
if (isset($_REQUEST['session']) && $_REQUEST['session']) {
    @session_id($_REQUEST['session']);
}

@session_start();

$post = new Rest();

$post->handle_request();

?>
