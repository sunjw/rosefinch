<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/history.class.php";
require_once "../inc/utility.class.php";

@session_start();

$history = Utility::get_history(false);

$action = get_query("action");
$step = get_query("step");
$search_page = get_query("sp"); // search page name

$redirect_url = "../index.php";
$history_item = null;

if ($action == "b") {
    // 后退
    $history_item = $history->back($step);
} else if ($action == "f") {
    // 前进
    $history_item = $history->forward($step);
}

$item_dir = $history_item->get_dir();
$item_search_key = $history_item->get_search_key();
if ($item_search_key == "") {
    $redirect_url = $redirect_url . "?dir=" . $item_dir . "&h";
} else {
    $redirect_url = "../" . $search_page . "?q=" . $item_search_key . "&dir=" . $item_dir . "&h";
}

redirect($redirect_url);

?>