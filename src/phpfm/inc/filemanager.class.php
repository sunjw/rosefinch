<?php

require_once "defines.inc.php";
require_once "common.inc.php";
require_once "gettext.inc.php";
require_once "sort.inc.php";
require_once "clipboard.class.php";
require_once "messageboard.class.php";
require_once "history.class.php";
require_once "search.class.php";
require_once "usermng.class.php";
require_once "utility.class.php";

@session_start();

/**
 * File Manager Class
 * 2009-8-4
 * @author Sun Junwen
 *
 */
class FileManager
{
	private $request_sub_dir; // 请求的子目录，一直是 UTF-8
	private $request_dir; // 请求目录对应的系统绝对路径
	private $sort_type; // 排序方式
	private $order; // 排序方向
	private $view_type; // 视图模式
	private $toolbar_type;
	
	private $sort; // 文件排序代号
	private $dsort;
	private $query_str;
	
	private $fstats;
	private $dstats;
	
	private $is_search;
	private $browser_page; // 浏览页面名称 默认为 index.php
	private $search_page; // 搜索页面名称 默认为 search.php
	private $search_query;
	
	private $clipboard;
	private $messageboard;
	private $history;
	private $user_manager;
	
	function __construct($is_search = false, $browser_page = "index.php", $search_page = "search.php")
	{
		/*
		 * 内部字符串全部使用 UTF-8 编码
		 */
		set_response_utf8();
		
		$this->clipboard = Utility::get_clipboard();
		$this->messageboard = Utility::get_messageboard();
		$this->history = Utility::get_history();
		$this->user_manager = Utility::get_usermng();
		
		$this->is_search = $is_search;
		$this->browser_page = $browser_page;
		$this->search_page = $search_page;
		$this->search_query = "";
		
		$this->dstats = Array();
		$this->fstats = Array();
		
		$files_base_dir = Utility::get_file_base_dir();
		$_SESSION['base_dir'] = $files_base_dir; // 将文件基路径存入 SESSION
		
		$this->request_sub_dir = $this->get_request_subdir();
		//echo $this->request_sub_dir;
		
		$this->request_dir = $this->prepare_request_dir($files_base_dir, $this->request_sub_dir);
		if(strlen(convert_toutf8($this->request_dir)) == strlen($files_base_dir))
			$this->request_sub_dir = "";
		else
			$this->request_sub_dir = substr(convert_toutf8($this->request_dir), strlen($files_base_dir));
		//echo $this->request_sub_dir;
		
		$this->sort_type = get_query(SORT_PARAM);
		$this->order = get_query(ORDER_PARAM);
		$this->view_type = get_query(VIEW_PARAM);
		
		if($this->sort_type == "")
		{
			// 读取 Cookie 值
			$this->sort_type = get_cookie(SORT_PARAM);
		}
		if($this->order == "")
		{
			$this->order = get_cookie(ORDER_PARAM);
		}
		if($this->view_type == "")
		{
			$this->view_type = get_cookie(VIEW_PARAM);
		}
		
		$this->toolbar_type = get_cookie(TOOLBAR_PARAM);
		if($this->toolbar_type != "little")
			$this->toolbar_type = "full";
		
		$allowed_sort_type = array('', 'n', 's', 't', 'm');
		$allowed_view_type = array('', 'detail', 'largeicon');
		if(!in_array($this->sort_type, $allowed_sort_type))
		{
			$this->sort_type = "";
		}
		if($this->order != "d")
		{
			$this->order = "a";
		}
		if(!in_array($this->view_type, $allowed_view_type))
		{
			$this->view_type = "";
		}
		
		setcookie(SORT_PARAM, $this->sort_type, time() + 60 * 60 * 24 * 365);
		setcookie(ORDER_PARAM, $this->order, time() + 60 * 60 * 24 * 365);
		setcookie(VIEW_PARAM, $this->view_type, time() + 60 * 60 * 24 * 365);
		
		$this->sort = 1;
		if($this->sort_type == "" || ($this->sort_type == "n" && $this->order == "a"))
		{
			$this->sort = 1;
		}
		else if($this->sort_type == "n" && $this->order == "d")
		{
			$this->sort = -1;
		}
		else if($this->sort_type == "s" && $this->order == "a")
		{
			$this->sort = 2;
		}
		else if($this->sort_type == "s" && $this->order == "d")
		{
			$this->sort = -2;
		}
		else if($this->sort_type == "t" && $this->order == "a")
		{
			$this->sort = 3;
		}
		else if($this->sort_type == "t" && $this->order == "d")
		{
			$this->sort = -3;
		}
		else if($this->sort_type == "m" && $this->order == "a")
		{
			$this->sort = 4;
		}
		else if($this->sort_type == "m" && $this->order == "d")
		{
			$this->sort = -4;
		}
		
		$this->dsort = 1;
		if($this->sort == 1 || $this->sort == -1)
		{
			$this->dsort = $this->sort;
		}
		else if($this->sort == 4 || $this->sort == -4)
		{
			$this->dsort = $this->sort > 0 ? 2 : -2;
		}
		
		$this->query_str = "s=".$this->sort_type."&o=".$this->order."&view=".$this->view_type;
		
		if(!$this->is_search)
		{
			$this->init_browser();
		}
		else
		{
			$this->init_search();
		}
		
		if(!isset($_GET['h'])) // 如果有 h，表示是后退或前进来的
		{
			if(!$this->is_search)
			{
				$this->history->push($this->request_sub_dir);
			}
			else
			{
				$this->history->push($this->request_sub_dir, $this->search_query);
			}
		}
	}
	
	/**
	 * 初始化浏览页面
	 */
	private function init_browser()
	{
		if(!Utility::allow_to_browser())
		{
			$this->messageboard->set_message(_("Please login to browse files."), 2);
			return;
		}
		$this->dstats = $this->get_dirs_list($this->request_dir, $this->dsort); // 获得已排序的目录数组
		$this->fstats = $this->get_files_list($this->request_dir, $this->sort); // 获得已排序的文件数组
	}
	
	/**
	 * 初始化搜索页面
	 */
	private function init_search()
	{
		if(!Utility::allow_to_browser())
		{
			$this->messageboard->set_message(_("Please login to search files."), 2);
			return;
		}
		$this->search_query = $this->get_search_query();
		if($this->search_query == "")
		{
			redirect($this->browser_page."?dir=".$this->request_sub_dir);
		}
		
		$search = new Search();
		
		$rows = $search->query($this->search_query, $this->request_sub_dir, $this->sort_type);
		//print_r($rows);
		
		$this->prepare_search_rows($rows);

	}
	
	/**
	 * title 部分
	 * @return title 字符串
	 */
	public function title()
	{
		return _(TITLENAME)." - "._("PHP File Manager");
	}
	
	/**
	 * title 部分的 HTML
	 * @return title 字符串
	 */
	public function title_html()
	{
		return htmlentities_utf8($this->title());
	}
	
	/**
	 * 返回搜索关键字
	 * @return 字符串
	 */
	public function get_search()
	{
		return $this->search_query;
	}
	
	/**
	 * 返回搜索关键字的 HTML
	 * @return 字符串
	 */
	public function get_search_html()
	{
		return htmlentities_utf8($this->get_search());
	}
	
	/**
	 * 需要载入的 css 和 js 文件 HTML 代码
	 */
	public function html_include_files($debug = false)
	{
?>
		<link href="css/filemanager.css" rel="stylesheet" type="text/css" />
		<link href="css/message.css" rel="stylesheet" type="text/css" />
	    <link href="css/detailView.css" rel="stylesheet" type="text/css" />
	    <link href="css/largeiconView.css" rel="stylesheet" type="text/css" />
	    <link href="css/func.css" rel="stylesheet" type="text/css" />
	    <link href="css/jquery.lightbox-0.5.css" rel="stylesheet" type="text/css" />
	    <link href="css/uploadify.css" rel="stylesheet" type="text/css" />
	    <script type="text/javascript" language="javascript" src="js/jquery-1.8.1.min.js"></script>
	    <script type="text/javascript" language="javascript" src="js/audio-player.js"></script> 
	    <script type="text/javascript" language="javascript" src="js/jquery.common.min.js"></script> 
		<script type="text/javascript" language="javascript" src="js/jquery.menu.min.js"></script>
    	<script type="text/javascript" language="javascript" src="js/swfobject.js"></script>
    	<script type="text/javascript" language="javascript" src="js/jquery.uploadify.v2.1.4.min.js"></script>
<?php 
		if($debug)
		{
?>
			<script type="text/javascript" language="javascript" src="js/jquery.lightbox-0.5.plus.js"></script>
	    	<script type="text/javascript" language="javascript" src="js/filemanager.js"></script>
<?php 
		}
		else
		{
?>
	    	<script type="text/javascript" language="javascript" src="js/jquery.lightbox-0.5.plus.pack.js"></script>
	    	<script type="text/javascript" language="javascript" src="js/filemanager.min.js"></script>
<?php 
		}
	}
	
	/**
	 * 获得当前路径
	 * @return 当前路径
	 */
	public function get_current_path()
	{
		return "/".$this->request_sub_dir;
	}
	
	/**
	 * 获得当前目录
	 * @return 当前目录
	 */
	public function get_current_dir()
	{
		$current_dir = "";
		$temp = $this->request_sub_dir;
		$temp = erase_last_slash($this->request_sub_dir);
		
		if($temp == "")
			$current_dir = "Root";
		else
			$current_dir = get_basename($temp);
		
		return $current_dir;
	}
	
	/**
	 * 在 $_GET 中获得所请求的子目录，并适当的整理格式
	 * @return $request_sub_dir
	 */
	private function get_request_subdir()
	{
		$request_sub_dir = rawurldecode(get_query(DIR_PARAM));
		
		if(false !== strpos($request_sub_dir, "..")) // 过滤 ".."
		{
			$request_sub_dir = "";
		}
		
		if($request_sub_dir != "")
		{
			if(substr($request_sub_dir, -1) != "/")
			{
				$request_sub_dir .= "/";
			}
		}
		
		return $request_sub_dir;
	}
	
	/**
	 * 准备 $request_dir
	 * @param $files_base_dir 文件基路径，<strong>以 UTF-8 传入</strong>
	 * @param $request_sub_dir 请求的子目录，<strong>该字符串会被转换成 UTF-8 编码</strong>
	 * @return $request_dir
	 */
	private function prepare_request_dir($files_base_dir, $request_sub_dir)
	{
		//echo $request_sub_dir;
		$files_base_dir_plat = convert_toplat($files_base_dir);
		$request_dir = $files_base_dir_plat.$request_sub_dir; // 获得请求目录路径
		if(PLAT_CHARSET != "UTF-8")
		{
			if(!file_exists($request_dir))
			{
				// 不存在，试试转化成本地编码
				$request_dir = $files_base_dir_plat.convert_toplat($request_sub_dir); // Windows 上可能要转换成 gb2312
				if(!file_exists($request_dir))
				{
					$request_dir = $files_base_dir_plat;
					//$request_sub_dir = "";
				}
			}
			else
			{
				 // 存在说明就是 gb2312 编码的，要换成 utf-8
				//$request_sub_dir = convert_gbtoutf8($request_sub_dir);
			}
		}
		else if(PLAT_CHARSET == "UTF-8")
		{
			if(!file_exists($request_dir))
			{
				// 不存在，试试转化成 UTF-8  编码
				$request_sub_dir = convert_gbtoutf8($request_sub_dir);
				$request_dir = $files_base_dir_plat.$request_sub_dir; // Linux 上可能要转换成 utf-8
				if(!file_exists($request_dir))
				{
					$request_dir = $files_base_dir_plat;
					//$request_sub_dir = "";
				}
			}
			else
			{
				// 存在说明就是 utf-8 编码的，什么都不用做
			}
		}
		
		//echo $request_dir;
		return $request_dir;
	}
	
	private function get_search_query()
	{
		return get_query(SEARCH_PARAM);
	}

	/**
	 * 获得指定路径的已排序文件列表
	 * @param $path 路径
	 * @param $sort 排序方式<br />
	 * 1 按文件名<br />
	 * 2 按大小 <br />
	 * 3 按类型<br />
	 * 4 按修改时间<br />
	 * -1 按文件名逆序<br />
	 * -2 按大小逆序 <br />
	 * -3 按类型逆序<br />
	 * -4 按修改时间逆序
	 * @return array 文件信息数组
	 */
	private function get_files_list($path, $sort = 1)
	{
		$files = array();
		if ($handle = @opendir($path)) {
			//echo "List of files:<br />";
			
		    while (false !== ($file_name = @readdir($handle))) {
		        //echo convert_toutf8($file)."<br />";
		        
		        $full_file_path = $path.$file_name;
		        if(!is_dir($full_file_path))
		        {
		        	//echo convert_toutf8($full_file_path)."<br />";
		        	$fstat = stat($full_file_path);
					$type = Utility::get_file_ext($file_name);
					
		        	$file = array();
		        	$file['name'] = htmlspecialchars(convert_toutf8($file_name));
		        	$file['path'] = convert_toutf8($full_file_path);
		        	$file['type'] = convert_toutf8($type);
		        	$file['stat'] = $fstat;
		        	
		        	if($this->filte($file))
						continue;
					
					// 处理大小
					$size = $file['stat']['size'];
					$size = Utility::format_size($size);
					//echo $request_sub_dir;
					
					//$a_href = FILES_DIR."/".$this->request_sub_dir.$file['name'];
					$a_href = "func/download.func.php?file=".rawurlencode($this->request_sub_dir.$file['name']);
					$type_html = "";
					if($file['type'] == "")
						$type_html = _("File");
					else
						$type_html = $file['type'];
						
					$item_path = $this->request_sub_dir.$file['name'];
					
					$file['size_str'] = $size;
					$file['type_html'] = $type_html;
					$file['a_href'] = $a_href;
					$file['item_path'] = $item_path;
		        	
		        	array_push($files, $file);
		        }
		    }
		
		    closedir($handle);
		    
		    // 排序
		    $cmp_function = "cmp_name";
		    switch($sort)
		    {
		    case 1:
		    	$cmp_function = "cmp_name";
		    	break;
		    case 2:
		    	$cmp_function = "cmp_size";
		    	break;
		   	case 3:
		    	$cmp_function = "cmp_type";
		    	break;
		    case 4:
		    	$cmp_function = "cmp_mtime";
		    	break;
		    case -1:
		    	$cmp_function = "rcmp_name";
		    	break;
		    case -2:
		    	$cmp_function = "rcmp_size";
		    	break;
		    case -3:
		    	$cmp_function = "rcmp_type";
		    	break;
		    case -4:
		    	$cmp_function = "rcmp_mtime";
		    	break;
		    }
		    usort($files, $cmp_function);
		}
		return $files;
	}
	
	/**
	 * 获得指定路径的已排序文件夹列表
	 * @param $path 路径
	 * @param $sort 排序方式<br />
	 * 1 按文件夹名<br />
	 * 2 按修改时间<br />
	 * -1 按文件夹名逆序<br />
	 * -2 按修改时间逆序
	 * @return array 文件夹信息数组
	 */
	private function get_dirs_list($path, $sort = 1)
	{
		$dirs = array();
		if ($handle = @opendir($path)) {
			//echo "List of dirs:<br />";
		    while (false !== ($dir_name = @readdir($handle))) {
		        //echo convert_toutf8($file)."<br />";
		        if($dir_name != "." && $dir_name != "..") // 过滤掉.和 ..
		        {
			        $full_dir_path = $path.$dir_name;
			        if(is_dir($full_dir_path))
			        {
			        	//echo convert_toutf8($full_dir_path)."<br />";
			        	$dstat = stat($full_dir_path);
			        	$dir = array();
			        	$dir['name'] = htmlspecialchars(convert_toutf8($dir_name));
			        	$dir['path'] = convert_toutf8($full_dir_path);
			        	$dir['stat'] = $dstat;
			        	$dir['type'] = "dir";
			        	
			        	if($this->filte($dir))
							continue;
			
						$a_href = $this->browser_page."?".
									$this->query_str."&dir=". 
									rawurlencode($this->request_sub_dir. 
									$dir['name']);
									
						$item_path = $this->request_sub_dir.$dir['name'];
						
						$dir['size_str'] = "&nbsp;";
						$dir['type_html'] = _("Folder");
						$dir['a_href'] = $a_href;
						$dir['item_path'] = $item_path;
			        	
			        	array_push($dirs, $dir);
			        }
		        }
		    }
		
		    closedir($handle);
		    
		    // 排序
			$cmp_function = "cmp_name";
		    switch($sort)
		    {
		    case 1:
		    	$cmp_function = "cmp_name";
		    	break;
		    case 2:
		    	$cmp_function = "cmp_mtime";
		    	break;
		    case -1:
		    	$cmp_function = "rcmp_name";
		    	break;
		    case -2:
		    	$cmp_function = "rcmp_mtime";
		    	break;
		    }
		    usort($dirs, $cmp_function);
		}
		return $dirs;
	}
	
	private function prepare_search_rows($rows)
	{
		$dirs = array();
		$files = array();
		
		if($rows != null)
		{
		
			foreach($rows as $row)
			{
				if($row->type == "dir")
				{
					$dir = array();
					$dir['name'] = $row->name;
				    $dir['stat']['mtime'] = timestrtotime($row->modified);
				    $dir['type'] = "dir";
				
					$a_href = $this->browser_page."?".
						$this->query_str."&dir=". 
						rawurlencode($row->path);
							
					$dir['size_str'] = "&nbsp;";
					$dir['type_html'] = _("Folder");
					$dir['a_href'] = $a_href;
					$dir['item_path'] = $row->path;
				        	
				    array_push($dirs, $dir);
				}
				else
				{
					$file = array();
			        $file['name'] = $row->name;
			        $file['type'] = $row->type;
			        $file['stat']['mtime'] = timestrtotime($row->modified);
			        $file['stat']['size'] = $row->size;
						
					// 处理大小
					$size = $file['stat']['size'];
					$size = Utility::format_size($size);
					//echo $request_sub_dir;
						
					//$a_href = FILES_DIR."/".$this->request_sub_dir.$file['name'];
					$a_href = "func/download.func.php?file=".rawurlencode($row->path);
					$type_html = "";
					if($file['type'] == "")
						$type_html = _("File");
					else
						$type_html = $file['type'];
						
					$file['size_str'] = $size;
					$file['type_html'] = $type_html;
					$file['a_href'] = $a_href;
					$file['item_path'] = $row->path;
			        	
			        array_push($files, $file);
				}
			}
		}
		
		// 排序
		$cmp_function = "cmp_name";
		switch($this->dsort)
		{
		case 1:
			$cmp_function = "cmp_name";
			break;
		case 2:
			$cmp_function = "cmp_mtime";
			break;
		case -1:
			$cmp_function = "rcmp_name";
			break;
		case -2:
			$cmp_function = "rcmp_mtime";
			break;
		}
		usort($dirs, $cmp_function);
		
		switch($this->sort)
		{
		case 1:
			$cmp_function = "cmp_name";
			break;
		case 2:
			$cmp_function = "cmp_size";
			break;
		case 3:
		 	$cmp_function = "cmp_type";
			break;
		case 4:
			$cmp_function = "cmp_mtime";
			break;
		case -1:
			$cmp_function = "rcmp_name";
			break;
		case -2:
			$cmp_function = "rcmp_size";
			break;
		case -3:
			$cmp_function = "rcmp_type";
			break;
		case -4:
			$cmp_function = "rcmp_mtime";
		 	break;
		}
		usort($files, $cmp_function);
		
		$this->dstats = $dirs;
		$this->fstats = $files;
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
	 * 获得上级文件夹路径
	 * @param $request_sub_dir
	 * @return unknown_type
	 */
	private function get_parent_dir($request_sub_dir)
	{
		//echo $request_sub_dir;
		$last_slash = strrpos($request_sub_dir, "/");
		$parent = "";
		if($last_slash !== false)
		{
			$parent = substr($request_sub_dir, 0, $last_slash);
			$last_slash = strrpos($parent, "/");
			if($last_slash !== false)
			{
				$parent = substr($parent, 0, $last_slash);
				
			}
			else
			{
				$parent = "";
			}
		}
		//echo $parent;
		return $parent;
	}
	
	/**
	 * 显示当前的子目录路径
	 */
	public function display_full_path()
	{
		if(!Utility::allow_to_browser())
			return;
?>
		<div id="fullpath">
			<div>
			    <div class="divDir"><a href="<?php echo $this->browser_page."?".$this->query_str; ?>">Root</a></div>
			    <div class="pathSlash menuContainer">
			    	<a class="arrow menuButton" href="javascript:void(0);">&nbsp;</a>
			        	
<?php 
					$sub_dirs = explode("/", $this->request_sub_dir);
					$dir_str = "";
					$this->display_sub_menus($dir_str, $sub_dirs[0]);
?>
			    </div>
<?php 
			        //print_r($sub_dirs);
			        $len = count($sub_dirs);
			        $sub_dirs[$len] = "";
			        for($i = 0; $i < $len - 1; $i++)
			        {
			        	$sub_dir = $sub_dirs[$i];
			        	$dir_str .= $sub_dir;
?>
			    <div class="divDir">
			        <a href="<?php echo $this->browser_page."?".$this->query_str; ?>&dir=<?php echo rawurlencode($dir_str); ?>">
			        	<?php echo str_replace(" ", "&nbsp;", $sub_dir); ?>
			        </a>
			    </div>
			    <div class="pathSlash menuContainer">
			    	<a class="arrow menuButton" href="javascript:void(0);">&nbsp;</a>
<?php 
						$dir_str .= "/";
						$this->display_sub_menus($dir_str, $sub_dirs[$i + 1]);
						
?>
			    </div>
<?php 
				    }
?>
			</div>
			<div class="clear"></div>

	    </div>
<?php 
	}
	
	/**
	 * 填充子目录菜单
	 * @param $sub_dir_str 至当前目录的路径
	 * @param $next_in_path 下一个在路径中的目录名
	 */
	private function display_sub_menus($sub_dir_str, $next_in_path = "")
	{
		$temp = $sub_dir_str;
		$base_dir = $this->prepare_request_dir(Utility::get_file_base_dir(), $temp);
		if($base_dir != "")
		{
			$sub_dstats = $this->get_dirs_list($base_dir);
			if(count($sub_dstats) > 0)
			{
?>
			<div class="subMenu">
				<ul>
<?php 
				foreach($sub_dstats as $sub_dstat)
				{
					if(!$this->filte($sub_dstat))
					{
?>
					<li>
						<a href="<?php echo $this->browser_page."?".$this->query_str; ?>&dir=<?php echo rawurlencode($sub_dir_str.$sub_dstat['name']); ?>" title="<?php echo $sub_dstat['name']; ?>">
							<?php 
								if($sub_dstat['name'] == $next_in_path)
									printf("<strong>%s</strong>", str_replace(" ", "&nbsp;", $sub_dstat['name']));
								else
									printf(str_replace(" ", "&nbsp;", $sub_dstat['name']));
							?>
						</a>
					</li>
<?php 
					}
				}
?>
				</ul>
			</div>
<?php 
			}
		}
	}
	
	/**
	 * 显示工具栏
	 */
	public function display_toolbar()
	{
		$this_page = $this->is_search ? $this->search_page : $this->browser_page;
		
		// 准备基本图标
		$this->prepare_basic_funcs($query_str, $up, $up_img, $new_folder_img, $upload_img);
		
		// 准备图标模式和清除搜索
		$detail_view_url = $this_page."?".$query_str.
							"&dir=".rawurlencode($this->request_sub_dir)."&view=detail";
		$largeicon_view_url = $this_page."?".$query_str .
							"&dir=".rawurlencode($this->request_sub_dir)."&view=largeicon";
		$clean_search_url = $this->browser_page."?".$this->query_str.
							"&dir=".rawurlencode($this->request_sub_dir);
		
		// 准备粘贴
		$this->prepare_paste_func($paste_img_src, $paste_class);
		
		// 准备历史
		$this->prepare_history_funcs($back_url, $back_class, $forward_url, $forward_class);
		$history_items = $this->render_history_items();
		
		// 准备 more
		$more_img_src = "images/toolbar-arrow-left.gif";
		$more_class = "full";
		if($this->toolbar_type == "little")
		{
			$more_img_src = "images/toolbar-arrow-right.gif";
			$more_class = "little";
		}
		
		// 准备按钮名称
		$button_names = $this->prepare_buttons_name();
		
		// 下面是工具栏的 HTML 部分
?>
		<div id="toolbar">
			<div id="leftToolbar">
				<a href="<?php echo $back_url; ?>" title="<?php echo $button_names['Back']; ?>" class="toolbarButton toolbarBack <?php echo $back_class; ?>">
					<img alt="<?php echo $button_names['Back']; ?>" src="images/toolbar-back.png" />
				</a>
				<a href="<?php echo $forward_url; ?>" title="<?php echo $button_names['Forward']; ?>" class="toolbarButton toolbarForward <?php echo $forward_class; ?>">
					<img alt="<?php echo $button_names['Forward']; ?>" src="images/toolbar-forward.png" />
				</a>
				<div class="toolbarSmallButton menuContainer toolbarHistory splitRight">
					<img class="menuButton" src="images/toolbar-history.png" />
					<?php 
					if($this->history->get_length() > 1)
					{
					?>
					<div class="subMenu">
						<ul class="menuSpace">
							<?php echo $history_items; ?>
						</ul>
					</div>
					<?php 
					}
					?>
				</div>
				<div title="<?php echo $button_names['Refresh']; ?>" class="toolbarButton toolbarRefresh">
					<img alt="<?php echo $button_names['Refresh']; ?>" src="images/toolbar-refresh.png" />
				</div>
				<a href="<?php echo $up; ?>" title="<?php echo $button_names['Up']; ?>" class="toolbarButton toolbarUp splitRight">
					<img alt="<?php echo $button_names['Up']; ?>" src="<?php echo $up_img; ?>" />
				</a>
				<?php 
				if(Utility::allow_to_modify())
				{
				?>
				<div class="toolbarPart">
					<div>
						<div title="<?php echo $button_names['Cut']; ?>" class="toolbarButton toolbarCut">
							<img alt="<?php echo $button_names['Cut']; ?>" src="images/toolbar-cut.png" />
						</div>
						<div title="<?php echo $button_names['Copy']; ?>" class="toolbarButton toolbarCopy">
							<img alt="<?php echo $button_names['Copy']; ?>" src="images/toolbar-copy.png" />
						</div>
						<div title="<?php echo $button_names['Paste']; ?>" class="toolbarButton toolbarPaste splitRight <?php echo $paste_class; ?>">
							<img alt="<?php echo $button_names['Paste']; ?>" src="<?php echo $paste_img_src; ?>" />
						</div>
						<div title="<?php echo $button_names['New Folder']; ?>" class="toolbarButton toolbarNewFolder">
							<img alt="<?php echo $button_names['New Folder']; ?>" src="<?php echo $new_folder_img; ?>" />
						</div>
						<div title="<?php echo $button_names['Rename']; ?>" class="toolbarButton toolbarRename">
							<img alt="<?php echo $button_names['Rename']; ?>" src="images/toolbar-rename.png" />
						</div>
						<div title="<?php echo $button_names['Delete']; ?>" class="toolbarButton toolbarDelete splitRight" >
							<img alt="<?php echo $button_names['Delete']; ?>" src="images/toolbar-delete.png" />
						</div>
						<div title="<?php echo $button_names['Upload']; ?>" class="toolbarButton toolbarUpload splitRight">
							<img alt="<?php echo $button_names['Upload']; ?>" src="<?php echo $upload_img; ?>" />
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
            <div id="rightToolbar">
				<?php 
				if(SEARCH)
				{
				?>
				<form id="searchForm" action="<?php echo $this->search_page ?>" method="get" class="splitLeft">
            		<input id="q" name="q" type="text" value="<?php echo $this->search_query; ?>" maxlength="255" size="10" />
            		<input type="hidden" name="dir" value="<?php echo $this->request_sub_dir; ?>" />
            		<input type="submit" value="<?php echo _("Search"); ?>" title="<?php echo _("Search"); ?>" />
            		<?php 
	            	if($this->is_search)
	            	{
	            	?>
	            	<a href="<?php echo $clean_search_url; ?>" title="<?php echo $button_names['Clean Search']; ?>" class="toolbarSmallButton">
						<img alt="<?php echo $button_names['Clean Search']; ?>" src="images/close.png" />
					</a>
	            	<?php 
	            	}
	            	?>
            	</form>
            	<?php 
				}
				?>
            </div>
        </div>
<?php 
	}
	
	/**
	 * 显示主要视图
	 * @param $display_type 显示方式
	 */
	public function display_main_view()
	{
?>
		<div id="mainView">
			<?php 
			// 显示列表头
			$this->display_header();
			
			$items = array_merge($this->dstats, $this->fstats);
			
			?>
			<div id="mainViewList">
			<?php 
			$this->render_main_view($items);
			?>
			</div>
        </div>
<?php 
	}
	
	/**
	 * 为功能准备的 HTML 内容
	 */
	public function display_func_pre()
	{
		$multilan_titles = "";
		$multilan_titles .= ("rename:"._("Rename")."|");
		$multilan_titles .= ("new folder:"._("New Folder")."|");
		$multilan_titles .= ("upload:"._("Upload")."|");
		$multilan_titles .= ("delete:"._("Confirm")."|");
		$multilan_titles .= ("audio:"._("Audio Player")."|");
		$multilan_titles .= ("user:"._("User")."|");
		$multilan_titles .= ("waiting:"._("Working...")."|");
?>
		<div id="funcBg">
		</div>
		<div id="funcDialog">
			<div class="divHeader">
				<span><?php echo $multilan_titles; ?></span>
				<a class="funcClose" href="javascript:;">
					<img alt="Close" src="images/close.png" border="0">
				</a>
			</div>
			<div id="divInput" class="container">
				<form action="" method="post" enctype="multipart/form-data">
					<input type="hidden" id="oper" name="oper" value="" />
					<input type="hidden" id="subdir" name="subdir" value="<?php echo rawurlencode($this->request_sub_dir); ?>" />
					<input type="hidden" id="renamePath" name="renamePath" value="" />
					<input type="hidden" id="return" name="return" value="<?php echo rawurlencode(get_URI()); ?>" />
					<div id="divReqInput">
						<table>
							<tr id="oldnameLine">
								<td><label for="oldname"><?php printf("%s&nbsp;", _("Old Name:")); ?></label></td>
								<td><input id="oldname" type="text" name="oldname" value="" maxlength="128" readonly="readonly"/></td>
							</tr>
							<tr>
								<td><label for="newname"><?php printf("%s&nbsp;", _("New Name:")); ?></label></td>
								<td><input id="newname" type="text" name="newname" value="" maxlength="128" /></td>
							</tr>
						</table>
						<div>
							<span class="inputRequire"><?php printf(_("There should not have %s in new name"), ".., /, \, *, ?, \", |, &amp;, &lt;, &gt;"); ?></span>
						</div>
					</div>
					<div id="divUpload">
						<div>
							<label for="uploadFile"><?php printf("%s&nbsp;", _("Select File:")); ?></label>
							<input id="uploadFile" type="file" name="uploadFile"/>
						</div>
						<div>
							<span class="inputRequire"><?php printf("%s%s", _("File cannot be larger than "), "50MB"); ?></span>
						</div>
					</div>
					<div id="divLogin">
						<table>
							<tr>
								<td><label for="username"><?php printf("%s&nbsp;", _("Username:")); ?></label></td>
								<td><input id="username" type="text" name="username" value="" maxlength="128"/></td>
							</tr>
							<tr>
								<td><label for="password"><?php printf("%s&nbsp;", _("Password:")); ?></label></td>
								<td><input id="password" type="password" name="password" value="" maxlength="128" /></td>
							</tr>
						</table>
					</div>
					<div id="divLogout">
						<div class="center"><?php echo _("Are you sure to logout?"); ?></div>
					</div>
					<div class="funcBtnLine">
						<input type="submit" value="<?php echo _("OK"); ?>" onclick="FileManager.funcSubmit()" /><input type="button" value="<?php echo _("Cancel"); ?>" onclick="FileManager.closeFunc()"/>
					</div>
				</form>
			</div>
			<div id="divDelete" class="container">
				<div class="center"><?php echo _("Are you sure to delete these items?"); ?></div>
				<div class="funcBtnLine">
					<input type="button" value="<?php echo _("OK"); ?>" onclick="FileManager.doDelete()"/><input type="button" value="<?php echo _("Cancel"); ?>" onclick="FileManager.closeFunc()"/>
				</div>
			</div>
			<div id="divAudio" class="container center">
				<div id="divAudioPlayer">Audio Player</div>
				<div id="link"></div>
			</div>
			<div id="divWaiting" class="container center">
				<div class="wating">
					<img alt="wating" src="images/loadingAnimation.gif" border="0">
				</div>
			</div>
		</div>
		
		<div id="phpfmMessage">
    	
    	</div>
<?php 
	}
	
	/**
	 * 显示目录和文件部分
	 */
	private function render_main_view($items)
	{
		// 详细视图
?>
		<ul id="detailView" class="<?php echo $this->sort_type; ?>">
<?php 
		$i = 0;
		foreach($items as $item)
		{
			$this->detail_view_item($item['item_path'],
									$item['a_href'],
									$item['name'],
									Utility::get_icon($item['type'], 32),
									$item['name'],
									$item['size_str'],
									$item['type_html'],
									$item['stat']['mtime']);
			$i++;
		}
		//$this->mark_to_20($i);
?>
        </ul>
<?php 
	}
	
	/**
	 * 显示列表头
	 */
	private function display_header()
	{
		$this_page = $this->is_search ? $this->search_page : $this->browser_page;
		
		$request_sub_dir = $this->request_sub_dir;
		$sort_type = $this->sort_type;
		$order = $this->order;
		
		$norder = "a";
		$sorder = "a";
		$torder = "a";
		$morder = "a";
		
		if($sort_type == "" || ($sort_type == "n" && $order == "a"))
		{
			$norder = "d";
		}
		else if($sort_type == "s" && $order == "a")
		{
			$sorder = "d";
		}
		else if($sort_type == "t" && $order == "a")
		{
			$torder = "d";
		}
		else if($sort_type == "m" && $order == "a")
		{
			$morder = "d";
		}
?>
		<div class="header">
			<span class="check">
				<input id="checkSelectAll" type="checkbox" title="<?php echo _("Select All"); ?>" />
			</span>
			<span class="icon">&nbsp;</span>
			<span class="name split">
				<a href="<?php echo $this_page."?q=".
				$this->search_query."&dir=".
				$request_sub_dir."&s=n".
				"&o=".$norder; ?>"><?php echo _("Name"); ?></a>
			</span>
			<span class="size split">
				<a href="<?php echo $this_page."?q=".
				$this->search_query."&dir=".
				$request_sub_dir."&s=s" .
				"&o=".$sorder; ?>"><?php echo _("Size"); ?></a>
			</span>
			<span class="type split">
				<a href="<?php echo $this_page."?q=".
				$this->search_query."&dir=".
				$request_sub_dir."&s=t" .
				"&o=".$torder; ?>"><?php echo _("Type"); ?></a>
			</span>
			<span class="mtime split">
				<a href="<?php echo $this_page."?q=".
				$this->search_query."&dir=".
				$request_sub_dir."&s=m" .
				"&o=".$morder; ?>"><?php echo _("Modified Time"); ?></a>
			</span>
		</div>
<?php 
		$javascript_call_arg = "name";
		if($sort_type == "s")
		{
			$javascript_call_arg = "size";
		}
		else if($sort_type == "t")
		{
			$javascript_call_arg = "type";
		}
		else if($sort_type == "m")
		{
			$javascript_call_arg = "mtime";
		}
		
?>
		<script type="text/javascript" >
			//<![CDATA[
			FileManager.setSortArrow(<?php echo "\"$javascript_call_arg\""; ?>, <?php echo "\"$order\""; ?>);
			FileManager.setSearchMode(<?php echo ($this->is_search?"true":"false"); ?>);
			//]]>
		</script>
<?php 
	}
	
	/**
	 * 显示“向上”
	 */
	private function display_up()
	{
		if($this->request_sub_dir != "")
		{
			//echo $request_sub_dir;
			$up = $this->browser_page."?";
			$up .= $this->query_str;
			$up .= ("&dir=".$this->get_parent_dir($this->request_sub_dir));		
?>
			<li>
				<span class="check"></span>
				<a href="<?php echo $up; ?>">
					<span class="icon">
					<img src="images/go-up.gif" alt="file icon" width="16" height="16" border="0" />
					</span>
					<span class="name"><?php echo _("Up"); ?></span>
					<span class="size">&nbsp;</span>
					<span class="type">&nbsp;</span>
					<span class="mtime">&nbsp;</span>
				
				</a>
			</li>	
<?php 
		}
	}
	
	/**
	 * 列表显示行
	 * @param $a_href 
	 * @param $a_title
	 * @param $img_html
	 * @param $name
	 * @param $size
	 * @param $type
	 * @param $mtime
	 */
	private function detail_view_item($item_path = "",
									$a_href = "", 
									$a_title = "", 
									$img_html = "", 
									$name = "",
									$size = "",
									$type = "",
									$mtime = 0)
	{
		$class = "";
		if(LIGHTBOX && $this->is_img_type($type))
			$class = 'class="lightboxImg"';

		if(AUDIOPLAYER && $this->is_audio_type($type))
			$class = 'class="audioPlayer"';
			
		
?>
			<li >
				<span class="check">
					<input class="inputCheck" type="checkbox" name="<?php echo $item_path; ?>" />
				</span>
				<a href="<?php echo $a_href; ?>" title="<?php echo $a_title; ?>" <?php echo $class; ?>>
					<span class="icon"><?php echo $img_html; ?></span>
					<span class="name"><?php echo str_replace(" ", "&nbsp;", $name); ?></span>
				</a>
				<span class="size"><?php echo $size; ?></span>
				<span class="type"><?php echo $type; ?></span>
				<span class="mtime"><?php echo date("Y-n-j H:i", $mtime); ?></span>
			</li>
<?php 
	}
	
	/**
	 * Large Icon 显示一个项目
	 * @param $a_href 
	 * @param $a_title
	 * @param $img_html
	 * @param $name
	 * @param $size
	 * @param $type
	 * @param $mtime
	 */
	private function largeicon_view_item($item_path = "",
										$a_href = "", 
										$a_title = "", 
										$img_html = "", 
										$name = "",
										$size = "",
										$type = "",
										$mtime = 0)
	{
		$class_1 = "";
		$class_2 = "";
		if(LIGHTBOX && $this->is_img_type($type))
			$class_1 = 'class="lightboxImg"';
		if(AUDIOPLAYER && $this->is_audio_type($type))
		{
			$class_1 = 'class="audioPlayer"';
			$class_2 = 'class="audioPlayer"';
		}
?>
			<div class="largeIconItem" >
				<div class="firstLine">
					<input class="inputCheck" type="checkbox" name="<?php echo $item_path; ?>" />
					<span class="type"><?php echo $type; ?></span>
				</div>
				<div class="imgLine">
					<a href="<?php echo $a_href; ?>" title="<?php echo $a_title; ?>" <?php echo $class_1; ?>>
						<?php echo $img_html; ?>
					</a>
				</div>
				<div class="infoLine">
					<a href="<?php echo $a_href; ?>" title="<?php echo $a_title; ?>" <?php echo $class_2; ?>>
						<span class="name"><?php echo str_replace(" ", "&nbsp;", $name); ?></span>
					</a>
					<span class="size"><?php echo $size; ?></span>
					<span class="mtime"><?php echo date("Y-n-j H:i", $mtime); ?></span>
				</div>
			</div>
<?php 
	}
	
	
	/**
	 * 补齐 20
	 * @param $total 已经有的行数
	 */
	private function mark_to_20($total)
	{
		$empty = "";
		if($this->view_type == "largeicon")
		{
			$empty = '<div class="largeIconItem empty"></div>';
		}
		else
		{
			$empty = '<li class="empty"></li>';
		}
		if($total < 20)
		{
			$need = 20 - $total;
			for($i = 0; $i < $need; $i++)
			{

				echo "$empty\n";
			}
		}
	}
	
	/**
	 * 根据扩展名判断是不是图片格式
	 * @param $type 扩展名
	 * @return boolean 是 true，否 false
	 */
	private function is_img_type($type)
	{
		$type = strtolower($type);
		if($type == "jpg" ||
			$type == "jpeg" ||
			$type == "bmp" ||
			$type == "png" ||
			$type == "gif" )
			return true;
		else
			return false;
	}	
	
	/**
	 * 根据扩展名判断是不是音乐格式
	 * @param $type 扩展名
	 * @return boolean 是 true，否 false
	 */
	private function is_audio_type($type)
	{
		$type = strtolower($type);
		if($type == "mp3")
			return true;
		else
			return false;
	}
	
	/**
	 * 准备基本功能
	 * @param $query_str query string
	 * @param $up 向上地址
	 * @param $up_img 向上图标
	 * @param $new_folder_img 新建目录图标
	 * @param $upload_img 上传图标
	 */
	private function prepare_basic_funcs(
		&$query_str, &$up, 
		&$up_img, &$new_folder_img, 
		&$upload_img )
	{
		$query_str = $this->query_str;
		$up = "";
		$up_img = "images/toolbar-up.png";
		$new_folder_img = "images/toolbar-new-folder.png";
		$upload_img = "images/toolbar-upload.png";
		
		//echo $request_sub_dir;
		// 设置向上，新建目录和上传按钮状态
		if(!$this->is_search)
		{
			// 浏览模式
			$up = $this->browser_page."?";
			$up .= $this->query_str;
			$up .= ("&dir=".rawurlencode($this->get_parent_dir($this->request_sub_dir)));
			
		}
		else
		{
			// 搜索模式
			$query_str = "q=".$this->search_query."&".$query_str;
			
			$up = "javascript:;";
			
			$up_img = "images/toolbar-up.png";
			$new_folder_img = "images/toolbar-new-folder.png";
			$upload_img = "images/toolbar-upload.png";
		}
	}
	
	/**
	 * 准备粘贴
	 * @param $paste_img 粘贴图标
	 * @param $paste_class 粘贴 class
	 */
	private function prepare_paste_func(&$paste_img, &$paste_class)
	{
		$paste_img = "images/toolbar-paste.png";
		$paste_class = "disable";
		if($this->clipboard->have_items() && $this->is_search == false)
		{
			$paste_img = "images/toolbar-paste.png";
			$paste_class = "";
		}
	}
	
	/**
	 * 准备历史功能状态
	 * @param $back_url 后退地址
	 * @param $back_class 后退 class
	 * @param $forward_url 前进地址
	 * @param $forward_class 前进 class
	 */
	private function prepare_history_funcs(
		&$back_url, &$back_class, 
		&$forward_url, &$forward_class)
	{
		$back_url = "javascript:;";
		$back_class = "disable";
		$forward_url = "javascript:;";
		$forward_class = "disable";
		if($this->history->able_to_back())
		{
			$back_class = "";
			$back_url = "func/history.func.php?action=b&sp=".$this->search_page;
		}
		if($this->history->able_to_forward())
		{
			$forward_class = "";
			$forward_url = "func/history.func.php?action=f&sp=".$this->search_page;
		}
	}
	
	/**
	 * 将历史转变成 HTML
	 * @return string
	 */
	private function render_history_items()
	{
		$history_current = $this->history->get_current() - 1;
		$history = $this->history->get_history();
		$history_items = "";
		$i = 0;
		foreach($history as $item)
		{
			if($i >= $this->history->get_length())
				break;
				
			$url = "func/history.func.php?action=f&sp=".$this->search_page."&step=".($i-$history_current);
			if($i != $history_current)
				$history_items .= ('<li><a href="'.$url.'">');
			else
				$history_items .= ('<li class="current">');
				
			if($item->is_search())
				$history_items .= (_("Search").' '.$item->to_string());
			else
				$history_items .= ($item->to_string());
			
			if($i != $history_current)
				$history_items .= '</a></li>';
			else
				$history_items .= '</li>';
			
			++$i;
		}
		
		return $history_items;
	}
	
	/**
	 * 设置按钮的 title
	 * @return Array
	 */
	private function prepare_buttons_name()
	{
		$button_names['Back'] = _('Back');
		$button_names['Forward'] = _('Forward');
		$button_names['Refresh'] = _("Refresh");
		$button_names['Up'] = _("Up");
		$button_names['Cut'] = _("Cut");
		$button_names['Copy'] = _("Copy");
		$button_names['Paste'] = _("Paste");
		$button_names['New Folder'] = _("New Folder");
		$button_names['Rename'] = _("Rename");
		$button_names['Delete'] = _("Delete");
		$button_names['Upload'] = _("Upload");
		$button_names['Large Icon View'] = _("Large Icon View");
		$button_names['Detail View'] = _("Detail View");
		$button_names['Clean Search'] = _("Clean Search");
		
		return $button_names;
	}
	
	/**
	 * 用户是否登录
	 * 直接调用 UserManager->is_logged()
	 */
	private function is_logged()
	{
		return $this->user_manager->is_logged();
	}
	
	/**
	 * 获得用户对象
	 * 直接调用 UserManager->get_user()
	 */
	private function get_user()
	{
		return 	$this->user_manager->get_user();
	}
}

?>