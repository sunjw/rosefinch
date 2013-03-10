<div id="phpfmDoc">
	<div id="phpfmDocMainUI">
	   	<a name="MainUI" title="<?php echo _("Main UI"); ?>"></a>
		<a name="Top" title="Top"></a>
	   	<h4>Main UI and Functions</h4>
		<p>
			<a class="lightboxImg" title="help_ui_large.png" href="images/help_ui_large.png">
				<img class="helpLeftImg" alt="Main UI" src="images/help_ui_small.png" />
			</a>
			Main UI of Rosefinche consists of 3 parts: header area which includes title, setting, help and about, folder name and main function area.
		</p><p>
			In main function area, there are current path, toolbar and file list. Current path shows where the folder you are browsing. You can navigate among folders in the path or among folders in the same level from pop-up menu. In toolbar there are various function icons including Forward, Back, Refresh, Up, Cut, Copy, Paste, New Folder, Rename, Delete, Upload, View Mode and Search (need to enable search function). File list can be displayed in "Large Icon View" or "Detail View" mode. You can sorted files by different attributes through clicking the header at top of file list. You can select files and folders using select box at front of file item and then use functions in toolbar (In some cases, a few functions will be disabled). You can click folder item in list to browse it. If you click a file item, browser will <strong>download</strong> this file. And if preview is enabled, when you click a mp3 file or a image file, Rosefinch will pop a <a class="lightboxImg" title="help_preview_pic.png" href="images/help_preview_pic.png">preview</a>.
		</p>
		<p>
			<a href="#Top">Back to top</a>
		</p>
		<div class="clear bottomSplit"></div>
		<p>
			<a class="lightboxImg" title="help_history_large.png" href="images/help_history_large.png">
				<img class="helpRightImg" alt="History" src="images/help_history_large.png" />
			</a>
			First function part: History functions, includes Forward, Back and History Navigator. Clicking Back icon<img alt="Back" src="images/toolbar-back.gif"/>, Rosefinch will go back to last folder you browsed or key word you searched. Clicking Forward icon<img alt="Foward" src="images/toolbar-forward.gif"/>, Rosefinch will go to the folder or search before you clicked Back. Clicking History Navigator<img alt="History Navigator" src="images/toolbar-history.gif"/>, you can go to folder or search in pop-up menu.
		</p>
		<p>
			Second function part includes Refresh and Up. Clicking Refresh icon<img alt="Refresh" src="images/toolbar-refresh.gif"/> will refresh current page. Clicking Up icon<img alt="Up" src="images/toolbar-up.gif"/>, Rosefinch will go to upper level folder.
		</p>
		<p>
			Third function part includes Cut, Copy and Paste. After <a class="lightboxImg" title="help_select.png" href="images/help_select.png">selected file you want to operate</a>, you can click Cut<img alt="Cut" src="images/toolbar-cut.gif"/> or Copy<img alt="Copy" src="images/toolbar-copy.gif"/>, then Rosefinch will record these folders and files and <a class="lightboxImg" title="help_notification.png" href="images/help_notification.png">notify you</a>. When you click Paste<img alt="Paste" src="images/toolbar-paste.gif" />, Rosefinch will <a class="lightboxImg" title="help_working.png" href="images/help_working.png">paste</a> folders and files in clipboard into current folder. Rosefinch will <a class="lightboxImg" title="help_notification_pasted.png" href="images/help_notification_pasted.png">notify you</a> after it finished.
		</p>
		<p>
			<a class="lightboxImg" title="help_upload_large.png" href="images/help_upload_large.png">
				<img class="helpLeftImg" alt="Upload" src="images/help_upload_small.png" />
			</a>
			Fourth function parts includes New Folder, Rename and Delete. Clicking New Folder icon<img alt="New Folder" src="images/toolbar-new-folder.gif"/>, Rosefinch will <a class="lightboxImg" title="help_new_folder.png" href="images/help_new_folder.png">pop a dialog</a>. After you input new folder's name and click OK, Rosefinch will make a folder in current folder. If you want to rename a folder or file, you need to select <strong>one</strong> item in file list and click Rename icon<img alt="Rename" src="images/toolbar-rename.gif"/>. Rosefinch will <a class="lightboxImg" title="help_rename.png" href="images/help_rename.png">pop a dialog</a>. After you input new name and click OK, Rosefinch will rename the folder or file to new name. If you want to delete folders and files, you need to select them and click Delete icon<img alt="Delete" src="images/toolbar-delete.gif"/>. Rosefinch will <a class="lightboxImg" title="help_delete.png" href="images/help_delete.png">pop a dialog</a> to make sure you want to do this. After you click OK, Rosefinch will delete them.
		</p>
		<p>
			Last, Rosefinch provides upload function. Clicking Upload icon<img alt="Upload" src="images/toolbar-upload.gif"/>, Rosefinch will pop a dialog for you to choose file. After you click OK, the file will be <a class="lightboxImg" title="help_working.png" href="images/help_working.png">uploaded</a>.
		</p>
		<p>
			<a href="#Top">Back to top</a>
		</p>
		<div class="clear bottomSplit"></div>
		<p>
			<a class="lightboxImg" title="help_view_diff_large.png" href="images/help_view_diff_large.png">
				<img class="helpRightImg" alt="View Mode" src="images/help_view_diff_small.png" />
			</a>
			There are View Mode and Search (need to enable search function) at right side of toolbar.<img alt="Toolbar" src="images/help_toolbar_right.png"/>
		</p>
		<p>
			Clicking "Large Icon View"<img alt="Large Icon" src="images/view-largeicon.gif"/>, Rosefinch will display current folder in large icon mode. Clicking "Detail View"<img alt="Detail" src="images/view-detail.gif"/>, Rosefinch will display current folder in detail mode.
		</p>
		<p>
			You can search folder and file using search box. Just input keywords and click Search icon<img alt="Search" src="images/toolbar-search.gif"/>. Then Rosefinch will search them in current folder and display result.
		</p>
		<p>
			<a href="#Top">Back to top</a>
		</p>
		<div class="clear"></div>
	</div>
	<div id="phpfmDocSetting">
	   	<a name="Setting" title="<?php echo _("Setting"); ?>"></a>
		<h4>Setting</h4>
		<p>
			<a class="lightboxImg" title="help_setting_large.png" href="images/help_setting_large.png">
				<img class="helpLeftImg" alt="Setting" src="images/help_setting_small.png" />
			</a>
			In the setting page, you can change configurations including Basic Settings, Timezone and Language, Search and Database and Others Settings.
		</p>
		<p>
			Basic Settings. You can change type of root directory path. "Absolute" means the path is an absolute path in filesystem. "Relative" means the path will be determined from directory of this Rosefinch. Also, you can change the path of files which you want to show in this Rosefinch. 
		</p>
		<p>
			Timezone and Language. You can change charset which operation system running this Rosefinch use in file system. Windows uses local charset like GB2312 and *nixs mostly use UTF-8. You can change timezone which the server running Rosefinch uses. Also you can change language you want to use in this Rosefinch.
		</p>
		<p>
			Search and Database. You can enable or disable search function. If you enable it, you need to configure database. In Database Setting page, you need to provide database user name, password, database name and host. And Rosefinch will check them. Clicking Index all files button will make Rosefinch index all folders and files for search. When folder or file information have been changed, Rosefinch will automatic update indexes.
		</p>
		<p>
			Others Settings. You can change title you want to display in this Rosefinch. You can enable or disable LightBox for previewing pictures. Also you can enable or disable Audio Player for previewing mp3 files.
		</p>
		<div class="clear"></div>
	</div>
	<div id="phpfmDocIssue">
	   	<a name="Issue" title="<?php echo _("Knowing Issue"); ?>"></a>
		<h4>Knowing Issue</h4>
		<p>Only running on web server which supports $_SERVER['HTTP_RANGE'] like Apache HTTP Server, can resume pause download.</p>
		<p>When try to close Audio Player on Internet Explorer 8 and 9, bug happens!</p>
	</div>
</div>