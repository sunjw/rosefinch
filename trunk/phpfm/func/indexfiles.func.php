<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/utility.class.php";
require_once "../inc/search.class.php";

if(SEARCH)
{
	$search = new Search();
	
	//$search->create_index();
	if($search->create_index())
		echo "ok";
	else
		echo "error";
}
else
{
	echo "error";
}

?>