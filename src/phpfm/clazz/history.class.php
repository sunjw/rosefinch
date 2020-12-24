<?php

/**
 * History item class
 * 2010-7-21
 * @author Sun Junwen
 *
 */
class HistoryItem
{
    private $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function get_dir()
    {
        return $this->dir;
    }

    public function equal($dir)
    {
        return ($this->dir == $dir);
    }

    public function to_string()
    {
        if ($this->dir == "") {
            return "Root";
        } else {
            return trim_last_slash($this->dir);
        }
    }

}

/**
 * History class
 * 2010-7-21
 * @author Sun Junwen
 *
 */
class History
{
    private $history_array;
    private $length;
    private $current;

    public function __construct()
    {
        $this->clear();
    }

    /**
     * Push a history.
     * @param string $dir current directory
     */
    public function push($dir)
    {
        if ($this->current == 0) {
            $this->history_array[++$this->current] = new HistoryItem($dir);
            $this->length = $this->current;
            return;
        }

        $current_item = $this->history_array[$this->current];
        if (!$current_item->equal($dir)) {
            $this->history_array[++$this->current] = new HistoryItem($dir);
            $this->length = $this->current;
        }
    }

    /**
     * Detect if be able to back.
     * @return bool
     */
    public function able_to_back()
    {
        return ($this->current > 1);
    }

    /**
     * Back.<br/>
     * Get back History item.
     * @return HistoryItem
     */
    public function back($step = 1)
    {
        $step = $step == null ? 1 : $step;
        if ($this->current < $step) {
            return null;
        }

        if ($this->current > $step) {
            $this->current -= $step;
        }

        return $this->history_array[$this->current];
    }

    /**
     * Detect if be able to forward.
     * @return bool
     */
    public function able_to_forward()
    {
        return ($this->current < $this->length);
    }

    /**
     * Forward.<br/>
     * Get forward History item.
     * @return HistoryItem
     */
    public function forward($step = 1)
    {
        $step = $step == null ? 1 : $step;
        if ($this->current < 1) {
            return null;
        }

        if ($this->current <= $this->length - $step) {
            $this->current += $step;
        }

        return $this->history_array[$this->current];
    }

    /**
     * Get current position.
     * @return HistoryItem
     */
    public function get_current()
    {
        return $this->current;
    }

    /**
     * Get history length.
     * @return number history length
     */
    public function get_length()
    {
        return $this->length;
    }

    /**
     * Get history array.
     * @return Array history array
     */
    public function get_history()
    {
        return $this->history_array;
    }

    /**
     * Debug.
     */
    public function debug()
    {
        echo 'Current: ' . $this->current . '<br />';
        echo 'Length: ' . $this->length . '<br />';
        echo 'History:<br />';
        print_r($this->history_array);
    }

    /**
     * Debug.
     */
    public function clear()
    {
        $this->history_array = array();
        $this->length = 0;
        $this->current = 0;
    }

}

?>