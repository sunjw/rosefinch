<?php
require_once "../inc/defines.inc.php";
require_once "../inc/common.inc.php";
require_once "../inc/utility.class.php";
require_once "../log/log.func.php";

@session_start();

/**
 * 向客户端写文件以提供下载
 * @param $file
 */
function dl_file($file)
{
    //Gather relevent info about file
    $size = filesize($file);
    $fileinfo['basename'] = get_basename($file);

	$fileinfo['extension'] = Utility::get_file_ext($file);
    //workaround for IE filename bug with multiple periods / multiple dots in filename
    //that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
    $filename = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ?
                  preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) :
                  $fileinfo['basename'];
   
    $file_extension = strtolower($fileinfo['extension']);

//    switch($file_extension)
//    {
//        case 'exe': $ctype='application/octet-stream'; break;
//        case 'zip': $ctype='application/zip'; break;
//        case 'mp3': $ctype='audio/mpeg'; break;
//        case 'mpg': $ctype='video/mpeg'; break;
//        case 'avi': $ctype='video/x-msvideo'; break;
//        default:    $ctype='application/force-download';
//    }
	$ctype = Utility::get_mime_type($file_extension);
	//log_to_file($ctype);
	//echo $ctype;
    //check if http_range is sent by browser (or download manager)
    
	$range = '';
    if(isset($_SERVER['HTTP_RANGE']))
    {
        list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
		
        if ($size_unit == 'bytes')
        {
            //multiple ranges could be specified at the same time, but for simplicity only serve the first range
            //http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
            list($range, $extra_ranges) = explode(',', $range_orig, 2);
        }
        else
        {
            $range = '';
        }
    }
    else
    {
        $range = '';
    }

    //figure out download piece from range (if set)
	if($range != '')
		list($seek_start, $seek_end) = explode('-', $range, 2);

    //set start and end based on range (if set), else set defaults
    //also check for invalid ranges.
    $seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));
    $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

    //add headers if resumable
    //if ($is_resume)
    //{
        //Only send partial content header if downloading a piece of the file (IE workaround)
    if ($seek_start > 0 || $seek_end < ($size - 1))
    {
        header('HTTP/1.1 206 Partial Content');
    }

    header('Accept-Ranges: bytes');
    header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
    //}

    //headers for IE Bugs (is this necessary?)
    //header("Cache-Control: cache, must-revalidate");  
    //header("Pragma: public");

    header('Content-Type: ' . $ctype);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: '.($seek_end - $seek_start + 1));

    //open the file
    $fp = fopen($file, 'rb');
    //seek to start of missing part
    fseek($fp, $seek_start);

    //start buffered download
    while(!feof($fp))
    {
        //reset time limit for big files
        set_time_limit(0);
        print(fread($fp, 1024*8));
        flush();
        ob_flush();
    }

    fclose($fp);
    exit;
    
}

/**
 * 准备下载路径
 * 对字符串编码方式进行判断
 * @param $request_file
 * @return 正确的文件路径或 false
 */
function prepare_file_path($request_file)
{
	$files_base_dir = Utility::get_file_base_dir();
	$files_base_dir_plat = convert_toplat($files_base_dir);
	$file = $files_base_dir_plat . $request_file; // 获得文件路径
	if(PLAT_CHARSET != "UTF-8")
	{
		//log_to_file("file1:$file");
		if(!is_file($file))
		{
			// 不存在，试试转化成本地编码
			$file = $files_base_dir_plat . convert_toplat($request_file); // Windows 上可能要转换成 gb2312
			//log_to_file("file2:$file");
			if(!is_file($file))
			{
				$file = false; // 没有这个文件
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
		if(!is_file($file))
		{
			// 不存在，试试转化成 UTF-8  编码
			$file = $files_base_dir_plat . convert_gbtoutf8($request_file); // Linux 上可能要转换成 utf-8
			if(!is_file($file))
			{
				$file = false; // 没有这个文件
				//$request_sub_dir = "";
			}
		}
		else
		{
			// 存在说明就是 utf-8 编码的，什么都不用做
		}
	}
	
	return $file;
}

$request_file = rawurldecode(get_query("file"));
log_to_file(join($_GET));

if(substr($request_file, -1) == "\"")
{
	$request_file = substr($request_file, 0, -1);
}
$request_file = prepare_file_path($request_file);

if($request_file != false)
{
	$test_array[0] = $request_file;
	$test_array = Utility::filter_paths($test_array);
	if(count($test_array) > 0)
	{
		log_to_file("Download start");
		dl_file($request_file);
	}
}
else
{
	log_to_file("Download fail");
	header("HTTP/1.1 404 Not Found");
	echo "<strong>404 Not Found</strong>";
    exit;
}
//

?>