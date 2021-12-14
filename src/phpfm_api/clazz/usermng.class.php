<?php

require_once dirname(__FILE__) . '/../inc/defines.inc.php';
require_once dirname(__FILE__) . '/../inc/common.inc.php';
require_once 'utility.class.php';

/**
 * User Class
 * 2011-02-14
 * @author Sun Junwen
 *
 */
class User {
    /**
     * ID
     */
    public $id;
    /**
     * 用户名
     */
    public $name;
    /**
     * 权限
     */
    public $permission;

    /**
     * 最低权限
     */
    public static $NOBODY = 0;
    /**
     * 一般用户
     */
    public static $USER = 25;
    /**
     * 管理员权限
     */
    public static $ADMIN = 75;
    /**
     * 最高权限
     */
    public static $ROOT = 100;
}

/**
 * User Manager Class
 * 2011-02-14 A valentine's day
 * @author Sun Junwen
 *
 */
class UserManager {
    function __construct() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['user'] = null;
        }
    }

    public function debug() {
        $this->is_logged() ? print_r($_SESSION['user']) : print_r('Nobody');
    }

    /**
     * 用户是否登录
     * @return true, 登录了；false, 没有
     */
    public function is_logged() {
        return $_SESSION['user'] != null;
    }

    /**
     * 得到当前登录的用户对象
     * @return 用户对象或者 null
     */
    public function get_user() {
        return $_SESSION['user'];
    }

    /**
     * 登录
     * @param $cert 认证参数
     * @return get_user()
     */
    public function login($cert) {
        $cert['username'] = $this->db->escape($cert['username']);
        $cert['password'] = md5($cert['password']);

//        if (count($row) == 1) {
//            $row = $row[0];
//            $user = new User();
//            $user->id = $row->id;
//            $user->name = $row->username;
//            $user->permission = $row->permission;
//            $_SESSION['user'] = $user;
//        }

        return $this->get_user();
    }

    public function logout() {
        $_SESSION['user'] = null;
    }

    public function get_users_by_permission($permission, $permission_str = false) {
        if (!is_numeric($permission)) {
            return null;
        }

//        if ($permission_str) {
//            foreach ($rows as $row) {
//                if ($row->permission == User::$ROOT)
//                    $row->permission = 'Root';
//                else if ($row->permission == User::$ADMIN)
//                    $row->permission = 'Administrator';
//                else if ($row->permission == User::$USER)
//                    $row->permission = 'User';
//                else if ($row->permission == User::$NOBODY)
//                    $row->permission = 'Everyone';
//            }
//        }

        return null; //$rows;
    }

    public function get_user_by_id($id, $permission_str = false) {
        if (!is_numeric($id)) {
            return null;
        }

//        if ($permission_str) {
//            if ($row->permission == User::$ROOT)
//                $row->permission = 'Root';
//            else if ($row->permission == User::$ADMIN)
//                $row->permission = 'Administrator';
//            else if ($row->permission == User::$USER)
//                $row->permission = 'User';
//            else if ($row->permission == User::$NOBODY)
//                $row->permission = 'Everyone';
//        }

        return null; //$row;
    }

    public function add_user($info) {
        $info['username'] = $this->db->escape($info['username']);
        $info['password'] = $this->db->escape($info['password']);
        $info['permission'] = $this->db->escape($info['permission']);
        if ($info['permission'] < User::$USER) {
            return 0;
        }

        $query = 'INSERT INTO `users` SET username=\'' . $info['username'] . '\', password=\'' . $info['password'] . '\', permission=' . $info['permission'];
        return ($this->db->query($query));
        //print_r($result);
    }

    public function delete_user($id) {
        $id = $this->db->escape($id);
        $query = 'DELETE FROM `users` WHERE id=\'' . $id . '\' AND username<>\'root\'';
        //echo $query;
        return ($this->db->query($query));
    }

    public function modify_user($info) {
        $info['id'] = $this->db->escape($info['id']);
        $info['username'] = $this->db->escape($info['username']);
        $info['permission'] = $this->db->escape($info['permission']);
        if ($info['permission'] < User::$USER) {
            return 0;
        }

        $query = 'UPDATE `users` SET username=\'' . $info['username'] . '\', permission=' . $info['permission'] . ' WHERE id=\'' . $info['id'] . '\' AND username<>\'root\'';
        return ($this->db->query($query));
        //print_r($result);
    }

    public function change_password($info) {
        $current_user = $this->get_user();
        $id = $current_user->id;
        $query = 'SELECT * FROM `users` WHERE id=\'' . $id . '\' AND password=\'' . $info['old'] . '\'';

        $row = $this->db->get_results($query);
        if (count($row)) {
            $query = 'UPDATE `users` SET password=\'' . $info['new'] . '\' WHERE id=\'' . $id . '\'';
            return ($this->db->query($query));
        }
    }
}

?>