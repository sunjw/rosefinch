<?php

/**
 * Message Board Class
 *
 * 2009-9-16
 *
 * @author Sun Junwen
 *
 */
class MessageBoard {
    private $cur_msg;
    private $cur_msg_stat; // 0: normal, other: some wrong.

    function __construct() {
        $this->clear();
    }

    /**
     * Set message.
     * @param string $message message
     * @param number $stat 0: normal, other: some wrong
     */
    public function set_message($message, $stat = 0) {
        $this->cur_msg = $message;
        $this->cur_msg_stat = $stat;
    }

    /**
     * Get current message.
     * @param string $message message
     * @param number $stat 0: normal, other: some wrong
     */
    public function get_message(&$message, &$stat) {
        $message = $this->cur_msg;
        $stat = $this->cur_msg_stat;
        $this->clear();
    }

    /**
     * Has message.
     * @return bool
     */
    public function has_message() {
        if (empty($this->cur_msg)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Clear message.
     */
    private function clear() {
        $this->cur_msg = '';
        $this->cur_msg_stat = 0;
    }

    public function debug() {
        echo('Msg: ' . $this->cur_msg . ', Stat: ' . $this->cur_msg_stat);
    }
}

?>