<?php

require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once 'utility.class.php';

/**
 * Clipboard Class
 *
 * 2009-8-11
 * @author Sun Junwen
 *
 */
class ClipBoard
{
    private $oper; // cut/copy
    private $items;

    function __construct()
    {
        $this->clear();
    }

    /**
     * Clear clipboard content.
     */
    private function clear()
    {
        $this->oper = '';
        $this->items = array();
    }

    /**
     * Add items.
     * @param string $oper cut/copy
     * @param array $items paths array
     */
    public function set_items($oper, $items)
    {
        $this->oper = $oper;
        $this->items = $items;
    }

    /**
     * Paste items to target directory.
     * @param string $target_subdir target directory
     * @return array result array (path => true/false)
     */
    public function paste($target_subdir)
    {
        $result = [];
        $result['items'] = [];

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
            get_logger()->info('paste, ' . $this->oper . ': [' . $oldname . '] to [' . $newname . '].');

            $success = false;
            if ($this->oper == 'cut') {
                $success = Utility::phpfm_rename($oldname, $newname, true);
            } else if ($this->oper == 'copy') {
                $success = Utility::phpfm_copy($oldname, $newname);
            } else {
                get_logger()->error('paste, unknown oper: [' . $this->oper . '].');
            }

            $result['items'][$item] = $success;
        }

        $result['oper'] = $this->oper;

        $this->clear();
        return $result;
    }

    /**
     * Check has items in clipboard.
     * @return bool
     */
    public function has_items()
    {
        if (is_array($this->items)) {
            if (count($this->items) > 0 && $this->oper != '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Debug.
     */
    public function debug()
    {
        print_r($this->oper);
        print_r($this->items);
    }

}

?>