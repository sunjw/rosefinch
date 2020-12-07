<?php

/**
 * Message Board Class
 *
 * 2009-9-16
 *
 * @author Sun Junwen
 *
 */
class MessageBoard
{
    private $cur_msg;
    private $cur_msg_stat; // 0: 一般, 1: 正确, 2: 错误

    function __construct()
    {
        $this->clear();
    }

    /**
     * 设置要显示的消息
     * @param $message 消息
     * @param $stat 消息状态，0: 一般, 1: 正确, 2: 错误
     */
    public function set_message($message, $stat = 0)
    {
        $this->cur_msg = $message;
        $this->cur_msg_stat = $stat;
    }

    /**
     * 获得消息
     * @param $message 消息
     * @param $stat 消息状态
     */
    public function get_message(&$message, &$stat)
    {
        $message = $this->cur_msg;
        $stat = $this->cur_msg_stat;
        $this->clear();
    }

    /**
     * 是否有新的(未显示)消息
     * @return true 有，false 没有
     */
    public function have_new_message()
    {
        if ($this->cur_msg == "")
            return false;
        else
            return true;
    }

    /**
     * 清除消息
     */
    private function clear()
    {
        $this->cur_msg = "";
        $this->cur_msg_stat = 0;
    }

    public function debug()
    {
        echo("Msg: " . $this->cur_msg . ", Stat: " . $this->cur_msg_stat);
    }
}

?>