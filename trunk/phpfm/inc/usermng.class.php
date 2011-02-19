<?php

require_once "defines.inc.php";
require_once "common.inc.php";
require_once "utility.class.php";

/**
 * User Class
 * 2011-02-14
 * @author Sun Junwen
 *
 */
class User
{
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
	public $privilege;
	
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
class UserManager
{
	private $db;
	
	function __construct()
	{
		if(!isset($_SESSION['user']))
			$_SESSION['user'] = null;
			
		$this->db = Utility::get_ezMysql();
	}
	
	public function debug()
	{
		$this->is_logged() ? print_r($_SESSION['user']) : print_r("Nobody");
	}
	
	private function check_db()
	{
		if($this->db == null)
			return false;
		
		$query = "SHOW TABLES";
		$rows = $this->db->get_results($query, ARRAY_N);
		//print_r($rows);
		if($rows == null)
			return false;
			
		foreach($rows as $row)
		{
			//echo $row[0];
			if($row[0] == "users")
				return true;
		}
		
		return false;
	}
	
	/**
	 * 用户是否登录
	 * @return true, 登录了；false, 没有
	 */
	public function is_logged()
	{
		return $_SESSION['user'] != null;
	}
	
	/**
	 * 得到用户对象
	 * @return 用户对象或者 null
	 */
	public function get_user()
	{
		return $_SESSION['user'];
	}
	
	/**
	 * 登录
	 * @param $cert 认证参数
	 * @return get_user()
	 */
	public function login($cert)
	{
		if(!$this->check_db())
			return null;
		
		$cert['username'] = $this->db->escape($cert['username']);
		$cert['password'] = md5($cert['password']);
		echo $query;
		$query = "SELECT * FROM `users` WHERE username='".$cert['username']."' AND password='".$cert['password']."'";
		
		$row = $this->db->get_results($query);
		if(count($row) == 1)
		{
			$row = $row[0];
			$user = new User();
			$user->id = $row->id;
			$user->name = $row->username;
			$user->privilege = $row->privilege;
			$_SESSION['user'] = $user;
		}
		
		return $this->get_user();
	}
	
	public function logout()
	{
		$_SESSION['user'] = null;
	}
	
	public function get_users_by_privilege($privilege, $privilege_str = false)
	{
		if(!is_numeric($privilege))
			return null;
		
		$query = "SELECT id, username, privilege FROM `users` WHERE privilege<='".$privilege."'";
		//echo $query;
		$rows = $this->db->get_results($query);
		
		if($privilege_str)
		{
			foreach($rows as $row)
			{
				if($row->privilege == User::$ROOT)
					$row->privilege = _("Root");
				else if($row->privilege == User::$ADMIN)
					$row->privilege = _("Administrator");
				else if($row->privilege == User::$USER)
					$row->privilege = _("User");
				else if($row->privilege == User::$NOBODY)
					$row->privilege = _("Everyone");
			}
		}
		
		return $rows;
	}
	
	public function get_user_by_id($id, $privilege_str = false)
	{
		if(!is_numeric($id))
			return null;
		
		$query = "SELECT id, username, privilege FROM `users` WHERE id='".$id."'";
		//echo $query;
		$row = $this->db->get_results($query);
		
		if($privilege_str)
		{
			if($row->privilege == User::$ROOT)
				$row->privilege = _("Root");
			else if($row->privilege == User::$ADMIN)
				$row->privilege = _("Administrator");
			else if($row->privilege == User::$USER)
				$row->privilege = _("User");
			else if($row->privilege == User::$NOBODY)
				$row->privilege = _("Everyone");
		}
		
		return $row;
	}
	
	public function add_user($info)
	{
		$info['username'] = $this->db->escape($info['username']);
		$info['password'] = $this->db->escape($info['password']);
		$info['privilege'] = $this->db->escape($info['privilege']);
		if($info['privilege'] < User::$USER)
			return 0;
		
		$query = "INSERT INTO `users` SET username='".$info['username']."', password='".$info['password']."', privilege=".$info['privilege'];
		return ($this->db->query($query));
		//print_r($result);
	}
	
	public function delete_user($id)
	{
		$id = $this->db->escape($id);
		$query = "DELETE FROM `users` WHERE id='$id' AND username<>'root'";
		//echo $query;
		return ($this->db->query($query));
	}
	
	public function modify_user($info)
	{
		$info['id'] = $this->db->escape($info['id']);
		$info['username'] = $this->db->escape($info['username']);
		$info['privilege'] = $this->db->escape($info['privilege']);
		if($info['privilege'] < User::$USER)
			return 0;
		
		$query = "UPDATE `users` SET username='".$info['username']."', privilege=".$info['privilege']." WHERE id='".$info['id']."' AND username<>'root'";
		return ($this->db->query($query));
		//print_r($result);
	}
}

?>