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
}

?>