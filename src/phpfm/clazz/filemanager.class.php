<?php

require_once dirname(__FILE__) . "/../inc/defines.inc.php";
require_once dirname(__FILE__) . "/../inc/common.inc.php";
require_once dirname(__FILE__) . "/../inc/gettext.inc.php";
require_once dirname(__FILE__) . "/../inc/sort.inc.php";
require_once "clipboard.class.php";
require_once "messageboard.class.php";
require_once "history.class.php";
require_once "utility.class.php";

@session_start();

/**
 * File Manager Class.
 * 2009-8-4
 * @author Sun Junwen
 *
 */
class FileManager
{
    private $request_sub_dir; // request sub directory path, UTF-8.
    private $request_dir; // request directory absolute path.
    private $sort_type; // sort.
    private $order; // order.
    private $view_type; // view type.
    private $toolbar_type;
    private $is_mobile;

    private $sort; // sort by.
    private $dsort;
    private $query_str;

    private $fstats;
    private $dstats;

    private $view_page; // view page, default is index.php

    private $clipboard;
    private $messageboard;
    private $history;

    function __construct($view_page = "index.php")
    {
        /*
         * all string are UTF-8!!!
         */
        set_response_utf8();

        $this->is_mobile = is_mobile_browser();

        $this->clipboard = Utility::get_clipboard();
        $this->messageboard = Utility::get_messageboard();
        $this->history = Utility::get_history();

        $this->view_page = $view_page;

        $this->dstats = array();
        $this->fstats = array();

        $files_base_dir = Utility::get_file_base_dir();
        $_SESSION['base_dir'] = $files_base_dir; // 将文件基路径存入 SESSION

        $this->request_sub_dir = $this->get_request_subdir();
        //echo $this->request_sub_dir;

        $this->request_dir = $this->prepare_request_dir($files_base_dir, $this->request_sub_dir);
        if (strlen(convert_toutf8($this->request_dir)) == strlen($files_base_dir)) {
            $this->request_sub_dir = "";
        } else {
            $this->request_sub_dir = substr(convert_toutf8($this->request_dir), strlen($files_base_dir));
        }
        //echo $this->request_sub_dir;

        $this->sort_type = get_query(SORT_PARAM);
        $this->order = get_query(ORDER_PARAM);
        $this->view_type = get_query(VIEW_PARAM);

        if ($this->sort_type == "") {
            // 读取 Cookie 值
            $this->sort_type = get_cookie(SORT_PARAM);
        }
        if ($this->order == "") {
            $this->order = get_cookie(ORDER_PARAM);
        }
        if ($this->view_type == "") {
            $this->view_type = get_cookie(VIEW_PARAM);
        }

        $this->toolbar_type = get_cookie(TOOLBAR_PARAM);
        if ($this->toolbar_type != "little")
            $this->toolbar_type = "full";

        $allowed_sort_type = array('', 'n', 's', 't', 'm');
        $allowed_view_type = array('', 'detail', 'largeicon');
        if (!in_array($this->sort_type, $allowed_sort_type)) {
            $this->sort_type = "";
        }
        if ($this->order != "d") {
            $this->order = "a";
        }
        if (!in_array($this->view_type, $allowed_view_type)) {
            $this->view_type = "";
        }

        setcookie(SORT_PARAM, $this->sort_type, time() + 60 * 60 * 24 * 365);
        setcookie(ORDER_PARAM, $this->order, time() + 60 * 60 * 24 * 365);
        setcookie(VIEW_PARAM, $this->view_type, time() + 60 * 60 * 24 * 365);

        $this->sort = 1;
        if ($this->sort_type == "" || ($this->sort_type == "n" && $this->order == "a")) {
            $this->sort = 1;
        } else if ($this->sort_type == "n" && $this->order == "d") {
            $this->sort = -1;
        } else if ($this->sort_type == "s" && $this->order == "a") {
            $this->sort = 2;
        } else if ($this->sort_type == "s" && $this->order == "d") {
            $this->sort = -2;
        } else if ($this->sort_type == "t" && $this->order == "a") {
            $this->sort = 3;
        } else if ($this->sort_type == "t" && $this->order == "d") {
            $this->sort = -3;
        } else if ($this->sort_type == "m" && $this->order == "a") {
            $this->sort = 4;
        } else if ($this->sort_type == "m" && $this->order == "d") {
            $this->sort = -4;
        }

        $this->dsort = 1;
        if ($this->sort == 1 || $this->sort == -1) {
            $this->dsort = $this->sort;
        } else if ($this->sort == 4 || $this->sort == -4) {
            $this->dsort = $this->sort > 0 ? 2 : -2;
        }

        $this->query_str = "s=" . $this->sort_type . "&o=" . $this->order . "&view=" . $this->view_type;

        $this->init_view();

        if (!isset($_GET['h'])) {
            // h means visit by back/forward.
            $this->history->push($this->request_sub_dir);
        }
    }

    /**
     * Init view page.
     */
    private function init_view()
    {
        if (!Utility::allow_to_view()) {
            $this->messageboard->set_message(_("Please login to browse files."), 2);
            return;
        }
        $this->dstats = $this->get_dirs_list($this->request_dir, $this->dsort); // get sorted directory list
        $this->fstats = $this->get_files_list($this->request_dir, $this->sort); // get sorted file list
    }

    /**
     * Title string.
     * @return string title string
     */
    public function title()
    {
        return _(TITLENAME) . " - " . _("PHP File Manager");
    }

    /**
     * Title HTML.
     * @return string title HTML
     */
    public function title_html()
    {
        return htmlentities_utf8($this->title());
    }

    /**
     * HTML include, css and js.
     */
    public function html_include_files()
    {
        $rand = "?rand=" . rand(1, 1000);
        ?>
        <link href="css/filemanager.css<?php echo($rand); ?>" rel="stylesheet" type="text/css"/>
        <link href="css/message.css<?php echo($rand); ?>" rel="stylesheet" type="text/css"/>
        <link href="css/detailView.css<?php echo($rand); ?>" rel="stylesheet" type="text/css"/>
        <link href="css/largeiconView.css" rel="stylesheet" type="text/css"/>
        <link href="css/func.css<?php echo($rand); ?>" rel="stylesheet" type="text/css"/>
        <link href="css/jquery.lightbox-0.5.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript" language="javascript" src="js/jquery-1.8.1.min.js"></script>
        <script type="text/javascript" language="javascript" src="js/jquery.common.min.js"></script>
        <script type="text/javascript" language="javascript" src="js/jquery.menu.min.js"></script>
        <script type="text/javascript" language="javascript" src="js/jquery.lightbox-0.5.plus.js"></script>
        <script type="text/javascript" language="javascript" src="js/filemanager.js<?php echo($rand); ?>"></script>
        <?php
    }

    /**
     * Get current path.
     * @return string current path
     */
    public function get_current_path()
    {
        return "/" . $this->request_sub_dir;
    }

    /**
     * Get current directory.
     * @return string current directory
     */
    public function get_current_dir()
    {
        $current_dir = "";
        $temp = $this->request_sub_dir;
        $temp = trim_last_slash($this->request_sub_dir);

        if ($temp == "") {
            $current_dir = "Root";
        } else {
            $current_dir = get_basename($temp);
        }

        return $current_dir;
    }

    /**
     * Get request sub directory in $_GET.
     * @return string requested sub directory
     */
    private function get_request_subdir()
    {
        $request_sub_dir = rawurldecode(get_query(DIR_PARAM));

        if (false !== strpos($request_sub_dir, "..")) {
            // filter '..'
            $request_sub_dir = "";
        }

        if ($request_sub_dir != "") {
            if (substr($request_sub_dir, -1) != "/") {
                $request_sub_dir .= "/";
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
    private function prepare_request_dir($files_base_dir, $request_sub_dir)
    {
        //echo $request_sub_dir;
        $files_base_dir_plat = convert_toplat($files_base_dir);
        $request_dir = $files_base_dir_plat . $request_sub_dir; // get request directory.
        if (PLAT_CHARSET != "UTF-8") {
            if (!file_exists($request_dir)) {
                // not exists, try to convert to platform encoding.
                $request_dir = $files_base_dir_plat . convert_toplat($request_sub_dir); // maybe GB2312 on Windows.
                if (!file_exists($request_dir)) {
                    $request_dir = $files_base_dir_plat;
                    //$request_sub_dir = "";
                }
            } else {
                // exits, means GB2312, need convert to UTF-8.
                //$request_sub_dir = convert_gbtoutf8($request_sub_dir);
            }
        } else if (PLAT_CHARSET == "UTF-8") {
            if (!file_exists($request_dir)) {
                // not exists, try to convert to UTF-8.
                $request_sub_dir = convert_gbtoutf8($request_sub_dir);
                $request_dir = $files_base_dir_plat . $request_sub_dir; // maybe UTF-8 on Unix.
                if (!file_exists($request_dir)) {
                    $request_dir = $files_base_dir_plat;
                    //$request_sub_dir = "";
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
    private function get_files_list($path, $sort = 1)
    {
        $files = array();
        if ($handle = @opendir($path)) {
            //echo "List of files:<br />";

            while (false !== ($file_name = @readdir($handle))) {
                //echo convert_toutf8($file)."<br />";

                $full_file_path = $path . $file_name;
                if (!@is_dir($full_file_path)) {
                    //echo convert_toutf8($full_file_path)."<br />";
                    $fstat = @stat($full_file_path);
                    if ($fstat == false) {
                        continue;
                    }
                    $type = Utility::get_file_ext($file_name);

                    $file = array();
                    $file['name'] = htmlspecialchars(convert_toutf8($file_name));
                    $file['path'] = convert_toutf8($full_file_path);
                    $file['type'] = convert_toutf8($type);
                    $file['stat'] = $fstat;

                    if ($this->filter_item($file)) {
                        continue;
                    }

                    // Handle size.
                    $size = $file['stat']['size'];
                    $size = Utility::format_size($size);
                    //echo $request_sub_dir;

                    //$a_href = FILES_DIR."/".$this->request_sub_dir.$file['name'];
                    $a_href = "func/download.func.php?file=" . rawurlencode($this->request_sub_dir . $file['name']);
                    $type_html = "";
                    if ($file['type'] == "") {
                        $type_html = _("File");
                    } else {
                        $type_html = $file['type'];
                    }

                    $item_path = $this->request_sub_dir . $file['name'];

                    $file['size_str'] = $size;
                    $file['type_html'] = $type_html;
                    $file['a_href'] = $a_href;
                    $file['item_path'] = $item_path;

                    array_push($files, $file);
                }
            }

            closedir($handle);

            // Sort.
            $cmp_function = "cmp_name";
            switch ($sort) {
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
     * Get sorted directory list.
     * @param string $path path
     * @param number $sort sort order<br />
     * 1 filename<br />
     * 2 modified time<br />
     * -1 filename reverse<br />
     * -2 modified time reverse
     * @return array directory info array
     */
    private function get_dirs_list($path, $sort = 1)
    {
        $dirs = array();
        if ($handle = @opendir($path)) {
            //echo "List of dirs:<br />";
            while (false !== ($dir_name = @readdir($handle))) {
                //echo convert_toutf8($file)."<br />";
                if ($dir_name == "." || $dir_name == "..") {
                    // filter "." and "..".
                    continue;
                }

                $full_dir_path = $path . $dir_name;
                if (is_dir($full_dir_path)) {
                    //echo convert_toutf8($full_dir_path)."<br />";
                    $dstat = stat($full_dir_path);
                    $dir = array();
                    $dir['name'] = htmlspecialchars(convert_toutf8($dir_name));
                    $dir['path'] = convert_toutf8($full_dir_path);
                    $dir['stat'] = $dstat;
                    $dir['type'] = "dir";

                    if ($this->filter_item($dir)) {
                        continue;
                    }

                    $a_href = $this->view_page . "?" .
                        $this->query_str . "&dir=" .
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

            closedir($handle);

            // sort.
            $cmp_function = "cmp_name";
            switch ($sort) {
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
     * Filter item.
     * @param item $item
     * @return bool true to filter，false not
     */
    private function filter_item($item)
    {
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
    private function get_parent_dir($request_sub_dir)
    {
        //echo $request_sub_dir;
        $last_slash = strrpos($request_sub_dir, "/");
        $parent = "";
        if ($last_slash !== false) {
            $parent = substr($request_sub_dir, 0, $last_slash);
            $last_slash = strrpos($parent, "/");
            if ($last_slash !== false) {
                $parent = substr($parent, 0, $last_slash);

            } else {
                $parent = "";
            }
        }
        //echo $parent;
        return $parent;
    }

    /**
     * Display current directory full path.
     */
    public function display_full_path()
    {
        if (!Utility::allow_to_view())
            return;
        ?>
        <div id="fullpath">
            <div>
                <div class="divDir"><a href="<?php echo $this->view_page . "?" . $this->query_str; ?>">Root</a></div>
                <div class="pathSlash menuContainer">
                    <a class="arrow menuButton" href="javascript:void(0);">&nbsp;</a>

                    <?php
                    $sub_dirs = explode("/", $this->request_sub_dir);
                    $dir_str = "";
                    if (!$this->is_mobile) {
                        $this->display_sub_menus($dir_str, $sub_dirs[0]);
                    }
                    ?>
                </div>
                <?php
                //print_r($sub_dirs);
                $len = count($sub_dirs);
                $sub_dirs[$len] = "";
                for ($i = 0; $i < $len - 1; $i++) {
                    $sub_dir = $sub_dirs[$i];
                    $dir_str .= $sub_dir;
                    ?>
                    <div class="divDir">
                        <a href="<?php echo $this->view_page . "?" . $this->query_str; ?>&dir=<?php echo rawurlencode($dir_str); ?>">
                            <?php echo str_replace(" ", "&nbsp;", $sub_dir); ?>
                        </a>
                    </div>
                    <div class="pathSlash menuContainer">
                        <a class="arrow menuButton" href="javascript:void(0);">&nbsp;</a>
                        <?php
                        $dir_str .= "/";
                        if (!$this->is_mobile) {
                            $this->display_sub_menus($dir_str, $sub_dirs[$i + 1]);
                        }
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
     * Fill sub directory menu.
     * @param string $sub_dir_str directory path to current
     * @param string $next_in_path next item in path
     */
    private function display_sub_menus($sub_dir_str, $next_in_path = "")
    {
        $temp = $sub_dir_str;
        $base_dir = $this->prepare_request_dir(Utility::get_file_base_dir(), $temp);
        if ($base_dir != "") {
            $sub_dstats = $this->get_dirs_list($base_dir);
            if (count($sub_dstats) > 0) {
                ?>
                <div class="subMenu">
                    <ul>
                        <?php
                        foreach ($sub_dstats as $sub_dstat) {
                            if (!$this->filter_item($sub_dstat)) {
                                ?>
                                <li>
                                    <a href="<?php echo $this->view_page . "?" . $this->query_str; ?>&dir=<?php echo rawurlencode($sub_dir_str . $sub_dstat['name']); ?>"
                                       title="<?php echo $sub_dstat['name']; ?>">
                                        <?php
                                        if ($sub_dstat['name'] == $next_in_path) {
                                            printf("<strong>%s</strong>", str_replace(" ", "&nbsp;", $sub_dstat['name']));
                                        } else {
                                            printf(str_replace(" ", "&nbsp;", $sub_dstat['name']));
                                        }
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
     * Display toolbar.
     */
    public function display_toolbar()
    {
        $this_page = $this->view_page;

        // Prepare basic icons.
        $this->prepare_basic_funcs($query_str, $up, $up_img, $new_folder_img, $upload_img);

        // Prepare view mode icon.
        $detail_view_url = $this_page . "?" . $query_str .
            "&dir=" . rawurlencode($this->request_sub_dir) . "&view=detail";
        $largeicon_view_url = $this_page . "?" . $query_str .
            "&dir=" . rawurlencode($this->request_sub_dir) . "&view=largeicon";

        // Prepare paste.
        $this->prepare_paste_func($paste_img_src, $paste_class);

        // Prepare history.
        $this->prepare_history_funcs($back_url, $back_class, $forward_url, $forward_class);
        $history_items = $this->render_history_items();

        // Prepare more
        $more_img_src = "images/toolbar-arrow-left.gif";
        $more_class = "full";
        if ($this->toolbar_type == "little") {
            $more_img_src = "images/toolbar-arrow-right.gif";
            $more_class = "little";
        }

        // Prepare button name.
        $button_names = $this->prepare_buttons_name();

        // Toolbar HTML.
        ?>
        <div id="toolbar">
            <div id="leftToolbar">
                <a href="<?php echo $back_url; ?>" title="<?php echo $button_names['Back']; ?>"
                   class="toolbarButton toolbarBack <?php echo $back_class; ?>">
                    <img alt="<?php echo $button_names['Back']; ?>" src="images/toolbar-back.png"/>
                </a>
                <a href="<?php echo $forward_url; ?>" title="<?php echo $button_names['Forward']; ?>"
                   class="toolbarButton toolbarForward <?php echo $forward_class; ?>">
                    <img alt="<?php echo $button_names['Forward']; ?>" src="images/toolbar-forward.png"/>
                </a>
                <div title="<?php echo $button_names['Refresh']; ?>" class="toolbarButton toolbarRefresh">
                    <img alt="<?php echo $button_names['Refresh']; ?>" src="images/toolbar-refresh.png"/>
                </div>
                <?php
                if (Utility::allow_to_modify()) {
                    ?>
                    <div class="toolbarPart">
                        <div>
                            <div title="<?php echo $button_names['Upload']; ?>"
                                 class="toolbarButton toolbarUpload splitRight">
                                <img alt="<?php echo $button_names['Upload']; ?>" src="<?php echo $upload_img; ?>"/>
                            </div>
                            <div title="<?php echo $button_names['New Folder']; ?>"
                                 class="toolbarButton toolbarNewFolder">
                                <img alt="<?php echo $button_names['New Folder']; ?>"
                                     src="<?php echo $new_folder_img; ?>"/>
                            </div>
                            <?php
                            if (!$this->is_mobile) {
                                ?>
                                <div title="<?php echo $button_names['Cut']; ?>" class="toolbarButton toolbarCut">
                                    <img alt="<?php echo $button_names['Cut']; ?>" src="images/toolbar-cut.png"/>
                                </div>
                                <div title="<?php echo $button_names['Copy']; ?>" class="toolbarButton toolbarCopy">
                                    <img alt="<?php echo $button_names['Copy']; ?>" src="images/toolbar-copy.png"/>
                                </div>
                                <div title="<?php echo $button_names['Paste']; ?>"
                                     class="toolbarButton toolbarPaste splitRight <?php echo $paste_class; ?>">
                                    <img alt="<?php echo $button_names['Paste']; ?>"
                                         src="<?php echo $paste_img_src; ?>"/>
                                </div>
                                <?php
                            }
                            ?>
                            <div title="<?php echo $button_names['Rename']; ?>" class="toolbarButton toolbarRename">
                                <img alt="<?php echo $button_names['Rename']; ?>" src="images/toolbar-rename.png"/>
                            </div>
                            <div title="<?php echo $button_names['Delete']; ?>"
                                 class="toolbarButton toolbarDelete splitRight">
                                <img alt="<?php echo $button_names['Delete']; ?>" src="images/toolbar-delete.png"/>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div id="rightToolbar">
            </div>
        </div>
        <?php
    }

    /**
     * Display main view.
     */
    public function display_main_view()
    {
        ?>
        <div id="mainView">
            <?php
            if (!$this->is_mobile) {
                // display header.
                $this->display_header();
            }

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
     * Prepare HTML for functions.
     */
    public function display_func_pre()
    {
        $multilan_titles = "";
        $multilan_titles .= ("rename:" . _("Rename") . "|");
        $multilan_titles .= ("new folder:" . _("New Folder") . "|");
        $multilan_titles .= ("upload:" . _("Upload") . "|");
        $multilan_titles .= ("delete:" . _("Confirm") . "|");
        $multilan_titles .= ("preview:" . _("Preview") . "|");
        $multilan_titles .= ("user:" . _("User") . "|");
        $multilan_titles .= ("waiting:" . _("Working...") . "|");
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
                    <input type="hidden" id="api" name="api" value=""/>
                    <input type="hidden" id="subdir" name="subdir"
                           value="<?php echo rawurlencode($this->request_sub_dir); ?>"/>
                    <input type="hidden" id="renamePath" name="renamePath" value=""/>
                    <input type="hidden" id="return" name="return" value="<?php echo rawurlencode(get_URI()); ?>"/>
                    <div id="divReqInput">
                        <table>
                            <tr id="oldnameLine">
                                <td><label for="oldname"><?php printf("%s&nbsp;", _("Old Name:")); ?></label></td>
                                <td><input id="oldname" type="text" name="oldname" value="" maxlength="128"
                                           readonly="readonly"/></td>
                            </tr>
                            <tr>
                                <td><label for="newname"><?php printf("%s&nbsp;", _("New Name:")); ?></label></td>
                                <td><input id="newname" type="text" name="newname" value="" maxlength="128"/></td>
                            </tr>
                        </table>
                        <div>
                            <span class="inputRequire"><?php printf(_("There should not have %s in new name"), ".., /, \, *, ?, \", |, &amp;, &lt;, &gt;"); ?></span>
                        </div>
                    </div>
                    <div id="divUpload">
                        <div>
                            <label id="uploadFileInfo"
                                   for="uploadFile"><?php printf("%s&nbsp;", ($this->is_mobile ? _("Click here to upload.") : _("Drag and drop files here to upload."))); ?></label>
                            <input id="uploadFile" type="file" name="uploadFile[]" multiple="multiple"/>
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
                                <td><input id="password" type="password" name="password" value="" maxlength="128"/></td>
                            </tr>
                        </table>
                    </div>
                    <div id="divLogout">
                        <div class="center"><?php echo _("Are you sure to logout?"); ?></div>
                    </div>
                    <div class="funcBtnLine">
                        <input type="submit" value="<?php echo _("OK"); ?>" onclick="FileManager.funcSubmit()"/><input
                                type="button" value="<?php echo _("Cancel"); ?>" onclick="FileManager.closeFunc()"/>
                    </div>
                </form>
            </div>
            <div id="divDelete" class="container">
                <div class="center"><?php echo _("Are you sure to delete these items?"); ?></div>
                <div class="funcBtnLine">
                    <input type="button" value="<?php echo _("OK"); ?>" onclick="FileManager.doDelete()"/><input
                            type="button" value="<?php echo _("Cancel"); ?>" onclick="FileManager.closeFunc()"/>
                </div>
            </div>
            <div id="divPreview" class="container">
                <div id="divPreviewContent">
                </div>
                <div id="link"><?php echo _("Download:"); ?>&nbsp;</div>
                <?php
                if ($this->is_mobile) {
                    ?>
                    <div class="funcBtnLine">
                        <input type="button" value="<?php echo _("Close"); ?>" onclick="FileManager.closeFunc()"/>
                    </div>
                    <?php
                }
                ?>
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
     * Display directories and files.
     */
    private function render_main_view($items)
    {
        // Only detail view.
        ?>
        <ul id="detailView" class="<?php echo $this->sort_type; ?>">
            <?php
            $i = 0;
            foreach ($items as $item) {
                $this->detail_view_item($item['item_path'],
                    $item['a_href'],
                    $item['name'],
                    Utility::get_icon($item['type'], 36),
                    $item['name'],
                    $item['size_str'],
                    $item['type_html'],
                    $item['stat']['mtime']);
                $i++;
            }

            ?>
        </ul>
        <?php
    }

    /**
     * Display header.
     */
    private function display_header()
    {
        $this_page = $this->view_page;

        $request_sub_dir = $this->request_sub_dir;
        $sort_type = $this->sort_type;
        $order = $this->order;

        $norder = "a";
        $sorder = "a";
        $torder = "a";
        $morder = "a";

        if ($sort_type == "" || ($sort_type == "n" && $order == "a")) {
            $norder = "d";
        } else if ($sort_type == "s" && $order == "a") {
            $sorder = "d";
        } else if ($sort_type == "t" && $order == "a") {
            $torder = "d";
        } else if ($sort_type == "m" && $order == "a") {
            $morder = "d";
        }
        ?>
        <div class="header">
            <span class="check">
                <input id="checkSelectAll" type="checkbox" title="<?php echo _("Select All"); ?>"/>
            </span>
            <span class="icon">&nbsp;</span>
            <span class="name split">
                <a href="<?php echo $this_page . "?dir=" .
                    $request_sub_dir . "&s=n" .
                    "&o=" . $norder; ?>"><?php echo _("Name"); ?></a>
            </span>
            <span class="size split">
                <a href="<?php echo $this_page . "?dir=" .
                    $request_sub_dir . "&s=s" .
                    "&o=" . $sorder; ?>"><?php echo _("Size"); ?></a>
            </span>
            <?php
            if (!$this->is_mobile) {
                ?>
                <span class="type split">
                <a href="<?php echo $this_page . "?dir=" .
                    $request_sub_dir . "&s=t" .
                    "&o=" . $torder; ?>"><?php echo _("Type"); ?></a>
            </span>
                <span class="mtime split">
                <a href="<?php echo $this_page . "?dir=" .
                    $request_sub_dir . "&s=m" .
                    "&o=" . $morder; ?>"><?php echo _("Modified Time"); ?></a>
            </span>
                <?php
            }
            ?>
        </div>
        <?php
        $javascript_call_arg = "name";
        if ($sort_type == "s") {
            $javascript_call_arg = "size";
        } else if ($sort_type == "t") {
            $javascript_call_arg = "type";
        } else if ($sort_type == "m") {
            $javascript_call_arg = "mtime";
        }

        ?>
        <script type="text/javascript">
            //<![CDATA[
            FileManager.setSortArrow(<?php echo "\"$javascript_call_arg\""; ?>, <?php echo "\"$order\""; ?>);
            //]]>
        </script>
        <?php
    }

    /**
     * Display "UP".
     */
    private function display_up()
    {
        if ($this->request_sub_dir != "") {
            //echo $request_sub_dir;
            $up = $this->view_page . "?";
            $up .= $this->query_str;
            $up .= ("&dir=" . $this->get_parent_dir($this->request_sub_dir));
            ?>
            <li>
                <span class="check"></span>
                <a href="<?php echo $up; ?>">
                    <span class="icon">
                    <img src="images/go-up.gif" alt="file icon" width="16" height="16" border="0"/>
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
     * Display detail view row.
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
        if (LIGHTBOX && $this->is_img_type($type)) {
            $class = 'class="lightboxImg"';
        }

        if (AUDIOPLAYER && $this->is_audio_type($type)) {
            $class = 'class="audioPlayer"';
        }

        ?>
        <li>
            <span class="check">
                <input class="inputCheck" type="checkbox" name="<?php echo $item_path; ?>"/>
            </span>
            <a href="<?php echo $a_href; ?>" title="<?php echo $a_title; ?>" <?php echo $class; ?>>
                <span class="icon"><?php echo $img_html; ?></span>
                <span class="name"><?php echo str_replace(" ", "&nbsp;", $name); ?></span>
            </a>
            <span class="size"><?php echo $size; ?></span>
            <?php
            if (!$this->is_mobile) {
                ?>
                <span class="type"><?php echo $type; ?></span>
                <span class="mtime"><?php echo date("Y-n-j H:i", $mtime); ?></span>
                <?php
            }
            ?>
        </li>
        <?php
    }

    /**
     * Detect image by type.
     * @param string $type type
     * @return boolean
     */
    private function is_img_type($type)
    {
        $type = strtolower($type);
        if ($type == "jpg" ||
            $type == "jpeg" ||
            $type == "bmp" ||
            $type == "png" ||
            $type == "gif") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Detect music by type.
     * @param string $type type
     * @return boolean
     */
    private function is_audio_type($type)
    {
        $type = strtolower($type);
        if ($type == "mp3") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Prepare basic functions.
     * @param string $query_str query string
     * @param string $up UP address
     * @param string $up_img UP icon
     * @param string $new_folder_img new folder icon
     * @param string $upload_img upload icon
     */
    private function prepare_basic_funcs(
        &$query_str, &$up,
        &$up_img, &$new_folder_img,
        &$upload_img)
    {
        $query_str = $this->query_str;
        $up = "";
        $up_img = "images/toolbar-up.png";
        $new_folder_img = "images/toolbar-new-folder.png";
        $upload_img = "images/toolbar-upload.png";

        //echo $request_sub_dir;
        // Set UP, new folder and upload state.
        $up = $this->view_page . "?";
        $up .= $this->query_str;
        $up .= ("&dir=" . rawurlencode($this->get_parent_dir($this->request_sub_dir)));
    }

    /**
     * Prepare paste.
     * @param string $paste_img paste icon
     * @param string $paste_class paste css class
     */
    private function prepare_paste_func(&$paste_img, &$paste_class)
    {
        $paste_img = "images/toolbar-paste.png";
        $paste_class = "disable";
        if ($this->clipboard->has_items()) {
            $paste_img = "images/toolbar-paste.png";
            $paste_class = "";
        }
    }

    /**
     * Prepare history.
     * @param string $back_url back url
     * @param string $back_class back css class
     * @param string $forward_url forward url
     * @param string $forward_class forward css class
     */
    private function prepare_history_funcs(
        &$back_url, &$back_class,
        &$forward_url, &$forward_class)
    {
        $back_url = "javascript:;";
        $back_class = "disable";
        $forward_url = "javascript:;";
        $forward_class = "disable";
        if ($this->history->able_to_back()) {
            $back_class = "";
            $back_url = "func/history.func.php?action=b";
        }
        if ($this->history->able_to_forward()) {
            $forward_class = "";
            $forward_url = "func/history.func.php?action=f";
        }
    }

    /**
     * Render history to HTML.
     * @return string
     */
    private function render_history_items()
    {
        $history_current = $this->history->get_current() - 1;
        $history = $this->history->get_history();
        $history_items = "";
        $i = 0;
        foreach ($history as $item) {
            if ($i >= $this->history->get_length()) {
                break;
            }

            $url = "func/history.func.php?action=f" . "&step=" . ($i - $history_current);
            if ($i != $history_current) {
                $history_items .= ('<li><a href="' . $url . '">');
            } else {
                $history_items .= ('<li class="current">');
            }

            $history_items .= ($item->to_string());

            if ($i != $history_current) {
                $history_items .= '</a></li>';
            } else {
                $history_items .= '</li>';
            }

            ++$i;
        }

        return $history_items;
    }

    /**
     * Set button title.
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
}

?>