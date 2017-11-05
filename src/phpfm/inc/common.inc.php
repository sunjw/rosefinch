<?php

/**
 * 获得 $_POST 中的 query 参数的值
 * @param $param_name 参数名称
 * @return 参数值或""
 */
function post_query($param_name)
{
	$query = "";
	if(isset($_POST[$param_name]))
	{
		$query = $_POST[$param_name];
	}
	return $query;
}


/**
 * 获得 $_GET 中的 query 参数的值
 * @param $param_name 参数名称
 * @return 参数值或""
 */
function get_query($param_name)
{
	$query = "";
	if(isset($_GET[$param_name]))
	{
		$query = $_GET[$param_name];
	}
	return $query;
}

/**
 * 获得 COOKIE 中的 name 参数的值
 * @param $name 参数名称
 * @return 参数值或""
 */
function get_cookie($name)
{
	$value = "";
	if(isset($_COOKIE[$name]))
	{
		$value = $_COOKIE[$name];
	}
	return $value;
}

/**
 * 获得当前页面的 URI
 * @return URI 字符串
 */
function get_URI()
{
	if($_SERVER['QUERY_STRING'] != "")
		$uri = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
	else
		$uri = $_SERVER['PHP_SELF'];
		
	return $uri;
}

/**
 * 获得该系统的基路径，结尾有'/'
 * @return 基路径
 */
function get_base_dir()
{
	$current_file_path = dirname(__FILE__); // inc 文件夹的绝对路径
	$current_base_path = substr($current_file_path, 0, -3);
	if(substr($current_base_path, strlen($current_base_path) - 1, 1) != "\\" && 
		substr($current_base_path, strlen($current_base_path) - 1, 1) != "/")
	{
		$current_base_path .= "/";
	}
	return $current_base_path;
}

/**
 * 获得设置的编码方式
 * @return 编码方式字符串
 */
function get_encoding()
{
	return "UTF-8";
}

/**
 * 将指定字符串从 GB2312 转换成 UTF-8
 * @param $str 目标字符串
 * @return 转换后的 UTF-8 字符串
 */
function convert_gbtoutf8($str)
{
	return @iconv("GB2312", "UTF-8", $str);
}

/**
 * 将指定字符串从 UTF-8 转换成 GB2312
 * @param $str 目标字符串
 * @return 转换后的 GB2312 字符串
 */
function convert_utf8togb($str)
{
	return @iconv("UTF-8", "GB2312", $str);
}

/**
 * xcopy 拷贝目录
 * @param $src 源
 * @param $dest 目标
 * @return false 失败, true 完成
 */
function xcopy($src, $dest)
{
	if(!$dh = @opendir($src))
		return false;
	
	if(!is_dir($dest))
		if(!@mkdir($dest))
			return false;
	
	while(false !== ($item = readdir($dh)))
	{
		if($item != '.' && $item != '..')
		{
			$src_folder_content = $src. '/' .$item;
			$dest_folder_content = $dest. '/' .$item;
			
			if(is_file($src_folder_content))
				@copy($src_folder_content, $dest_folder_content);
			elseif(is_dir($src_folder_content))
				xcopy($src_folder_content, $dest_folder_content);
		}
	}
		
	closedir($dh);
	return true;
}

/**
 * 在文件路径中获得文件名
 * 取代 php 自带的 basename()，因为 basename() 在部分平台处理中文有问题
 * @param $filename
 * @return basename
 */
function get_basename($filename)
{
	return preg_replace('/^.+[\\\\\\/]/', '', $filename);
}

/**
 * 跳转至指定 url
 * @param $url url
 * @param $need_rawurldecode 是否要进行 rawurldecode，默认 false
 */
function redirect($url, $need_rawurldecode = false)
{
	if($need_rawurldecode)
		$url = rawurldecode($url);
	
	header("Location: " . $url);
	exit;
}

/**
 * 在 header 中设置编码 UTF-8
 */
function set_response_utf8()
{
	header("Content-Type: text/html; charset=UTF-8");
}

/**
 * 将时间字符串转换为 timestamp
 * @param $str 时间字符串 格式为 Y-n-j H:i:s
 * @return int timestamp
 */
function timestrtotime($str)
{
	$array = explode(" ", $str);
	$date = $array[0];
	$time = $array[1];
	
	$date = explode("-", $date);
	$time = explode(":", $time);
	
	$timestamp = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
	
	return $timestamp;
}

/**
 * htmlentities 的 UTF-8 编码版本
 * @param $str 需要处理的字符串
 * @return string 结果
 */
function htmlentities_utf8($str)
{
	return htmlentities($str, ENT_COMPAT, "UTF-8");
}

/**
 * 将字符串最后的斜杠或反斜杠删掉
 * @param $str 需要处理的字符串
 * @return string 结果
 */
function erase_last_slash($str)
{
	$temp = $str;
	if(mb_substr($str, -1) == "/" || mb_substr($str, -1) == "\\" )
		$temp = mb_substr($str, 0, mb_strlen($str) - 1);
		
	return $temp;
}

/**
 * 检测是否是移动浏览器访问
 * @return false, true
 */
function is_mobile_browser()
{
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)))
		return true;
	return false;
}

?>