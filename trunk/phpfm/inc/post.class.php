<?php
require_once "defines.inc.php";
require_once "common.inc.php";
require_once "gettext.inc.php";
require_once "clipboard.class.php";
require_once "messageboard.class.php";
require_once "../log/log.func.php";

class Post
{
	private $files_base_dir;
	private $messageboard;
	private $clipboard;
	private $oper;
	
	function __construct($oper)
	{
		$this->files_base_dir = Utility::get_file_base_dir();//$_SESSION['base_dir'];
		$this->messageboard = Utility::get_messageboard();
		$this->clipboard = Utility::get_clipboard(false);
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
		}
	}
	
	/**
	 * 剪切和复制的操作
	 */
	private function post_cut_copy()
	{
		if($this->clipboard != null)
		{
			$items = post_query("items");
			
			$items = split("[|]", $items);
			$items = Utility::filter_paths($items);
			//print_r($files);
			
			$this->clipboard->set_items($this->oper, $items);
			
			if($this->clipboard->have_items())
			{
				$message = _("Add items to clipboard:") . "&nbsp;<br />";//"向剪贴板添加项目:&nbsp;<br />";
				$message .= (join("<br />", $items));
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
		$items = post_query("items");
		$items = split("[|]", $items);
		$items = Utility::filter_paths($items);
		
		$message = "";
		
		$count = count($items);
		for($i = 0; $i < $count; $i++)
		{
			$success = false;
			$item = $items[$i];
			$path = $this->files_base_dir . $item;
			log_to_file("try to delete: $path");
			$message .= (_("Delete") . " $item ");//("删除 $item ");
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
				$message .= (_("succeed") . "<br />");
				$stat = 1;
			}
			else
			{
				$message .= ("<strong>" . _("failed") . "</strong><br />");
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
		$sub_dir = rawurldecode(post_query("subdir"));
		$name = post_query("newname");
		
		$success = false;
		if(false === strpos($sub_dir, "..") && Utility::check_name($name)) // 过滤
		{
			$name = $this->files_base_dir . $sub_dir . $name;
			log_to_file("mkdir: $name");
			$name = convert_toplat($name);
			if(!file_exists($name))
				$success = @mkdir($name);
		}
		
		if($success === TRUE)
			$this->messageboard->set_message(
				_("Make new folder:") . "&nbsp;" . post_query("newname") . "&nbsp;" . _("succeed"), 
				1);
		else
			$this->messageboard->set_message(
				_("Make new folder:") . "&nbsp;" . post_query("newname") . "&nbsp;<strong>" . _("failed") . "</strong>", 
				2);
		
		
		Utility::redirct_after_oper(false, 1);
	}
	
	/**
	 * 重命名的操作
	 */
	private function post_rename()
	{
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
			$oldname = $this->files_base_dir . $sub_dir . $oldname;
			$newname = $this->files_base_dir . $sub_dir . $newname;
			
			log_to_file("Try to rename: $oldname to $newname");
				
			$success = Utility::phpfm_rename($oldname, $newname, false);
			
		}
		if($success === TRUE)
			$this->messageboard->set_message(
				sprintf(_("Rename %s to %s ") . _("succeed"), post_query("oldname"), post_query("newname")), 
				1);
		else
			$this->messageboard->set_message(
				sprintf(_("Rename %s to %s ") . " <strong>" . _("failed") . "<strong>", post_query("oldname"), post_query("newname")), 
				2);
		
		Utility::redirct_after_oper(false, 1);
	}
	
	/**
	 * 上传的操作
	 */
	private function post_upload()
	{
		$sub_dir = rawurldecode(post_query("subdir"));
		
		if(isset($_FILES['uploadFile']))
		{
			$uploadfile = $this->files_base_dir. $sub_dir . $_FILES['uploadFile']['name'];
			
			if (Utility::phpfm_move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadfile)) {
				$this->messageboard->set_message(
					_("Upload") . ":&nbsp;" . $_FILES['uploadFile']['name'] . "&nbsp;" . _("succeed"),
					1);
				log_to_file("upload success: " . $uploadfile);
			} else {
				$this->messageboard->set_message(
					_("Upload") . ":&nbsp;" . $_FILES['uploadFile']['name'] . " <strong>" . _("failed") . "<strong>",
					2);
				log_to_file("upload failed: " . $uploadfile);
			}
		}

		Utility::redirct_after_oper(false, 1);
	}

}

?>