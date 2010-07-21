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
	public function back()
	{
		if($this->current < 1)
			return null;
		
		if($this->current > 1)
			--$this->current;
		
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
	public function forward()
	{
		if($this->current < 1)
			return null;
			
		if($this->current < $this->length)
			++$this->current;
		
		return $this->history_array[$this->current];
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