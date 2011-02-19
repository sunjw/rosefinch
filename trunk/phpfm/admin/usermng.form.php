        <form id="phpfmSettingForm" class="container" action="<?php echo get_URI(); ?>" method="post">
        	<input type="hidden" name="settingsForm" value="settingsForm" />
        	<fieldset>
        		<legend><?php echo _("User Privilege"); ?></legend>
        		<div>
	        		<label for="roseBrowser"><?php echo _("Browsing Privilege") . ":"; ?></label>
	        		<select id="roseBrowser" name="roseBrowser">
	        			<option value="<?php echo User::$NOBODY; ?>" <?php if($settings['rose_browser'] == User::$NOBODY)print("selected='selected'"); ?>><?php echo _("Everyone"); ?></option>
	        			<option value="<?php echo User::$USER; ?>" <?php if($settings['rose_browser'] == User::$USER)print("selected='selected'"); ?>><?php echo _("User"); ?></option>
	        			<option value="<?php echo User::$ADMIN; ?>" <?php if($settings['rose_browser'] == User::$ADMIN)print("selected='selected'"); ?>><?php echo _("Administrator"); ?></option>
	        			<option value="<?php echo User::$ROOT; ?>" <?php if($settings['rose_browser'] == User::$ROOT)print("selected='selected'"); ?>><?php echo _("Root"); ?></option>
	        		</select>
	        		<div class="info">
	        			<?php 
	        			echo _("Who can browse directories and files.");
	        			?>
	        		</div>
        		</div>
        		<div>
	        		<label for="roseModify"><?php echo _("Modifying Privilege") . ":"; ?></label>
	        		<select id="roseModify" name="roseModify">
	        			<option value="<?php echo User::$NOBODY; ?>" <?php if($settings['rose_modify'] == User::$NOBODY)print("selected='selected'"); ?>><?php echo _("Everyone"); ?></option>
	        			<option value="<?php echo User::$USER; ?>" <?php if($settings['rose_modify'] == User::$USER)print("selected='selected'"); ?>><?php echo _("User"); ?></option>
	        			<option value="<?php echo User::$ADMIN; ?>" <?php if($settings['rose_modify'] == User::$ADMIN)print("selected='selected'"); ?>><?php echo _("Administrator"); ?></option>
	        			<option value="<?php echo User::$ROOT; ?>" <?php if($settings['rose_modify'] == User::$ROOT)print("selected='selected'"); ?>><?php echo _("Root"); ?></option>
	        		</select>
	        		<div class="info">
	        			<?php 
	        			echo _("Who can modify (cut/copy, rename, upload, etc.) directories and files.");
	        			?>
	        		</div>
        		</div>
        		<div>
	        		<label for="roseAdmin"><?php echo _("Administrating Privilege") . ":"; ?></label>
	        		<select id="roseAdmin" name="roseAdmin">
	        			<option value="<?php echo User::$ADMIN; ?>" <?php if($settings['rose_admin'] == User::$ADMIN)print("selected='selected'"); ?>><?php echo _("Administrator"); ?></option>
	        			<option value="<?php echo User::$ROOT; ?>" <?php if($settings['rose_admin'] == User::$ROOT)print("selected='selected'"); ?>><?php echo _("Root"); ?></option>
	        		</select>
	        		<div class="info">
	        			<?php 
	        			echo _("Who can administrate Rosefinch.");
	        			?>
	        		</div>
        		</div>
        	</fieldset>
        	<input type="submit" value="<?php echo _("OK"); ?>" />
        </form>
        <fieldset id="phpfmUserMng">
        	<legend><?php echo _("User Management"); ?></legend>
        	<div id="divUserMng">
        		<div>
        			<input id="addUser" type="button" value="<?php echo _("Add User"); ?>" /><span id="statUserMng"></span>
        		</div>
		       	<table id="tableUserMng" border="0">
		       		<tr>
						<th class="id"><?php echo _("ID"); ?></th><th class="name"><?php echo _("Name"); ?></th><th class="privilege"><?php echo _("Privilege"); ?></th><th class="operation"><?php echo _("Operation"); ?></th>
					</tr>
		       	</table>
	       	</div>
       </fieldset>