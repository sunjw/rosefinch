<?php

require_once "defines.inc.php";
require_once "common.inc.php";
require_once "sort.inc.php";
require_once "utility.class.php";

/**
 * Search Class
 * 2010-7-2
 * @author Sun Junwen
 *
 */
class Search
{
	private $db; // 存入数据库的所有数据都是 UTF-8 编码的
	private $files_base_dir; // 文件基路径，已经转换成平台编码，结尾有 '/'
	
	function __construct()
	{
		$this->db = Utility::get_ezMysql();
		//$this->db->debug();
		if(!$this->check_db())
			return;
		
		$this->files_base_dir = Utility::get_file_base_dir();
		
		if(PLAT_CHARSET != "UTF-8")
		{
			$this->files_base_dir = convert_toplat($this->files_base_dir);
		}
		
	}
	
	private function check_db()
	{
		if($this->db == null)
			return false;
		
		$query = "SHOW TABLES";
		$rows = $this->db->get_results($query, ARRAY_N);
		//print_r($rows);
		foreach($rows as $row)
		{
			//echo $row[0];
			if($row[0] == "fileindex")
				return true;
		}
		
		return false;
	}
	
	/**
	 * 判断项目是否应被过滤
	 * @param $item 项目(目录或文件)
	 * @return true 需要过滤，false 无需过滤
	 */
	private function filte($item)
	{
		// 过滤隐藏文件
		if(substr($item['name'], 0, 1) == '.') // linux 风格
			return true;
		
		// 其他需要过滤内容
		
		return false;
	}
	
	/**
	 * 创建索引
	 * 索引的主键是相对于基路径的相对路径的 md5 值
	 * @param $subdir 子文件夹路径，必须是 UTF-8 字符串
	 */
	function create_index($subdir = "")
	{
		if(!$this->check_db())
			return false;
			
		if($subdir != "" && 
			substr($subdir, strlen($subdir) - 1, 1) != "\\" && 
			substr($subdir, strlen($subdir) - 1, 1) != "/")
		{
			$subdir .= "/";
		}
		
		// 应用事务
		$query = "START TRANSACTION";
		$this->db->query($query);
		
		// 预处理状态
		$query = "UPDATE fileindex SET refreshed=1 WHERE `path` NOT LIKE '$subdir%'";
		$this->db->query($query);
		
		// 索引文件
		$this->create_index_r($subdir);
		
		// 删除未更新的索引
		$query = "DELETE FROM fileindex WHERE refreshed=0";
		$this->db->query($query);
		
		// 回复状态
		$query = "UPDATE fileindex SET refreshed=0";
		$this->db->query($query);
		
		$query = "COMMIT";
		$this->db->query($query);
		
		return true;
	}
	
	/**
	 * 递归创建索引
	 * @param $subdir 子文件夹路径，必须是 UTF-8 字符串
	 */
	private function create_index_r($subdir = "")
	{
		if(!$this->check_db())
			return;
			
		if($subdir != "" && 
			substr($subdir, strlen($subdir) - 1, 1) != "\\" && 
			substr($subdir, strlen($subdir) - 1, 1) != "/")
		{
			$subdir .= "/";
		}
		
		$subdir = convert_toplat($subdir);
		
		$path = $this->files_base_dir . $subdir;
		//echo $path;
		if(!file_exists($path))
		{
			return;
		}
		//echo 1;
		
		// 处理目录
		$dirs = array();
		if ($handle = @opendir($path)) 
		{
			//echo "List of dirs:<br />";
		    while (false !== ($dir_name = @readdir($handle))) 
		    {
		        //echo convert_toutf8($file) . "<br />";
		        if($dir_name != "." && $dir_name != "..") // 过滤掉 . 和 ..
		        {
			        $full_dir_path = $path . $dir_name;
			        if(is_dir($full_dir_path))
			        {
			        	//echo convert_toutf8($full_dir_path) . "<br />";
			        	$dstat = stat($full_dir_path);
			        	$dir = array();
			        	$dir['name'] = convert_toutf8($dir_name);
			        	$dir['path'] = convert_toutf8($full_dir_path);
			        	$dir['stat'] = $dstat;
			        	$dir['type'] = "dir";
			        	
			        	if($this->filte($dir))
							continue;
									
						$item_path = convert_toutf8($subdir) . $dir['name'];
						$dir['item_path'] = $item_path;
						$dir['hash'] = md5($dir['item_path']);
			        	
						// 信息收集完成
						// 写入数据库
						$query = "INSERT INTO fileindex VALUES ('".$dir['hash']."',".
							"'".$dir['item_path']."',".
							"'".$dir['name']."',".
							"NULL,".
							"'dir',".
							"'".date("Y-n-j H:i:s", $dir['stat']['mtime'])."',".
							"1".
							")";
						//echo $query."<br />";
						//echo date("Y-n-j H:i:s", $dir['stat']['mtime'])."<br />";
						$rows = $this->db->query($query);
						//echo $rows;
						
						if($rows != 1)
						{
							// 条目已经存在或者其他原因，试试 UPDATE
							$query = "UPDATE fileindex SET ".
								"size=NULL,".
								"type='dir',".
								"modified='".date("Y-n-j H:i:s", $dir['stat']['mtime'])."',".
								"refreshed=1 ".
								" WHERE path_hash='".$dir['hash']."'";
							//echo $query."<br />";
							$rows = $this->db->query($query);
							//echo $rows;
						}
						
			        	array_push($dirs, $dir);
			        }
		        }
		    }
		    closedir($handle);
		}
		//print_r($dirs);
		// 处理目录 完成
		
		// 处理文件
		$files = array();
		if ($handle = @opendir($path)) 
		{
			//echo "List of files:<br />";
			
		    while (false !== ($file_name = @readdir($handle))) 
		    {
		        //echo convert_toutf8($file) . "<br />";
		        
		        $full_file_path = $path . $file_name;
		        if(!is_dir($full_file_path))
		        {
		        	//echo convert_toutf8($full_file_path) . "<br />";
		        	$fstat = stat($full_file_path);
					$type = Utility::get_file_ext($file_name);
					
		        	$file = array();
		        	$file['name'] = convert_toutf8($file_name);
		        	$file['path'] = convert_toutf8($full_file_path);
		        	$file['type'] = convert_toutf8($type);
		        	$file['stat'] = $fstat;
		        	
		        	if($this->filte($file))
						continue;
					
					if($file['type'] == "")
						$file['type'] = "file";
						
					$item_path =  convert_toutf8($subdir).$file['name'];
					$file['item_path'] = $item_path;
					
					$file['hash'] = md5($file['item_path']);
					
		        	// 信息收集完成
					// 写入数据库
					$query = "INSERT INTO fileindex VALUES ('".$file['hash']."',".
						"'".$file['item_path']."',".
						"'".$file['name']."',".
						$file['stat']['size'].",".
						"'".$file['type']."',".
						"'".date("Y-n-j H:i:s", $file['stat']['mtime'])."',".
						"1".
						")";
					//echo $query."<br />";
					//echo date("Y-n-j H:i:s", $file['stat']['mtime'])."<br />";
					$rows = $this->db->query($query);
					//echo $rows;
						
					if($rows != 1)
					{
						// 条目已经存在或者其他原因，试试 UPDATE
						$query = "UPDATE fileindex SET ".
							"size=".$file['stat']['size'].",".
							"type='".$file['type']."',".
							"modified='".date("Y-n-j H:i:s", $file['stat']['mtime'])."',".
							"refreshed=1 ".
							" WHERE path_hash='".$file['hash']."'";
						//echo $query."<br />";
						$rows = $this->db->query($query);
						//echo $rows;
					}
		        	
		        	array_push($files, $file);
		        }
		    }
		
		    closedir($handle);
		}
		//print_r($files);
		// 处理文件 完成
		
		// 递归处理文件夹
		foreach($dirs as $dir)
		{
			$this->create_index_r($dir['item_path']);
		} 

	}
	
	function query($query_str, $subdir, $sort = 1)
	{
		if(!$this->check_db() || $query_str == "")
		{
			return null;
		}
		
		if($subdir != "" && 
			substr($subdir, strlen($subdir) - 1, 1) != "/")
		{
			$subdir .= "/";
		}
		if(substr($subdir, strlen($subdir) - 1, 1) == "\\")
		{
			$subdir = substr($subdir, 0, strlen($subdir) - 1);
			$subdir .= "/";
		}
		
		$subdir .= "%";
		
		$query = "SELECT * FROM `fileindex` WHERE name LIKE \"%$query_str%\"";
		if($subdir != "%")
		{
			$query .= " AND path LIKE \"$subdir\"";
		}
		
		//echo $query;
		$rows = $this->db->get_results($query);
		
		return $rows;
	}
	
}

?>