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
	private $current_message;
	
	function __construct()
	{
		$this->clear();
	}
	
	/**
	 * 设置要显示的消息
	 * @param $message 消息
	 */
	public function set_message($message)
	{
		$this->current_message = $message;
	}
	
	/**
	 * 获得消息
	 * @return 消息
	 */
	public function get_message()
	{
		$message = $this->current_message;
		$this->clear();
		return $message;
	}
	
	/**
	 * 是否有新的(未显示)消息
	 * @return true 有，false 没有
	 */
	public function have_new_message()
	{
		if($this->current_message == "")
			return false;
		else
			return true;
	}
	
	/**
	 * 清除消息
	 */
	private function clear()
	{
		$this->current_message = "";
	}
	
	public function debug()
	{
		echo $this->current_message;
	}
}

?>