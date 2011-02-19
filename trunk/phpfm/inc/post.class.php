<?php
require_once "defines.inc.php";
require_once "common.inc.php";
require_once "gettext.inc.php";
require_once "utility.class.php";
require_once "clipboard.class.php";
require_once "messageboard.class.php";
require_once "search.class.php";
require_once "usermng.class.php";
require_once "../log/log.func.php";

/**
 * Search Class
 * 2009-10-7
 * @author Sun Junwen
 *
 */
class Post
{
	private $files_base_dir;
	private $messageboard;
	private $clipboard;
	private $user_manager;
	private $oper;
	
	function __construct($oper)
	{
		$this->files_base_dir = Utility::get_file_base_dir();//$_SESSION['base_dir'];
		$this->messageboard = Utility::get_messageboard();
		$this->clipboard = Utility::get_clipboard(false);
		$this->user_manager = Utility::get_usermng();
		$this->oper = $oper;
	}
	
	/**
	 * 执行操作
	 */
	public function do_oper()
	{
		switch($this->oper)
		{
			case "cut":
			case "copy":
				$this->post_cut_copy();
				break;
			case "delete":
				$this->post_delete();
				break;
			case "newfolder":
				$this->post_newfolder();
				break;
			case "paste":
				$this->post_paste();
				break;
			case "rename":
				$this->post_rename();
				break;
			case "upload":
				$this->post_upload();
				break;
			case "login":
				$this->post_login();
				break;
			case "logout":
				$this->post_logout();
				break;
			case "userlist":
				$this->user_list();
				break;
			case "adduser":
				$this->add_user();
				break;
			case "deluser":
				$this->delete_user();
				break;
			case "modiuser":
				$this->modify_user();
				break;
		}
	}
	
	/**
	 * 剪切和复制的操作
	 */
	private function post_cut_copy()
	{
		if($this->clipboard != null)
		{
			if(!Utility::allow_to_modify())
			{
				$this->messageboard->set_message(_("Please login to cut or copy files."), 2);
				echo "ok";
				return;
			}
			
			$items = post_query("items");
			
			$items = explode("|", $items);
			$items = Utility::filter_paths($items);
			//print_r($files);
			
			$this->clipboard->set_items($this->oper, $items);
			
			if($this->clipboard->have_items())
			{
				$message = _("Add items to clipboard:")."&nbsp;<br />";//"向剪贴板添加项目:&nbsp;<br />";
				$message .= htmlentities_utf8((join("***", $items)));
				$message = str_replace("***", "<br />", $message);
				$this->messageboard->set_message($message);
				echo "ok";
			}
		}
	}

	/**
	 * 删除的操作
	 */
	private function post_delete()
	{
		if(!Utility::allow_to_modify())
		{
			$this->messageboard->set_message(_("Please login to delete files."), 2);
			echo "ok";
			return;
		}
		
		$items = post_query("items");
		$items = explode("|", $items);
		$items = Utility::filter_paths($items);
		
		$search = null;
		if(SEARCH)
		{
			$search = new Search();
		}
		
		$message = "";
		
		$count = count($items);
		for($i = 0; $i < $count; $i++)
		{
			$success = false;
			$item = $items[$i];
			$sub_dir = dirname($item);
			$path = $this->files_base_dir.$item;
			log_to_file("try to delete: $path");
			$message .= (_("Delete")." ".htmlentities_utf8($item)." ");//("删除 $item ");
			$path = convert_toplat($path);
			if(file_exists($path))
			{
				if(is_dir($path))
				{
					$success = Utility::phpfm_rmdir($path);
				}
				else
				{
					$success = @unlink($path);
				}
			}
			if($success === TRUE)
			{
				$message .= (_("succeed")."<br />");
				$stat = 1;
				
				if(SEARCH)
				{
					$search->create_index($sub_dir);
				}
			}
			else
			{
				$message .= ("<strong>"._("failed")."</strong><br />");
				$stat = 2;
			}
		}
		
		$this->messageboard->set_message($message, $stat);
		
		echo "ok";
	}
	
	/**
	 * 粘贴的操作
	 */
	private function post_paste()
	{
		if(!Utility::allow_to_modify())
		{
			$this->messageboard->set_message(_("Please login to paste files."), 2);
			echo "ok";
			return;
		}
		$target_subdir = rawurldecode(post_query("subdir"));

		if($this->clipboard != null)
		{
			$this->clipboard->paste($target_subdir);
		}
		
		//print_r($_GET);
		
		echo "ok";
	}
	
	/**
	 * 新建目录的操作
	 */
	private function post_newfolder()
	{
		if(!Utility::allow_to_modify())
		{
			$this->messageboard->set_message(_("Please login to make a new folder."), 2);
			Utility::redirct_after_oper(false, 1);
		}
		
		$search = null;
		if(SEARCH)
		{
			$search = new Search();
		}
		
		$sub_dir = rawurldecode(post_query("subdir"));
		$name = post_query("newname");
		
		$success = false;
		if(false === strpos($sub_dir, "..") && Utility::check_name($name)) // 过滤
		{
			$name = $this->files_base_dir.$sub_dir.$name;
			log_to_file("mkdir: $name");
			$name = convert_toplat($name);
			if(!file_exists($name))
				$success = @mkdir($name);
		}
		
		if($success === TRUE)
		{
			$this->messageboard->set_message(
				_("Make new folder:")."&nbsp;".htmlentities_utf8(post_query("newname"))."&nbsp;"._("succeed"), 
				1);
				
			if(SEARCH)
			{
				$search->create_index($sub_dir);
			}
		}
		else
		{
			$this->messageboard->set_message(
				_("Make new folder:")."&nbsp;".htmlentities_utf8(post_query("newname"))."&nbsp;<strong>"._("failed")."</strong>", 
				2);
		}
		
		
		Utility::redirct_after_oper(false, 1);
	}
	
	/**
	 * 重命名的操作
	 */
	private function post_rename()
	{
		if(!Utility::allow_to_modify())
		{
			$this->messageboard->set_message(_("Please login to rename file."), 2);
			Utility::redirct_after_oper(false, 1);
		}
		
		$search = null;
		if(SEARCH)
		{
			$search = new Search();
		}
		
		//$sub_dir = rawurldecode(post_query("subdir"));
		$oldpath = post_query("renamePath");
		$sub_dir = "";
		if(strrpos($oldpath, "/") != false)
		{
			$sub_dir = substr($oldpath, 0, strrpos($oldpath, "/") + 1);
		}
		
		$oldname = post_query("oldname");
		$newname = post_query("newname");
		
		$success = false;
		if(false === strpos($sub_dir, "..") &&
			Utility::check_name($newname) && Utility::check_name($oldname)) // 过滤
		{
			$oldname = $this->files_base_dir.$sub_dir.$oldname;
			$newname = $this->files_base_dir.$sub_dir.$newname;
			
			log_to_file("Try to rename: $oldname to $newname");
				
			$success = Utility::phpfm_rename($oldname, $newname, false);
			
		}
		if($success === TRUE)
		{
			$this->messageboard->set_message(
				sprintf(_("Rename %s to %s ")._("succeed"), htmlentities_utf8(post_query("oldname")), htmlentities_utf8(post_query("newname"))), 
				1);
				
			if(SEARCH)
			{
				$search->create_index($sub_dir);
			}
		}
		else
		{
			$this->messageboard->set_message(
				sprintf(_("Rename %s to %s ")." <strong>"._("failed")."<strong>", htmlentities_utf8(post_query("oldname")), htmlentities_utf8(post_query("newname"))), 
				2);
		}
		
		Utility::redirct_after_oper(false, 1);
	}
	
	/**
	 * 上传的操作
	 */
	private function post_upload()
	{
		if(!Utility::allow_to_modify())
		{
			$this->messageboard->set_message(_("Please login to upload file."), 2);
			Utility::redirct_after_oper(false, 1);
		}
		
		$search = null;
		if(SEARCH)
		{
			$search = new Search();
		}
		
		$sub_dir = rawurldecode(post_query("subdir"));
		
		if(isset($_FILES['uploadFile']))
		{
			$uploadfile = $this->files_base_dir. $sub_dir.$_FILES['uploadFile']['name'];
			
			if (Utility::phpfm_move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadfile)) {
				$this->messageboard->set_message(
					_("Upload").":&nbsp;".$_FILES['uploadFile']['name']."&nbsp;"._("succeed"),
					1);
				log_to_file("upload success: ".$uploadfile);
				
				if(SEARCH)
				{
					$search->create_index($sub_dir);
				}
				
			} else {
				$this->messageboard->set_message(
					_("Upload").":&nbsp;".$_FILES['uploadFile']['name']." <strong>"._("failed")."<strong>",
					2);
				log_to_file("upload failed: ".$uploadfile);
			}
		}

		Utility::redirct_after_oper(false, 1);
	}
	
	/**
	 * 登录
	 */
	private function post_login()
	{
		$cert = Array();
		$cert['username'] = post_query('username');
		$cert['password'] = post_query('password');
		$this->user_manager->login($cert);
		
		if($this->user_manager->is_logged())
			$this->messageboard->set_message(_("Login succeed."), 1);
		else
			$this->messageboard->set_message(_("Login failed."), 2);
		
		Utility::redirct_after_oper(false, 1);
	}
	
	/**
	 * 登出
	 */
	private function post_logout()
	{
		$this->user_manager->logout();
		$this->messageboard->set_message(_("Logout."), 1);
		
		Utility::redirct_after_oper(false, 1);
	}
	
	private function check_privilege($target_privilege)
	{
		$user = $this->user_manager->get_user();
		$privilege = $user->privilege;
		return (is_numeric($target_privilege) && $privilege >= $target_privilege);
	}
	
	/**
	 * 列出用户
	 * 当前用户必须是可管理的，并且只列出当前级别或更低的用户
	 */
	private function user_list()
	{
		$ret = array();
		
		if(Utility::allow_to_admin())
		{
			$user = $this->user_manager->get_user();
			$privilege = $user->privilege;
			//print_r($user);
			
			$ret = $this->user_manager->get_users_by_privilege($privilege, true);
		}
		
		echo json_encode($ret);
	}
	
	/**
	 * 添加用户
	 * 只能添加比当前用户级别同等或更低的
	 */
	private function add_user()
	{
		if(Utility::allow_to_admin() && $this->check_privilege(post_query("privilege")))
		{
			$info = Array('username' => post_query("username"),
						'password' => md5(post_query("password")),
						'privilege' => post_query("privilege"));
			$result = $this->user_manager->add_user($info);
			if($result)
			{
				$this->messageboard->set_message(_("Adding user succeed"), 1);
			}
			else
			{
				$this->messageboard->set_message(_("Adding user failed"), 2);
			}
		}
		else
			$this->messageboard->set_message(_("Adding user failed"), 2);
		
		Utility::redirct_after_oper(false, 1);
	}
	
	/**
	 * 删除用户
	 * 只能删除比当前用户级别同等或更低的
	 */
	private function delete_user()
	{
		if(Utility::allow_to_admin())
		{
			$del_id = post_query("id");
			if(is_numeric($del_id))
			{
				$user = $this->user_manager->get_user_by_id($del_id);
				//print_r($user[0]);
				if($this->check_privilege($user[0]->privilege))
				{
					if($this->user_manager->delete_user($del_id))
					{
						$this->messageboard->set_message(_("Deleting user succeed"), 1);
						Utility::redirct_after_oper(false, 1);
						return;
					}
				}
			}
		}
		
		$this->messageboard->set_message(_("Deleting user failed"), 2);
		Utility::redirct_after_oper(false, 1);
	}
	
	/**
	 * 修改用户
	 * 只能修改比当前用户级别同等或更低的
	 */
	private function modify_user()
	{
		if(Utility::allow_to_admin())
		{
			$modify_id = post_query("id");
			if(is_numeric($modify_id))
			{
				if($this->check_privilege(post_query("privilege")))
				{
					$info = Array('id' => $modify_id,
								'username' => post_query("username"),
								'privilege' => post_query("privilege"));
					if($this->user_manager->modify_user($info))
					{
						$this->messageboard->set_message(_("Modifing user succeed"), 1);
						Utility::redirct_after_oper(false, 1);
						return;
					}
				}
			}
		}
		
		$this->messageboard->set_message(_("Modifing user failed"), 2);
		Utility::redirct_after_oper(false, 1);
	}

}

?>