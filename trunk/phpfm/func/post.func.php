<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/post.class.php";

if($_REQUEST['session']) 
	session_id($_REQUEST['session']);


@session_start();

$post = new Post(post_query("oper"));

$post->do_oper();

?>