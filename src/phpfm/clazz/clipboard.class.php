<?php

require_once dirname(__FILE__) . "/../inc/defines.inc.php";
require_once dirname(__FILE__) . "/../inc/common.inc.php";
require_once dirname(__FILE__) . "/../inc/gettext.inc.php";
require_once "messageboard.class.php";
require_once "utility.class.php";

//@session_start();

/**
 * Clip Board Class
 *
 * 2009-8-11
 * @author Sun Junwen
 *
 */
class ClipBoard
{
    private $oper;
    private $items;

    function __construct()
    {
        $this->clear();
    }

    /**
     * 清楚剪贴板内容
     */
    private function clear()
    {
        $this->oper = "";
        $this->items = array();
    }

    /**
     * 添加项目
     * @param $oper 操作
     * @param $items 项目
     */
    public function set_items($oper, $items)
    {
        $this->oper = $oper;
        $this->items = $items;
    }

    /**
     * 粘贴，并记录到消息板
     * @param $target_subdir 目标子文件夹
     */
    public function paste($target_subdir)
    {
        $messageboard = Utility::get_messageboard();
        $message = "";

        $files_base_dir = $_SESSION['base_dir'];
        //$old_dir = $files_base_dir . $this->subdir;
        $new_dir = $files_base_dir . $target_subdir;
        $count = count($this->items);
        for ($i = 0; $i < $count; $i++) {
            $item = $this->items[$i];
            $item_sub_dir = dirname($item);
            $oldname = $files_base_dir . $item;
            $basename = get_basename($item);
            $newname = $new_dir . $basename;
            get_logger()->info($this->oper . ": " . $oldname . " to " . $newname);

            // 处理重名
            $success = false;
            if ($this->oper == "cut") {
                $message .= (_("Cut") . " " . htmlentities_utf8($item) . " ");
                $success = Utility::phpfm_rename($oldname, $newname, true);
            } else if ($this->oper == "copy") {
                $message .= (_("Copy") . " " . htmlentities_utf8($item) . " ");
                $success = Utility::phpfm_copy($oldname, $newname);
            }

            if ($success) {
                $message .= (_("succeed") . "<br />");
                $stat = 1;
            } else {
                $message .= ("<strong>" . _("failed") . "</strong><br />");
                $stat = 2;
            }
        }
        $messageboard->set_message($message, $stat);
        $this->clear();
    }

    /**
     * 剪贴板中是否有内容
     * @return bool 有 true，没有 false
     */
    public function have_items()
    {
        if (is_array($this->items)) {
            if (count($this->items) > 0 && $this->oper != "") {
                return true;
            }
        }

        return false;
    }

    /**
     * 输出 debug 信息
     */
    public function debug()
    {
        print_r($this->oper);
        print_r($this->items);
    }

}

?>