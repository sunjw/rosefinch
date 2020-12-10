<?php
require_once dirname(__FILE__) . "/../clazz/messageboard.class.php";
require_once dirname(__FILE__) . "/../clazz/utility.class.php";

@session_start();

$messageboard = Utility::get_messageboard(false);
if ($messageboard != null) {
    $message = "";
    $stat = 0;
    if ($messageboard->has_message()) {
        $messageboard->get_message($message, $stat);
        echo $message . "|PHPFM|" . $stat;
    }
}

?>
