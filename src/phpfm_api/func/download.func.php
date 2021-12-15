<?php
require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../clazz/utility.class.php';
require_once dirname(__FILE__) . '/../log/log.func.php';

@session_start();

/**
 * Write file to HTTP client.
 * @param string $file file path
 */
function dl_file($file) {
    // Gather relevant info about file.
    $size = filesize($file);
    $fileinfo['basename'] = get_basename($file);

    $fileinfo['extension'] = Utility::get_file_ext($file);
    // Workaround for IE filename bug with multiple periods / multiple dots in filename
    // that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe.
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $filename = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ?
            preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) :
            $fileinfo['basename'];
    } else {
        $filename = $fileinfo['basename'];
    }

    $file_extension = strtolower($fileinfo['extension']);

//    switch($file_extension)
//    {
//        case 'exe': $ctype='application/octet-stream'; break;
//        case 'zip': $ctype='application/zip'; break;
//        case 'mp3': $ctype='audio/mpeg'; break;
//        case 'mpg': $ctype='video/mpeg'; break;
//        case 'avi': $ctype='video/x-msvideo'; break;
//        default: $ctype='application/force-download';
//    }
    $ctype = Utility::get_mime_type($file_extension);
    //get_logger()->info($ctype);
    //echo $ctype;
    // Check if http_range is sent by browser (or download manager).

    $range = '';
    if (isset($_SERVER['HTTP_RANGE'])) {
        list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

        if ($size_unit == 'bytes') {
            // Multiple ranges could be specified at the same time, but for simplicity only serve the first range.
            // http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
            //list($range, $extra_ranges) = explode(',', $range_orig, 2);
            $ranges = explode(',', $range_orig, 2);
            $range = $ranges[0];
        } else {
            $range = '';
        }
    } else {
        $range = '';
    }

    // Figure out download piece from range (if set).
    if ($range != '') {
        list($seek_start, $seek_end) = explode('-', $range, 2);
    }

    // Set start and end based on range (if set), else set defaults
    // also check for invalid ranges.
    $seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)), ($size - 1));
    $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)), 0);

    // Add headers if resumable.
    //if ($is_resume)
    //{
    // Only send partial content header if downloading a piece of the file (IE workaround).
    if ($seek_start > 0 || $seek_end < ($size - 1)) {
        header('HTTP/1.1 206 Partial Content');
    }

    header('Accept-Ranges: bytes');
    header('Content-Range: bytes ' . $seek_start . '-' . $seek_end . '/' . $size);
    //}

    //headers for IE Bugs (is this necessary?)
    //header('Cache-Control: cache, must-revalidate');
    //header('Pragma: public');

    header('Content-Type: ' . $ctype);
    header('Content-Disposition: attachment; filename="' . convert_toutf8($filename) . '"');
    header('Content-Length: ' . ($seek_end - $seek_start + 1));

    // Open the file.
    $fp = fopen($file, 'rb');
    // Seek to start of missing part
    fseek($fp, $seek_start);

    // Start buffered download
    while (!feof($fp)) {
        //reset time limit for big files
        set_time_limit(0);
        print(fread($fp, 1024 * 8));
        flush();
        ob_flush();
    }

    fclose($fp);
    exit;

}

/**
 * Prepare download path, detect request encoding.
 * @param string $request_file request file path
 * @return string platform file path or false
 */
function prepare_file_path($request_file) {
    $files_base_dir = Utility::get_file_base_dir();
    $files_base_dir_plat = convert_toplat($files_base_dir);
    $file = $files_base_dir_plat . $request_file; // Get file path.
    if (PLAT_CHARSET != 'UTF-8') {
        //get_logger()->info('file1:$file');
        if (!is_file($file)) {
            // Not exists, try local encoding.
            $file = $files_base_dir_plat . convert_toplat($request_file); // On Windows, may need convert to GB2312.
            //get_logger()->info('file2:$file');
            if (!is_file($file)) {
                $file = false; // Not exists.
            }
        } else {
            // Exists, may need convert to UTF-8.
            //$request_sub_dir = convert_gbtoutf8($request_sub_dir);
        }
    } else if (PLAT_CHARSET == 'UTF-8') {
        if (!is_file($file)) {
            // Not exists, try UTF-8.
            $file = $files_base_dir_plat . convert_gbtoutf8($request_file); // On Linux, may need convert to UTF-8.
            if (!is_file($file)) {
                $file = false; // Not exists.
                //$request_sub_dir = '';
            }
        } else {
            // Exists, UTF-8, do nothing.
        }
    }

    return $file;
}

if (Utility::allow_to_view()) {
    // Need view permission.
    $request_file = rawurldecode(get_query('file'));
    get_logger()->info(join($_GET));

    if (substr($request_file, -1) == '"') {
        $request_file = substr($request_file, 0, -1);
    }

    if ($request_file != false) {
        $test_array[0] = $request_file;
        $test_array = Utility::filter_paths($test_array);
        if (count($test_array) > 0) {
            $request_file = prepare_file_path($request_file);
            get_logger()->info('Download start');
            dl_file($request_file);
        }
    } else {
        get_logger()->info('Download fail');
        response_404();
    }
}
//

?>
