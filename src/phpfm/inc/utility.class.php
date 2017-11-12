<?php
require_once "defines.inc.php";
require_once "common.inc.php";
require_once "clipboard.class.php";
require_once "messageboard.class.php";
require_once "history.class.php";
require_once "ez_sql_core.php"; // Include ezSQL core
require_once "ez_sql_mysql.php"; // Include ezSQL database specific component
require_once "usermng.class.php";

/**
 * Utility Class
 * 2009-9-11
 * @author Sun Junwen
 *
 */
class Utility
{
	/**
	 * 获得储存文件目录的基路径
	 * @return 储存文件目录的基路径
	 */
	public static function get_file_base_dir()
	{
		if(FILE_POSITION == "relative")
		{
			$base_dir = get_base_dir();
			$files_base_dir = $base_dir . FILES_DIR . "/";
			return $files_base_dir;
		}
		else if(FILE_POSITION == "absolute")
		{
			$files_base_dir = FILES_DIR . "/";
			return $files_base_dir;
		}
	}
	
	/**
	 * 格式化大小字符串
	 * @param $size 以字节计的大小
	 * @return 格式化的字符串
	 */
	public static function format_size($size)
	{
		if($size > 1024)
		{
			$size /= 1024.0;
			if($size > 1024)
			{
				$size /= 1024.0;
				$size = round($size, 2);
				$size .= "MB";
			}
			else
			{
				$size = round($size, 2);
				$size .= "KB";
			}
		}
		else
		{
			$size .= "B";
		}
		
		return $size;
	}
	
	/**
	 * 获得文件扩展名
	 * @param $file 文件名或路径
	 * @return 文件扩展名 (不包括'.')
	 */
	public static function get_file_ext($file)
	{
		$dot_pos = strrpos($file, ".");
		$type = "";
		if($dot_pos !== false)
		{
			$type = substr($file, $dot_pos + 1, strlen($file) - $dot_pos - 1);
		}
		return $type;
	}
	
	/**
	 * 检查文件名是否符合要求
	 * @param $name
	 * @return 符合要求 true, 不符合 false
	 */
	public static function check_name($name)
	{
		if($name == "")
		{
			return false;
		}
		if(false !== strpos($name, "..") ||
			false !== strpos($name, "/") || 
			false !== strpos($name, "\\") ||
			false !== strpos($name, "*") ||
			false !== strpos($name, "?") ||
			false !== strpos($name, "\"") ||
			false !== strpos($name, "|") ||
			false !== strpos($name, "&") ||
			false !== strpos($name, ">") ||
			false !== strpos($name, "<"))
		{
			return false;		
		}
		
		return true;
	}
	
	/**
	 * 检查路径是否符合要求
	 * @param $path
	 * @return 符合要求 true, 不符合 false
	 */
	public static function check_path($path)
	{
		if($path == "")
		{
			return false;
		}
		if(false !== strpos($path, "..") ||
			false !== strpos($path, "*") ||
			false !== strpos($path, "?") ||
			false !== strpos($path, "\"") ||
			false !== strpos($path, "|") ||
			false !== strpos($path, "&") ||
			false !== strpos($path, ">") ||
			false !== strpos($path, "<"))
		{
			return false;		
		}
		
		return true;
	}
	
	/**
	 * 根据文件扩展名生成图标图片的 html 代码
	 * @param $file_type 文件扩展名
	 * @return 图标图片的 html 代码
	 */
	public static function get_icon($file_type, $size = 16)
	{
		$img_ext = ".png";
		$file_type = strtolower($file_type);
		$img_postfix = "_32";
		switch ($file_type)
		{
		case "文件夹":
		case "folder":	
		case "dir":
			return Utility::generate_img_html("images/folder".$img_postfix.$img_ext, $size, $size, "zip");
		case "zip":
		case "rar":
		case "tar":
		case "bz":
		case "bz2":
		case "gz":
			return Utility::generate_img_html("images/compressed".$img_postfix.$img_ext, $size, $size, "zip");
		case "exe":
		case "com":
			return Utility::generate_img_html("images/binary".$img_postfix.$img_ext, $size, $size, "app");
		case "mp3":
		case "wma":
			return Utility::generate_img_html("images/music".$img_postfix.$img_ext, $size, $size, "music");
		case "html":
		case "htm":
			return Utility::generate_img_html("images/html".$img_postfix.$img_ext, $size, $size, "html");
		case "jpg":
		case "jpeg":
		case "bmp":
		case "png":
		case "gif":
			return Utility::generate_img_html("images/image".$img_postfix.$img_ext, $size, $size, "image");
		default:
			return Utility::generate_img_html("images/generic".$img_postfix.$img_ext, $size, $size, "file");
		}
	}
	
	/**
	 * 生成 <img/> 的 html 代码
	 * @param $src 图片路径
	 * @param $width width
	 * @param $height height
	 * @param $alt alt 字符串
	 * @return 图标图片的 html 代码
	 */
	private static function generate_img_html($src, $width, $height, $alt)
	{
		$imagehtml = '<img src="' . $src . '" alt="' . $alt . '" width="' . $width . '" height="' . $height . '" border="0" />';
		return $imagehtml;
	}
	
	/**
	 * 根据文件后缀名获得 MIME 类型
	 * @param $file_extension 文件后缀名
	 * @return MIME 类型
	 */
	public static function get_mime_type($file_extension)
	{
		$mimetypes = array(
			"ez" => "application/andrew-inset",
			"hqx" => "application/mac-binhex40",
			"cpt" => "application/mac-compactpro",
			"doc" => "application/msword",
			"bin" => "application/octet-stream",
			"dms" => "application/octet-stream",
			"lha" => "application/octet-stream",
			"lzh" => "application/octet-stream",
			"exe" => "application/octet-stream",
			"class" => "application/octet-stream",
			"so" => "application/octet-stream",
			"dll" => "application/octet-stream",
			"oda" => "application/oda",
			"pdf" => "application/pdf",
			"ai" => "application/postscript",
			"eps" => "application/postscript",
			"ps" => "application/postscript",
			"smi" => "application/smil",
			"smil" => "application/smil",
			"wbxml" => "application/vnd.wap.wbxml",
			"wmlc" => "application/vnd.wap.wmlc",
			"wmlsc" => "application/vnd.wap.wmlscriptc",
			"bcpio" => "application/x-bcpio",
			"vcd" => "application/x-cdlink",
			"pgn" => "application/x-chess-pgn",
			"cpio" => "application/x-cpio",
			"csh" => "application/x-csh",
			"dcr" => "application/x-director",
			"dir" => "application/x-director",
			"dxr" => "application/x-director",
			"dvi" => "application/x-dvi",
			"spl" => "application/x-futuresplash",
			"gtar" => "application/x-gtar",
			"hdf" => "application/x-hdf",
			"js" => "application/x-javascript",
			"skp" => "application/x-koan",
			"skd" => "application/x-koan",
			"skt" => "application/x-koan",
			"skm" => "application/x-koan",
			"latex" => "application/x-latex",
			"nc" => "application/x-netcdf",
			"cdf" => "application/x-netcdf",
			"sh" => "application/x-sh",
			"shar" => "application/x-shar",
			"swf" => "application/x-shockwave-flash",
			"sit" => "application/x-stuffit",
			"sv4cpio" => "application/x-sv4cpio",
			"sv4crc" => "application/x-sv4crc",
			"tar" => "application/x-tar",
			"tcl" => "application/x-tcl",
			"tex" => "application/x-tex",
			"texinfo" => "application/x-texinfo",
			"texi" => "application/x-texinfo",
			"t" => "application/x-troff",
			"tr" => "application/x-troff",
			"roff" => "application/x-troff",
			"man" => "application/x-troff-man",
			"me" => "application/x-troff-me",
			"ms" => "application/x-troff-ms",
			"ustar" => "application/x-ustar",
			"src" => "application/x-wais-source",
			"xhtml" => "application/xhtml+xml",
			"xht" => "application/xhtml+xml",
			"zip" => "application/zip",
			"au" => "audio/basic",
			"snd" => "audio/basic",
			"mid" => "audio/midi",
			"midi" => "audio/midi",
			"kar" => "audio/midi",
			"mpga" => "audio/mpeg",
			"mp2" => "audio/mpeg",
			"mp3" => "audio/mpeg",
			"aif" => "audio/x-aiff",
			"aiff" => "audio/x-aiff",
			"aifc" => "audio/x-aiff",
			"m3u" => "audio/x-mpegurl",
			"ram" => "audio/x-pn-realaudio",
			"rm" => "audio/x-pn-realaudio",
			"rpm" => "audio/x-pn-realaudio-plugin",
			"ra" => "audio/x-realaudio",
			"wav" => "audio/x-wav",
			"pdb" => "chemical/x-pdb",
			"xyz" => "chemical/x-xyz",
			"bmp" => "image/bmp",
			"gif" => "image/gif",
			"ief" => "image/ief",
			"jpe" => "image/jpeg",
			"jpeg" => "image/jpeg",
			"jpg" => "image/jpeg",
			"png" => "image/png",
			"tiff" => "image/tiff",
			"tif" => "image/tif",
			"djvu" => "image/vnd.djvu",
			"djv" => "image/vnd.djvu",
			"wbmp" => "image/vnd.wap.wbmp",
			"ras" => "image/x-cmu-raster",
			"pnm" => "image/x-portable-anymap",
			"pbm" => "image/x-portable-bitmap",
			"pgm" => "image/x-portable-graymap",
			"ppm" => "image/x-portable-pixmap",
			"rgb" => "image/x-rgb",
			"xbm" => "image/x-xbitmap",
			"xpm" => "image/x-xpixmap",
			"xwd" => "image/x-windowdump",
			"igs" => "model/iges",
			"iges" => "model/iges",
			"msh" => "model/mesh",
			"mesh" => "model/mesh",
			"silo" => "model/mesh",
			"wrl" => "model/vrml",
			"vrml" => "model/vrml",
			"css" => "text/css",
			"html" => "text/html",
			"htm" => "text/html",
			"asc" => "text/plain",
			"txt" => "text/plain",
			"rtx" => "text/richtext",
			"rtf" => "text/rtf",
			"sgml" => "text/sgml",
			"sgm" => "text/sgml",
			"tsv" => "text/tab-seperated-values",
			"wml" => "text/vnd.wap.wml",
			"wmls" => "text/vnd.wap.wmlscript",
			"etx" => "text/x-setext",
			"xml" => "text/xml",
			"xsl" => "text/xml",
			"mpeg" => "video/mpeg",
			"mpg" => "video/mpeg",
			"mpe" => "video/mpeg",
			"qt" => "video/quicktime",
			"mov" => "video/quicktime",
			"mxu" => "video/vnd.mpegurl",
			"avi" => "video/x-msvideo",
			"movie" => "video/x-sgi-movie",
			"ice" => "x-conference-xcooltalk",
			"wmv"=>"video/x-ms-wmv",
			"wma"=>"audio/x-ms-wma",
			"asf"=>"video/x-msvideo"
		);
		
		if(isset($mimetypes[$file_extension]))
			$type = $mimetypes[$file_extension];
		else
			$type = 'application/force-download';
		

		return $type;
	}
	
	/**
	 * 过滤文件名
	 * 去掉各种非法字符
	 * @param $files 文件名数组
	 * @return 过滤过的文件名数组
	 */
	public static function filter_files($files)
	{
		$new_files = Array();
		$count = count($files);
		for($i = 0; $i < $count; $i++)
		{
			$file = $files[$i];
			if(Utility::check_name($file))
			{
				array_push($new_files, $file);
			}
		}
		return $new_files;
	}
	
	/**
	 * 过滤路径
	 * 去掉各种非法字符
	 * @param $paths 路径数组
	 * @return 过滤过的路径数组
	 */
	public static function filter_paths($paths)
	{
		$new_paths = Array();
		$count = count($paths);
		for($i = 0; $i < $count; $i++)
		{
			$path = $paths[$i];
			if(Utility::check_path($path))
			{
				array_push($new_paths, $path);
			}
		}
		return $new_paths;
	}
	
	private static function get_name_part($name_part)
	{
		$suffix_len = 0;
		$suffix = substr($name_part, -1);
		if(strcmp($suffix, ")") == 0)
		{
			$suffix_len = 1;
			while(1)
			{
				$suffix = substr($name_part, -($suffix_len + 1), -1);
				if(!is_numeric($suffix))
				{
					break;
				}
				++$suffix_len;
			}
			$suffix = substr($name_part, -($suffix_len + 1), 1);
			if(strcmp($suffix, "(") == 0)
			{
				++$suffix_len;
			}
			else
			{
				$suffix_len = 0;
			}
		}
		if($suffix_len > 0)
		{
			$name_part = substr($name_part, 0, -($suffix_len));
		}
			
		return $name_part;
	}
	
	/**
	 * 处理已有名称
	 * @param $name 完整路径 (UTF-8)
	 * @return 新完整路径 (UTF-8)
	 */
	public static function deal_same_name($name, $i = 2)
	{
		$file_name = get_basename($name);
		$dir_name = dirname($name);
		$dot_pos = strrpos($file_name, ".");
		
		$newname = "";
		
		$name_part = "";
		$type_part = "";
		if($dot_pos !== false)
		{
			$name_part = substr($file_name, 0, $dot_pos);
			$type_part = substr($file_name, $dot_pos + 1, strlen($file_name) - $dot_pos - 1);
			
			$name_part = Utility::get_name_part($name_part);
			
			$name_part .= "(".$i.")";
			$newname = $name_part.".".$type_part;
		}
		else
		{
			$name_part = Utility::get_name_part($file_name);
			$newname = $name_part."(".$i.")";
		}
		
		$newname = $dir_name."/".$newname;
		if(file_exists(convert_toplat($newname)))
		{
			$newname = Utility::deal_same_name($newname, $i + 1);
		}
		
		return $newname;
	}
	
	/**
	 * 重名的 rename
	 * @param $oldname 原路径 (UTF-8)
	 * @param $newname 新路径 (UTF-8)
	 * @param $deal_same_name 是否处理重名, 默认不处理
	 * @return 完成 TRUE, 失败 FALSE
	 */
	public static function phpfm_rename($oldname, $newname, $deal_same_name = false)
	{
		$newname_dir_part = dirname($newname);
		if($newname_dir_part == $oldname)
			return FALSE;
		
		$plat_oldname = convert_toplat($oldname);
		$plat_newname = convert_toplat($newname);
		if($plat_oldname == $plat_newname)
			return TRUE;
		
		// 处理文件重名
		if(file_exists($plat_oldname))
		{
			if(file_exists($plat_newname))
			{
				if($deal_same_name)
					$newname = Utility::deal_same_name($newname);
				else
					return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	
		$plat_newname = convert_toplat($newname);
		return @rename($plat_oldname, $plat_newname);
	}
	
	/**
	 * 处理过重名的 copy
	 * 可以拷贝目录
	 * @param $oldname 原路径 (UTF-8)
	 * @param $newname 新路径 (UTF-8)
	 * @return 完成 TRUE, 失败 FALSE
	 */
	public static function phpfm_copy($oldname, $newname)
	{
		$newname_dir_part = dirname($newname);
		if($newname_dir_part == $oldname)
			return FALSE;
			
		$plat_oldname = convert_toplat($oldname);
		$plat_newname = convert_toplat($newname);
		
		if(file_exists($plat_newname))
		{
			$newname = Utility::deal_same_name($newname);
		}
		$plat_newname = convert_toplat($newname);
		
		if(is_dir($plat_oldname))
		{
			return xcopy($plat_oldname, $plat_newname); // 目录使用 xcopy 
		}
		else
		{
			return @copy($plat_oldname, $plat_newname);
		}
	}
	
	/**
	 * 处理过删除文件夹
	 * 可以直接删除文件夹
	 * @param $path
	 * @return bool 完成 TRUE, 失败 FALSE
	 */
	public static function phpfm_rmdir($path)
	{
		if(!$dh = @opendir($path))
			return FALSE;
		
		$success = TRUE;
		while(false !== ($item = readdir($dh)))
		{
			if($item != '.' && $item != '..')
			{
				$folder_content = $path. '/' .$item;
				
				if(is_file($folder_content))
				{
					$success = $success && @unlink($folder_content);
				}
				elseif(is_dir($folder_content))
				{
					$success = $success && Utility::phpfm_rmdir($folder_content);
				}
			}
		}
		closedir($dh);
		
		$success = $success && @rmdir($path);
		
		return $success;
	}
	
	/**
	 * 处理过重名的 move_uploaded_file
	 * @param $filename (UTF-8)
	 * @param $destination (UTF-8)
	 * @return 与 move_uploaded_file 相同
	 */
	public static function phpfm_move_uploaded_file($filename, $destination)
	{
		$plat_destination = convert_toplat($destination);
		if(file_exists($plat_destination))
		{
			$plat_destination = convert_toplat(Utility::deal_same_name($destination));
		}
		
		return move_uploaded_file(convert_toplat($filename), $plat_destination);
	}
	
	/**
	 * 功能完成后跳转
	 * 读取请求的 <strong>return</strong> 参数值，rawurldecode 后，跳转
	 * @param $is_from_get return 参数是否来自 get，默认 true
	 * @param $sub_dir_level 子文件夹层数，默认0
	 */
	public static function redirct_after_oper($is_from_get = true, $sub_dir_level = 0)
	{
		if(post_query("noredirect") != "")
			return;
		if($is_from_get)
			$returnURL = rawurldecode(get_query("return"));
		else
			$returnURL = rawurldecode(post_query("return"));
	
		if($returnURL == "")
		{
			$prefix = "";
			for($i = 0; $i < $sub_dir_level; $i++)
			{
				$prefix .= "../";
			}
			$returnURL = $prefix . "index.php";
		}
		
		redirect($returnURL);
	}
	
	/**
	 * 从 SESSION 中获得当前 MessageBoard 对象，并存入 SESSION
	 * @param $need_new 不存在是否要新建，默认 true
	 * @return MessageBoard 对象或 null
	 */
	public static function get_messageboard($need_new = true)
	{
		if($need_new)
		{
			$messageboard = isset($_SESSION['messageboard']) ? $_SESSION['messageboard'] : new MessageBoard();
			$_SESSION['messageboard'] = $messageboard; // 将消息板存入 SESSION
		}
		else
		{
			$messageboard = isset($_SESSION['messageboard']) ? $_SESSION['messageboard'] : null;
		}
		
		return $messageboard;
	}
	
	/**
	 * 从 SESSION 中获得当前 ClipBoard 对象，并存入 SESSION
	 * @param $need_new 不存在是否要新建，默认 true
	 * @return ClipBoard 对象或 null
	 */
	public static function get_clipboard($need_new = true)
	{
		if($need_new)
		{
			$clipboard = isset($_SESSION['clipboard']) ? $_SESSION['clipboard'] : new ClipBoard();
			$_SESSION['clipboard'] = $clipboard; // 将剪贴板存入 SESSION
		}
		else
		{
			$clipboard = isset($_SESSION['clipboard']) ? $_SESSION['clipboard'] : null;
		}
		
		return $clipboard;
	}
	
	/**
	 * 获得数据库连接
	 * @return ezSQL_mysql 对象
	 */
	public static function get_ezMysql()
	{
		// Initialise database object and establish a connection
		// at the same time - db_user / db_password / db_name / db_host
		if(!defined('DB_USER') || !defined('DB_PSWD') || !defined('DB_NAME') ||!defined('DB_HOST'))
			return null;
		$db = new ezSQL_mysql(DB_USER, DB_PSWD, DB_NAME, DB_HOST);
		
		$db->hide_errors();
		
		$ret = $db->query("set names 'utf8'");
		if((!is_numeric($ret) && !$ret))
		{
			return null;
		}
		
		return $db;
	}
	
	/**
	 * 从 SESSION 中获得当前 History 对象，并存入 SESSION
	 * @param $need_new 不存在是否要新建，默认 true
	 * @return History 对象或 null
	 */
	public static function get_history($need_new = true)
	{
		if($need_new)
		{
			$history = isset($_SESSION['history']) ? $_SESSION['history'] : new History();
			$_SESSION['history'] = $history; // 将剪贴板存入 SESSION
		}
		else
		{
			$history = isset($_SESSION['history']) ? $_SESSION['history'] : null;
		}
		
		return $history;
	}
	
	/**
	 * 从 SESSION 中获得当前 UserManager 对象，并存入 SESSION
	 * @param $need_new 不存在是否要新建，默认 true
	 * @return UserManager 对象或 null
	 */
	public static function get_usermng($need_new = true)
	{
		if($need_new)
		{
			$usermgn = isset($_SESSION['usermgn']) ? $_SESSION['usermgn'] : new UserManager();
			$_SESSION['usermgn'] = $usermgn; // 将剪贴板存入 SESSION
		}
		else
		{
			$usermgn = isset($_SESSION['usermgn']) ? $_SESSION['usermgn'] : null;
		}
		
		return $usermgn;
	}
	
	private static function allow_to($do)
	{
		if(!USERMNG)
			return true;
		
		$modify_permission = $do;
		if($modify_permission == User::$NOBODY)
			return true;
			
		$user_manager = Utility::get_usermng();
		if(!$user_manager->is_logged())
			return false;
		
		$user = $user_manager->get_user();
		if($user->permission >= $modify_permission)
			return true;
		
		return false;
	}
	
	/**
	 * 检查当前用户是否可以浏览文件
	 * @return true, 可以；false, 不行
	 */
	public static function allow_to_browser()
	{
		if(!defined("ROSE_BROWSER"))
			return true;
		return Utility::allow_to(ROSE_BROWSER);
	}
	
	/**
	 * 检查当前用户是否可以修改文件
	 * @return true, 可以；false, 不行
	 */
	public static function allow_to_modify()
	{
		if(!defined("ROSE_MODIFY"))
			return true;
		return Utility::allow_to(ROSE_MODIFY);
	}
	
	/**
	 * 检查当前用户是否可以管理
	 * @return true, 可以；false, 不行
	 */
	public static function allow_to_admin()
	{
		if(!defined("ROSE_ADMIN"))
			return true;
		return Utility::allow_to(ROSE_ADMIN);
	}
	
	/**
	 * 显示用户状态
	 */
	public static function display_user()
	{
		if(is_mobile_browser() || !USERMNG)
			return '';
		$user = Utility::get_usermng()->get_user();
		if($user == null)
		{
			echo _("Welcome").' '._("Guest").'&nbsp;|&nbsp;<a id="linkLogin" href="javascript:;">'._("Login").'</a>';
		}
		else
		{
			echo _("Welcome").' '.$user->name.'&nbsp;|&nbsp;<a id="linkLogout" href="javascript:;">'._("Logout").'</a>';;
		}
	}
	
	/**
	 * 显示导航栏
	 */
	public static function html_navigation($page = "index")
	{
		if(!is_mobile_browser())
		{
			if($page == "setting")
			{
			?>
			<ul>
				<li class="li-item"><a href="../"><?php echo htmlentities_utf8(_(TITLENAME)); ?></a></li>
				<li class="li-item current"><a href="setting.php"><?php echo _("Setting"); ?></a></li>
				<li class="li-item"><a href="../help.php"><?php echo _("Help"); ?></a></li>
				<li class="li-item"><a href="../about.php"><?php echo _("About"); ?></a></li>
			</ul>
			<?php 
			}
			else
			{
			?>
			<ul>
				<li class="li-item<?php $page == "index" ? print(" current") : print(""); ?>"><a href="index.php"><?php echo htmlentities_utf8(_(TITLENAME)); ?></a></li>
				<li class="li-item<?php $page == "setting" ? print(" current") : print(""); ?>"><a href="admin/setting.php"><?php echo _("Setting"); ?></a></li>
				<li class="li-item<?php $page == "help" ? print(" current") : print(""); ?>"><a href="help.php"><?php echo _("Help"); ?></a></li>
				<li class="li-item<?php $page == "about" ? print(" current") : print(""); ?>"><a href="about.php"><?php echo _("About"); ?></a></li>
			</ul>
			<?php 
			}
		}
		else
		{
			?>
			<ul>
				<li class="li-item<?php $page == "index" ? print(" current") : print(""); ?>"><a href="index.php"><?php echo htmlentities_utf8(_(TITLENAME)); ?></a></li>
			</ul>
			<?php 
		}
	}
	
	/**
	 * 版权信息
	 */
	public static function html_copyright_info($begin_time)
	{
?>
		<div id="copyright">
<?php
		if(!is_mobile_browser())
		{
?>
			<div><?php echo _("Notice: Rosefinch only supports IE 7 or newer versions and other modern browser"); ?>&nbsp;|&nbsp;<?php echo _("Generating time"); ?>&nbsp;<?php echo (microtime(true) - $begin_time) . "s"; ?></div>
<?php
		}
?>
			<div><?php printf("Rosefinch - %s - PHPFM %s&nbsp;|&nbsp;SUN Junwen&nbsp;|&nbsp;<a target=\"_blank\" href=\"https://material.io/icons/\">%s</a>", _("Rosefinch"), VERSION, _("Using Google Material icons")); ?></div>
		</div>
<?php
	}
	
}

?>