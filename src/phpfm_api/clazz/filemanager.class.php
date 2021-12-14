<?php

require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once dirname(__FILE__) . '/../inc/gettext.inc.php';
require_once dirname(__FILE__) . '/../inc/sort.inc.php';
require_once 'clipboard.class.php';
require_once 'messageboard.class.php';
require_once 'utility.class.php';

@session_start();

/**
 * File Manager Class.
 * 2009-8-4
 * @author Sun Junwen
 *
 */
class FileManager {
    private $request_sub_dir; // request sub directory path, UTF-8.
    private $request_dir; // request directory absolute path.
    private $sort_by; // sort.
    private $sort_order; // sort order.

    private $sort; // sort by (a number).
    private $dsort;
    private $query_str;

    private $fstats;
    private $dstats;

    private $clipboard;
    private $messageboard;

    function __construct() {
        /*
         * all string are UTF-8!!!
         */
        set_response_utf8();

        $this->clipboard = Utility::get_clipboard();
        $this->messageboard = Utility::get_messageboard();

        $this->dstats = array();
        $this->fstats = array();

        $files_base_dir = Utility::get_file_base_dir();
        $_SESSION['base_dir'] = $files_base_dir; // save base dir into SESSION

        $this->request_sub_dir = $this->get_request_subdir();
        //echo $this->request_sub_dir;

        $this->request_dir = $this->prepare_request_dir($files_base_dir, $this->request_sub_dir);
        if (strlen(convert_toutf8($this->request_dir)) == strlen($files_base_dir)) {
            $this->request_sub_dir = '';
        } else {
            $this->request_sub_dir = substr(convert_toutf8($this->request_dir), strlen($files_base_dir));
        }
        //echo $this->request_sub_dir;

        $this->sort_by = get_query(SORT_PARAM);
        $this->sort_order = get_query(ORDER_PARAM);

        if ($this->sort_by == '') {
            // read cookie value
            $this->sort_by = get_cookie(SORT_PARAM);
        }
        if ($this->sort_order == '') {
            $this->sort_order = get_cookie(ORDER_PARAM);
        }

        $allowed_sort_by = array('', 'n', 's', 't', 'm');
        if (!in_array($this->sort_by, $allowed_sort_by)) {
            $this->sort_by = '';
        }
        if ($this->sort_order != 'd') {
            $this->sort_order = 'a';
        }

        setcookie(SORT_PARAM, $this->sort_by, time() + 60 * 60 * 24 * 365);
        setcookie(ORDER_PARAM, $this->sort_order, time() + 60 * 60 * 24 * 365);

        $this->sort = 1;
        if ($this->sort_by == '' || ($this->sort_by == 'n' && $this->sort_order == 'a')) {
            $this->sort = 1;
        } else if ($this->sort_by == 'n' && $this->sort_order == 'd') {
            $this->sort = -1;
        } else if ($this->sort_by == 's' && $this->sort_order == 'a') {
            $this->sort = 2;
        } else if ($this->sort_by == 's' && $this->sort_order == 'd') {
            $this->sort = -2;
        } else if ($this->sort_by == 't' && $this->sort_order == 'a') {
            $this->sort = 3;
        } else if ($this->sort_by == 't' && $this->sort_order == 'd') {
            $this->sort = -3;
        } else if ($this->sort_by == 'm' && $this->sort_order == 'a') {
            $this->sort = 4;
        } else if ($this->sort_by == 'm' && $this->sort_order == 'd') {
            $this->sort = -4;
        }

        $this->dsort = 1;
        if ($this->sort == 1 || $this->sort == -1) {
            $this->dsort = $this->sort;
        } else if ($this->sort == 4 || $this->sort == -4) {
            $this->dsort = $this->sort > 0 ? 2 : -2;
        }

        $this->query_str = 's=' . $this->sort_by . '&o=' . $this->sort_order;

        $this->init_view();
    }

    /**
     * Init view page.
     */
    private function init_view() {
        if (!Utility::allow_to_view()) {
            $this->messageboard->set_message(_('Please login to browse files.'), 400);
            return;
        }
        $this->dstats = $this->get_dirs_list($this->request_dir, $this->dsort); // get sorted directory list
        $this->fstats = $this->get_files_list($this->request_dir, $this->sort); // get sorted file list
    }

    /**
     * Title string.
     * @return string title string
     */
    public function title() {
        return _(TITLENAME) . ' - ' . _('PHP File Manager');
    }

    /**
     * Get current path.
     * @return string current path
     */
    public function get_current_path() {
        return '/' . $this->request_sub_dir;
    }

    /**
     * Get current path array.
     * @return array current path exploded by "/"
     */
    public function get_current_path_array() {
        $request_sub_dir_array = explode('/', $this->request_sub_dir);
        $request_sub_dir_array_count = count($request_sub_dir_array);
        if ($request_sub_dir_array_count > 0) {
            // Remove the last "" element in array.
            if ($request_sub_dir_array[$request_sub_dir_array_count - 1] == '') {
                array_pop($request_sub_dir_array);
            }
        }
        return $request_sub_dir_array;
    }

    /**
     * Get current directory.
     * @return string current directory
     */
    public function get_current_dir() {
        $current_dir = '';
        $temp = $this->request_sub_dir;
        $temp = trim_last_slash($this->request_sub_dir);

        if ($temp == '') {
            $current_dir = 'Root';
        } else {
            $current_dir = get_basename($temp);
        }

        return $current_dir;
    }

    /**
     * Get request sub directory in $_GET.
     * @return string requested sub directory
     */
    private function get_request_subdir() {
        $request_sub_dir = rawurldecode(get_query(DIR_PARAM));

        if (false !== strpos($request_sub_dir, '..')) {
            // filter '..'
            $request_sub_dir = '';
        }

        if ($request_sub_dir != '') {
            if (substr($request_sub_dir, -1) != '/') {
                $request_sub_dir .= '/';
            }
        }

        return $request_sub_dir;
    }

    /**
     * Prepare $request_dir.
     * @param string $files_base_dir file base directory, by UTF-8.
     * @param string $request_sub_dir request sub directory, by UTF-8.
     * @return string $request_dir
     */
    private function prepare_request_dir($files_base_dir, $request_sub_dir) {
        //echo $request_sub_dir;
        $files_base_dir_plat = convert_toplat($files_base_dir);
        $request_dir = $files_base_dir_plat . $request_sub_dir; // get request directory.
        if (PLAT_CHARSET != 'UTF-8') {
            if (!file_exists($request_dir)) {
                // not exists, try to convert to platform encoding.
                $request_dir = $files_base_dir_plat . convert_toplat($request_sub_dir); // maybe GB2312 on Windows.
                if (!file_exists($request_dir)) {
                    $request_dir = $files_base_dir_plat;
                    //$request_sub_dir = '';
                }
            } else {
                // exits, means GB2312, need convert to UTF-8.
                //$request_sub_dir = convert_gbtoutf8($request_sub_dir);
            }
        } else if (PLAT_CHARSET == 'UTF-8') {
            if (!file_exists($request_dir)) {
                // not exists, try to convert to UTF-8.
                $request_sub_dir = convert_gbtoutf8($request_sub_dir);
                $request_dir = $files_base_dir_plat . $request_sub_dir; // maybe UTF-8 on Unix.
                if (!file_exists($request_dir)) {
                    $request_dir = $files_base_dir_plat;
                    //$request_sub_dir = '';
                }
            } else {
                // exits, means UTF-8, done.
            }
        }

        //echo $request_dir;
        return $request_dir;
    }

    /**
     * Get sorted file list of path.
     * @param string $path path
     * @param number $sort sort order<br />
     * 1 filename<br />
     * 2 size<br />
     * 3 type<br />
     * 4 modified time<br />
     * -1 filename reverse<br />
     * -2 size reverse<br />
     * -3 type reverse<br />
     * -4 modified time reverse
     * @return array files info array
     */
    private function get_files_list($path, $sort = 1) {
        $files = array();
        if ($handle = @opendir($path)) {
            //echo 'List of files:<br />';

            while (false !== ($file_name = @readdir($handle))) {
                //echo convert_toutf8($file).'<br />';

                $full_file_path = $path . $file_name;
                if (!@is_dir($full_file_path)) {
                    //echo convert_toutf8($full_file_path).'<br />';
                    $fstat = @stat($full_file_path);
                    if ($fstat == false) {
                        continue;
                    }
                    $type = Utility::get_file_ext($file_name);

                    $file = array();
                    $file['name'] = htmlspecialchars(convert_toutf8($file_name));
                    $file['path'] = convert_toutf8($full_file_path);
                    $file['type'] = strtolower(convert_toutf8($type));
                    $file['stat'] = $fstat;

                    if ($this->filter_item($file)) {
                        continue;
                    }

                    // Handle size.
                    $size = $file['stat']['size'];
                    $size_str = Utility::format_size($size);
                    //echo $request_sub_dir;

                    //$a_href = FILES_DIR.'/'.$this->request_sub_dir.$file['name'];
                    $a_href = 'func/download.func.php?file=' . rawurlencode($this->request_sub_dir . $file['name']);
                    $type_html = '';
                    if ($file['type'] == '') {
                        $type_html = 'file';
                    } else {
                        $type_html = $file['type'];
                    }

                    $item_path = $this->request_sub_dir . $file['name'];

                    $file['size'] = $size;
                    $file['size_str'] = $size_str;
                    $file['type_html'] = $type_html;
                    $file['a_href'] = $a_href;
                    $file['item_path'] = $item_path;

                    array_push($files, $file);
                }
            }

            closedir($handle);

            // Sort.
            $cmp_function = 'cmp_name';
            switch ($sort) {
                case 1:
                    $cmp_function = 'cmp_name';
                    break;
                case 2:
                    $cmp_function = 'cmp_size';
                    break;
                case 3:
                    $cmp_function = 'cmp_type';
                    break;
                case 4:
                    $cmp_function = 'cmp_mtime';
                    break;
                case -1:
                    $cmp_function = 'rcmp_name';
                    break;
                case -2:
                    $cmp_function = 'rcmp_size';
                    break;
                case -3:
                    $cmp_function = 'rcmp_type';
                    break;
                case -4:
                    $cmp_function = 'rcmp_mtime';
                    break;
            }
            usort($files, $cmp_function);
        }
        return $files;
    }

    /**
     * Get sorted directory list.
     * @param string $path path
     * @param number $sort sort order<br />
     * 1 filename<br />
     * 2 modified time<br />
     * -1 filename reverse<br />
     * -2 modified time reverse
     * @return array directory info array
     */
    private function get_dirs_list($path, $sort = 1) {
        $dirs = array();
        if ($handle = @opendir($path)) {
            //echo 'List of dirs:<br />';
            while (false !== ($dir_name = @readdir($handle))) {
                //echo convert_toutf8($file).'<br />';
                if ($dir_name == '.' || $dir_name == '..') {
                    // filter '.' and '..'.
                    continue;
                }

                $full_dir_path = $path . $dir_name;
                if (is_dir($full_dir_path)) {
                    //echo convert_toutf8($full_dir_path).'<br />';
                    $dstat = stat($full_dir_path);
                    $dir = array();
                    $dir['name'] = htmlspecialchars(convert_toutf8($dir_name));
                    $dir['path'] = convert_toutf8($full_dir_path);
                    $dir['stat'] = $dstat;
                    $dir['type'] = 'folder';

                    if ($this->filter_item($dir)) {
                        continue;
                    }

                    $item_path = $this->request_sub_dir . $dir['name'];

                    $dir['size'] = 0;
                    $dir['size_str'] = '&nbsp;';
                    $dir['type_html'] = 'folder';
                    $dir['item_path'] = $item_path;

                    array_push($dirs, $dir);
                }
            }

            closedir($handle);

            // sort.
            $cmp_function = 'cmp_name';
            switch ($sort) {
                case 1:
                    $cmp_function = 'cmp_name';
                    break;
                case 2:
                    $cmp_function = 'cmp_mtime';
                    break;
                case -1:
                    $cmp_function = 'rcmp_name';
                    break;
                case -2:
                    $cmp_function = 'rcmp_mtime';
                    break;
            }
            usort($dirs, $cmp_function);
        }
        return $dirs;
    }

    /**
     * Merge directories and files into one array (directory first).
     * @return array
     */
    public function get_main_list() {
        return $items = array_merge($this->dstats, $this->fstats);
    }

    /**
     * Get item list sort by.
     * @return string
     */
    public function get_sort_by() {
        return $this->sort_by;
    }

    /**
     * Get item list sort order.
     * @return string
     */
    public function get_sort_order() {
        return $this->sort_order;
    }

    /**
     * Filter item.
     * @param item $item
     * @return bool true to filter，false not
     */
    private function filter_item($item) {
        // Filter hidden files.
        if (substr($item['name'], 0, 1) == '.') {
            // Unix style.
            return true;
        }

        // Other need to filter.

        return false;
    }

    /**
     * Get upper directory path.
     * @param string $request_sub_dir current directory path
     * @return string upper directory path
     */
    private function get_parent_dir($request_sub_dir) {
        //echo $request_sub_dir;
        $last_slash = strrpos($request_sub_dir, '/');
        $parent = '';
        if ($last_slash !== false) {
            $parent = substr($request_sub_dir, 0, $last_slash);
            $last_slash = strrpos($parent, '/');
            if ($last_slash !== false) {
                $parent = substr($parent, 0, $last_slash);
            } else {
                $parent = '';
            }
        }
        //echo $parent;
        return $parent;
    }

}

?>