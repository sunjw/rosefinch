<?php

require_once "defines.inc.php";
require_once "common.inc.php";
require_once "gettext.inc.php";
require_once "sort.inc.php";
require_once "clipboard.class.php";
require_once "messageboard.class.php";
require_once "utility.class.php";

@session_start();

/**
 * File Manager Class
 * 2009-8-4 rev.1
 * @author Sun Junwen
 *
 */
class FileManager
{
	private $request_sub_dir; // 请求的子目录
	private $request_dir; // 请求目录对应的系统绝对路径
	private $sort_type; // 排序方式
	private $order; // 排序方向
	private $view_type; // 视图模式
	
	private $sort; // 文件排序代号
	private $dsort;
	private $query_str;
	
	private $fstats;
	private $dstats;
	
	private $clipboard;
	private $messageboard;
	
	function __construct()
	{
		/*
		 * 内部字符串全部使用 UTF-8 编码
		 */
		
		$this->clipboard = Utility::get_clipboard();
		$this->messageboard = Utility::get_messageboard();
		
		$files_base_dir = Utility::get_file_base_dir();
		
		$this->request_sub_dir = $this->get_request_subdir();
		//echo $this->request_sub_dir;
		
		$this->request_dir = $this->prepare_request_dir($files_base_dir, $this->request_sub_dir);
		if(strlen(convert_toutf8($this->request_dir)) == strlen($files_base_dir))
			$this->request_sub_dir = "";
		else
			$this->request_sub_dir = substr(convert_toutf8($this->request_dir), strlen($files_base_dir));
		//echo $this->request_sub_dir;
		$_SESSION['base_dir'] = $files_base_dir; // 将文件基路径存入 SESSION
		
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
		
		$this->query_str = "s=" . $this->sort_type . "&o=" . $this->order . "&view=" . $this->view_type;
		
		$this->dstats = $this->get_dirs_list($this->request_dir, $this->dsort); // 获得已排序的目录数组
		$this->fstats = $this->get_files_list($this->request_dir, $this->sort); // 获得已排序的文件数组
	}
	
	/**
	 * title 部分
	 * @return title 字符串
	 */
	public function title()
	{
		return _(TITLENAME) . " - " . _("PHP File Manager");
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
	    <link href="css/ajax.css" rel="stylesheet" type="text/css" />
	    <link href="css/jquery.lightbox-0.5.css" rel="stylesheet" type="text/css" />
	    <script type="text/javascript" language="javascript" src="js/jquery-1.3.2.min.js"></script>
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
		return "/" . $this->request_sub_dir;
	}
	
	/**
	 * 获得当前目录
	 * @return 当前目录
	 */
	public function get_current_dir()
	{
		$current_dir = "";
		$temp = $this->request_sub_dir;
		if(mb_substr($this->request_sub_dir, -1) == "/" || mb_substr($this->request_sub_dir, -1) == "\\" )
			$temp = mb_substr($this->request_sub_dir, 0, mb_strlen($this->request_sub_dir) - 1);
		
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
		$request_dir = $files_base_dir_plat . $request_sub_dir; // 获得请求目录路径
		if(PLAT_CHARSET != "UTF-8")
		{
			if(!file_exists($request_dir))
			{
				// 不存在，试试转化成本地编码
				$request_dir = $files_base_dir_plat . convert_toplat($request_sub_dir); // Windows 上可能要转换成 gb2312
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
				$request_sub_dir = convert_toutf8($request_sub_dir);
				$request_dir = $files_base_dir_plat . $request_sub_dir; // Linux 上可能要转换成 utf-8
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
		        //echo convert_toutf8($file) . "<br />";
		        
		        $full_file_path = $path . $file_name;
		        if(!is_dir($full_file_path))
		        {
		        	//echo convert_toutf8($full_file_path) . "<br />";
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
					
					//$a_href = FILES_DIR . "/" . $this->request_sub_dir . $file['name'];
					$a_href = "func/download.func.php?file=" . rawurlencode($this->request_sub_dir . $file['name']);
					$type_html = "";
					if($file['type'] == "")
						$type_html = _("File");
					else
						$type_html = $file['type'];
						
					$item_path = $this->request_sub_dir . $file['name'];
					
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
		        //echo convert_toutf8($file) . "<br />";
		        if($dir_name != "." && $dir_name != "..") // 过滤掉 . 和 ..
		        {
			        $full_dir_path = $path . $dir_name;
			        if(is_dir($full_dir_path))
			        {
			        	//echo convert_toutf8($full_dir_path) . "<br />";
			        	$dstat = stat($full_dir_path);
			        	$dir = array();
			        	$dir['name'] = htmlspecialchars(convert_toutf8($dir_name));
			        	$dir['path'] = convert_toutf8($full_dir_path);
			        	$dir['stat'] = $dstat;
			        	$dir['type'] = "dir";
			        	
			        	if($this->filte($dir))
							continue;
			
						$a_href = $_SERVER['PHP_SELF'] . "?" .
									$this->query_str. "&dir=" . 
									rawurlencode($this->request_sub_dir . 
									$dir['name']);
									
						$item_path = $this->request_sub_dir . $dir['name'];
						
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
?>
		<div id="fullpath">
			<div>
				<div class="divLabel"><?php echo _("Current path:"); ?>&nbsp;</div>
			    <div class="divDir"><a href="index.php?<?php echo $this->query_str; ?>">Root</a></div>
			    <div class="pathSlash">
			    	<a class="arrow" href="javascript:void(0);">&nbsp;</a>
			        	
<?php 
					$sub_dirs = split("[/]", $this->request_sub_dir);
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
			        <a href="index.php?<?php echo $this->query_str; ?>&dir=<?php echo rawurlencode($dir_str); ?>">
			        	<?php echo str_replace(" ", "&nbsp;", $sub_dir); ?>
			        </a>
			    </div>
			    <div class="pathSlash">
			    	<a class="arrow" href="javascript:void(0);">&nbsp;</a>
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
						<a href="index.php?<?php echo $this->query_str; ?>&dir=<?php echo rawurlencode($sub_dir_str . $sub_dstat['name']); ?>" title="<?php echo $sub_dstat['name']; ?>">
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
		$up = "";
		//echo $request_sub_dir;
		$up = $_SERVER['PHP_SELF'] . "?";
		$up .= $this->query_str;
		$up .= ("&dir=" . rawurlencode($this->get_parent_dir($this->request_sub_dir)));
		
		$detail_view_url = $_SERVER['PHP_SELF'] . "?" . $this->query_str .
							"&dir=" . rawurlencode($this->request_sub_dir) . "&view=detail";
		$largeicon_view_url = $_SERVER['PHP_SELF'] . "?" . $this->query_str .
							"&dir=" . rawurlencode($this->request_sub_dir) . "&view=largeicon";
		
		$paste_img_src = "images/toolbar-paste-disable.gif";
		$paste_click = "doNothing()";
		$paste_class = "disable";
		if($this->clipboard->have_items())
		{
			$paste_img_src = "images/toolbar-paste.gif";
			$paste_click = "clickPaste()";
			$paste_class = "";
		}
		$upload_click = "clickUpload()";
		
		$button_names['Refresh'] = _("Refresh");
		$button_names['Up'] = _("Up");
		$button_names['Select All'] = _("Select All");
		$button_names['Deselect'] = _("Deselect");
		$button_names['Cut'] = _("Cut");
		$button_names['Copy'] = _("Copy");
		$button_names['Paste'] = _("Paste");
		$button_names['New Folder'] = _("New Folder");
		$button_names['Rename'] = _("Rename");
		$button_names['Delete'] = _("Delete");
		$button_names['Upload'] = _("Upload");
		$button_names['Large Icon View'] = _("Large Icon View");
		$button_names['Detail View'] = _("Detail View");
		
?>
		<div id="toolbar">
			<div id="leftToolbar">
				<a href="javascript:window.location.reload();" title="<?php echo $button_names['Refresh']; ?>" class="toolbarRefresh">
					<img alt="<?php echo $button_names['Refresh']; ?>" src="images/toolbar-refresh.gif" />
				</a>
				<a href="<?php echo $up; ?>" title="<?php echo $button_names['Up']; ?>" class="toolbarUp splitRight">
					<img alt="<?php echo $button_names['Up']; ?>" src="images/toolbar-up.gif" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['Select All']; ?>" class="toolbarSelectAll" onclick="selectAll()">
					<img alt="<?php echo $button_names['Select All']; ?>" src="images/toolbar-select-all.gif" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['Deselect']; ?>" class="toolbarDeselect splitRight" onclick="deselect()">
					<img alt="<?php echo $button_names['Deselect']; ?>" src="images/toolbar-deselect.gif" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['Cut']; ?>" class="toolbarCut">
					<img alt="<?php echo $button_names['Cut']; ?>" src="images/toolbar-cut-disable.gif" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['Copy']; ?>" class="toolbarCopy">
					<img alt="<?php echo $button_names['Copy']; ?>" src="images/toolbar-copy-disable.gif" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['Paste']; ?>" class="toolbarPaste splitRight <?php echo $paste_class; ?>" onclick="<?php echo $paste_click; ?>">
					<img alt="<?php echo $button_names['Paste']; ?>" src="<?php echo $paste_img_src; ?>" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['New Folder']; ?>" class="toolbarNewFolder" onclick="clickNewFolder()">
					<img alt="<?php echo $button_names['New Folder']; ?>" src="images/toolbar-new-folder.gif" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['Rename']; ?>" class="toolbarRename">
					<img alt="<?php echo $button_names['Rename']; ?>" src="images/toolbar-rename-disable.gif" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['Delete']; ?>" class="toolbarDelete splitRight" >
					<img alt="<?php echo $button_names['Delete']; ?>" src="images/toolbar-delete-disable.gif" />
				</a>
				<a href="javascript:;" title="<?php echo $button_names['Upload']; ?>" class="toolbarUpload splitRight"  onclick="<?php echo $upload_click; ?>">
					<img alt="<?php echo $button_names['Upload']; ?>" src="images/toolbar-upload.gif" />
				</a>
			</div>
            <div id="rightToolbar">
				<a href="<?php echo $largeicon_view_url; ?>" title="<?php echo $button_names['Large Icon View']; ?>" class="splitLeft">
					<img alt="<?php echo $button_names['Large Icon View']; ?>" src="images/view-largeicon.gif" />
				</a>
				<a href="<?php echo $detail_view_url; ?>" title=<?php echo $button_names['Detail View']; ?>">
					<img alt="<?php echo $button_names['Detail View']; ?>" src="images/view-detail.gif" />
				</a>
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
			
			$this->render_main_view($items);
			?>
        </div>
<?php 
	}
	
	/**
	 * 显示为 Ajax 功能准备的 HTML 内容
	 */
	public function display_ajax_pre()
	{
?>
		<div id="ajaxBg">
		</div>
		<div id="ajaxInput">
			<div class="ajaxHeader">
				<span></span>
				<a class="ajaxFuncClose" href="javascript:;">
					<img alt="Close" src="images/ajax-func-close.gif" border="0">
				</a>
			</div>
			<form action="" method="post" enctype="multipart/form-data">
				<input type="hidden" id="oper" name="oper" value="" />
				<input type="hidden" id="subdir" name="subdir" value="<?php echo rawurlencode($this->request_sub_dir); ?>" />
				<input type="hidden" id="return" name="return" value="<?php echo rawurlencode(get_URI()); ?>" />
				<div id="divInput">
					<div id="oldnameLine">
						<label for="oldname"><?php printf("%s&nbsp;", _("Old Name:")); ?></label>
						<input id="oldname" type="text" name="oldname" value="" size="40" maxlength="128" readonly="readonly"/>
					</div>
					<div>
						<label for="newname"><?php printf("%s&nbsp;", _("New Name:")); ?></label>
						<input id="newname" type="text" name="newname" value="" size="40" maxlength="128" />
					</div>
					<div>
						<span class="inputRequire"><?php printf(_("There should not have %s in new name"), ".., /, \, *, ?, \", |, &amp;, &lt;, &gt;"); ?></span>
					</div>
				</div>
				<div id="divUpload">
					<div>
						<label for="uploadFile"><?php printf("%s&nbsp;", _("Select File:")); ?></label>
						<input id="uploadFile" type="file" name="uploadFile" size="20"/>
					</div>
					<div>
						<span class="inputRequire"><?php printf("%s%s", _("File cannot be larger than "), "2MB"); ?></span>
					</div>
				</div>
				<div class="rightAlign">
					<input type="submit" value="<?php echo _("OK"); ?>"/>
					<input type="button" value="<?php echo _("Cancel"); ?>" onclick="closeAjax()"/>
				</div>
			</form>
		</div>
		<div id="ajaxDelete">
			<div class="ajaxHeader">
				<span><?php echo _("Confirm"); ?></span>
				<a class="ajaxFuncClose" href="javascript:;">
					<img alt="Close" src="images/ajax-func-close.gif" border="0">
				</a>
			</div>
			<div id="deleteMsg">
				<p><?php echo _("Are you sure to delete these items?"); ?></p>
				<p class="rightAlign">
					<input type="button" value="<?php echo _("OK"); ?>" onclick="doDelete()"/>
					<input type="button" value="<?php echo _("Cancel"); ?>" onclick="closeAjax()"/>
				</p>
			</div>
		</div>
		<div id="ajaxWait">
			<div class="ajaxHeader">
				<span><?php echo _("Working..."); ?></span>
			</div>
			<div class="wating">
				<img alt="wating" src="images/loadingAnimation.gif" border="0">
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
		
		if($this->view_type == "largeicon")
		{
			// 大图标视图
?>
			<div id="largeiconView">
<?php 	
			$i = 0;
			foreach($items as $item)
			{
				$this->largeicon_view_item($item['item_path'],
											$item['a_href'],
											$item['name'],
											Utility::get_icon($item['type'], 32),
											$item['name'],
											$item['size_str'],
											$item['type_html'],
											$item['stat']['mtime']);
				$i++;
			}
			$this->mark_to_20($i);
?>
				<div class="clear"></div>
			</div>
<?php 
		}
		else
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
											Utility::get_icon($item['type'], 16),
											$item['name'],
											$item['size_str'],
											$item['type_html'],
											$item['stat']['mtime']);
				$i++;
			}
			$this->mark_to_20($i);
?>
            </ul>
<?php 
		}

	}
	
	/**
	 * 显示列表头
	 */
	private function display_header()
	{
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
			<span class="check"></span>
			<span class="icon">&nbsp;</span>
			<span class="name split">
				<a href="<?php echo $_SERVER['PHP_SELF'] . "?dir=" .
				$request_sub_dir . "&s=n" .
				"&o=" . $norder; ?>"><?php echo _("Name"); ?></a>
			</span>
			<span class="size split">
				<a href="<?php echo $_SERVER['PHP_SELF'] . "?dir=" .
				$request_sub_dir . "&s=s" .
				"&o=" . $sorder; ?>"><?php echo _("Size"); ?></a>
			</span>
			<span class="type split">
				<a href="<?php echo $_SERVER['PHP_SELF'] . "?dir=" .
				$request_sub_dir . "&s=t" .
				"&o=" . $torder; ?>"><?php echo _("Type"); ?></a>
			</span>
			<span class="mtime split">
				<a href="<?php echo $_SERVER['PHP_SELF'] . "?dir=" .
				$request_sub_dir . "&s=m" .
				"&o=" . $morder; ?>"><?php echo _("Modified Time"); ?></a>
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
			setSortArrow("<?php echo $javascript_call_arg; ?>", "<?php echo $order; ?>");
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
			$up = $_SERVER['PHP_SELF'] . "?";
			$up .= $this->query_str;
			$up .= ("&dir=" . $this->get_parent_dir($this->request_sub_dir));		
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
?>
			<li >
				<span class="check">
					<input class="inputCheck" type="checkbox" name="<?php echo $item_path; ?>" />
				</span>
				<a href="<?php echo $a_href; ?>" title="<?php echo $a_title; ?>" <?php echo $class; ?>>
					<span class="icon"><?php echo $img_html; ?></span>
					<span class="name"><?php echo str_replace(" ", "&nbsp;", $name); ?></span>
					<span class="size"><?php echo $size; ?></span>
					<span class="type"><?php echo $type; ?></span>
					<span class="mtime"><?php echo date("Y-n-j H:i", $mtime); ?></span>
				</a>
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
		$class = "";
		if(LIGHTBOX && $this->is_img_type($type))
			$class = 'class="lightboxImg"';
?>
			<div class="largeIconItem" >
				<div class="firstLine">
					<input class="inputCheck" type="checkbox" name="<?php echo $item_path; ?>" />
					<span class="type"><?php echo $type; ?></span>
				</div>
				<div class="imgLine">
					<a href="<?php echo $a_href; ?>" title="<?php echo $a_title; ?>" <?php echo $class; ?>>
						<?php echo $img_html; ?>
					</a>
				</div>
				<div class="infoLine">
					<a href="<?php echo $a_href; ?>" title="<?php echo $a_title; ?>" <?php echo $class; ?>>
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
	
}

?>