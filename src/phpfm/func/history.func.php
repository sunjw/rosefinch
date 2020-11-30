<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/history.class.php";
require_once "../inc/utility.class.php";

@session_start();

$history = Utility::get_history(false);

$action = get_query("action");
$step = get_query("step");

$redirect_url = "../index.php";
$history_item = null;

if ($action == "b") {
    // back.
    $history_item = $history->back($step);
} else if ($action == "f") {
    // forward.
    $history_item = $history->forward($step);
}

$item_dir = $history_item->get_dir();
$redirect_url = $redirect_url . "?dir=" . $item_dir . "&h";

redirect($redirect_url);

?>