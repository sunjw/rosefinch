<?php

/**
 * History item class
 * 2010-7-21
 * @author Sun Junwen
 *
 */
class HistoryItem
{
	private $dir;
	private $search_key;
	
	public function __construct($dir, $search_key = "")
	{
		$this->dir = $dir;
		$this->search_key = $search_key;
	}
	
	public function get_dir()
	{
		return $this->dir;
	}
	
	public function get_search_key()
	{
		return $this->search_key;
	}
	
	public function equal($dir, $search_key = "")
	{
		return ($this->dir == $dir && $this->search_key == $search_key);
	}
	
	public function is_search()
	{
		return ($this->search_key != "");
	}
	
	public function to_string()
	{
		if($this->search_key != "")
			return $this->search_key;
		else if($this->dir == "")
			return "Root";
		else
			return erase_last_slash($this->dir);
	}
	
}

/**
 * History class
 * 2010-7-21
 * @author Sun Junwen
 *
 */
class History
{
	private $history_array;
	private $length;
	private $current;
	
	public function __construct()
	{
		$this->clear();
	}
	
	/**
	 * 压入历史
	 * @param $dir 访问的目录
	 * @param $search_key 搜索关键字
	 */
	public function push($dir, $search_key = "")
	{
		if($this->current == 0)
		{
			$this->history_array[++$this->current] = new HistoryItem($dir, $search_key);
			$this->length = $this->current;
			return;
		}
		
		$current_item = $this->history_array[$this->current];
		if(!$current_item->equal($dir, $search_key))
		{
			$this->history_array[++$this->current] = new HistoryItem($dir, $search_key);
			$this->length = $this->current;
		}
	}
	
	/**
	 * 判断是否可以后退
	 * @return bool
	 */
	public function able_to_back()
	{
		return ($this->current > 1);
	}
	
	/**
	 * 后退
	 * 获得后退去的 History item
	 * @return HistoryItem
	 */
	public function back($step = 1)
	{
		$step = $step==null ? 1 : $step;
		if($this->current < $step)
			return null;
		
		if($this->current > $step)
			$this->current -= $step;
		
		return $this->history_array[$this->current];
	}
	
	/**
	 * 判断是否可以前进
	 * @return bool
	 */
	public function able_to_forward()
	{
		return ($this->current < $this->length);
	}
	
	/**
	 * 前进
	 * 获得前进的 History item
	 * @return HistoryItem
	 */
	public function forward($step = 1)
	{
		$step = $step==null ? 1 : $step;
		if($this->current < 1)
			return null;
			
		if($this->current <= $this->length - $step)
			$this->current += $step;
		
		return $this->history_array[$this->current];
	}
	
	/**
	 * 获得当前位置
	 * @return current
	 */
	public function get_current()
	{
		return $this->current;
	}
	
	/**
	 * 获得历史长度，不是历史数组的长度
	 * @return length
	 */
	public function get_length()
	{
		return $this->length;
	}
	
	/**
	 * 获得历史数组，注意它的长度并非历史的长度
	 * @return Array 历史数组
	 */
	public function get_history()
	{
		return $this->history_array;
	}
	
	/**
	 * 测试用
	 */
	public function debug()
	{
		echo "Current: ".$this->current."<br />";
		echo "Length: ".$this->length."<br />";
		echo "History:<br />";
		print_r($this->history_array);
	}
	
	/**
	 * 测试用
	 */
	public function clear()
	{
		$this->history_array = Array();
		$this->length = 0;
		$this->current = 0;
	}
	
}

?>