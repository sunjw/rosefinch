<?php
require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../clazz/history.class.php';
require_once dirname(__FILE__) . '/../clazz/utility.class.php';

@session_start();

$history = Utility::get_history(false);

$action = get_query('action');
$step = get_query('step');

$redirect_url = '../index.php';
$history_item = null;

if ($action == 'b') {
    // back.
    $history_item = $history->back($step);
} else if ($action == 'f') {
    // forward.
    $history_item = $history->forward($step);
}

$item_dir = $history_item->get_dir();
$redirect_url = $redirect_url . '?dir=' . $item_dir . '&h';

redirect($redirect_url);

?>
