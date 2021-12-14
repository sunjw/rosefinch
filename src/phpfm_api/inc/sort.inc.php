<?php
/*
 * Custom compare functions.
 * 2009-8-4 rev.1
 * @author Sun Junwen
 *
 */

/**
 * Compare by file name.
 * @param item $a
 * @param item $b
 * @return int
 */
function cmp_name($a, $b) {
    return strcmp($a['name'], $b['name']);
}

/**
 * Compare by file name, reverse.
 * @param item $a
 * @param item $b
 * @return int
 */
function rcmp_name($a, $b) {
    return strcmp($b['name'], $a['name']);
}

/**
 * Compare by file size.
 * @param item $a
 * @param item $b
 * @return int
 */
function cmp_size($a, $b) {
    if ($a['stat']['size'] == $b['stat']['size']) {
        return 0;
    } else if ($a['stat']['size'] > $b['stat']['size']) {
        return 1;
    } else {
        return -1;
    }
}

/**
 * Compare by file size, reverse.
 * @param item $a
 * @param item $b
 * @return int
 */
function rcmp_size($a, $b) {
    return 0 - cmp_size($a, $b);
}

/**
 * Compare by file modified time.
 * @param item $a
 * @param item $b
 * @return int
 */
function cmp_mtime($a, $b) {
    if ($a['stat']['mtime'] == $b['stat']['mtime']) {
        return 0;
    } else if ($a['stat']['mtime'] > $b['stat']['mtime']) {
        return 1;
    } else {
        return -1;
    }
}

/**
 * Compare by file modified time, reverse.
 * @param item $a
 * @param item $b
 * @return int
 */
function rcmp_mtime($a, $b) {
    return 0 - cmp_mtime($a, $b);
}

/**
 * Compare by file type.
 * @param item $a
 * @param item $b
 * @return int
 */
function cmp_type($a, $b) {
    $a_name = $a['name'];
    $a_dot_pos = strrpos($a_name, '.');
    $a_type = '';
    if ($a_dot_pos !== false) {
        $a_type = substr($a_name, $a_dot_pos + 1, strlen($a_name) - $a_dot_pos - 1);
    }

    $b_name = $b['name'];
    $b_dot_pos = strrpos($b_name, '.');
    $b_type = '';
    if ($b_dot_pos !== false) {
        $b_type = substr($b_name, $b_dot_pos + 1, strlen($b_name) - $b_dot_pos - 1);
    }
    //echo '$a_type, $b_type\n';

    return strcmp($a_type, $b_type);
}

/**
 * Compare by file type, reverse.
 * @param item $a
 * @param item $b
 * @return int
 */
function rcmp_type($a, $b) {
    return 0 - cmp_type($a, $b);
}

?>
