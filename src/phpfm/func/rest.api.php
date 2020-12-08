<?php
require_once dirname(__FILE__) . '/../clazz/rest.class.php';

$REQUEST_SESSION_NAME = 'session';

// For HTML5 upload.
if (isset($_REQUEST[$REQUEST_SESSION_NAME]) && $_REQUEST[$REQUEST_SESSION_NAME]) {
    @session_id($_REQUEST[$REQUEST_SESSION_NAME]);
}

@session_start();

$post = new Rest();

$post->handle_request();

?>
