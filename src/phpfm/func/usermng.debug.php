<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/usermng.class.php";
require_once "../inc/utility.class.php";

@session_start();

$user_manager = Utility::get_usermng(false);
if ($user_manager != null) {
    $user_manager->debug();
} else {
    echo "NULL";
}

?>