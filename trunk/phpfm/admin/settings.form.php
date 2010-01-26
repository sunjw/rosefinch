        <form id="phpfmInstallForm" action="<?php echo get_URI(); ?>" method="post">
        	<input type="hidden" name="settingsForm" value="settingsForm" />
        	<fieldset>
        		<legend><?php echo _("Basic Settings"); ?></legend>
        		<label for="rootType"><?php echo _("Type of root directory path") . ":"; ?></label>
        		<select id="rootType" name="rootType">
        			<option value="absolute" <?php if($settings['root_type'] == "absolute")print("selected='selected'"); ?>><?php echo _("Absolute path"); ?></option>
        			<option value="relative" <?php if($settings['root_type'] == "relative")print("selected='selected'"); ?>><?php echo _("Relative path"); ?></option>
        		</select>
        		<div class="info">
        		</div>
        		<br />
        		<label for="rootPath"><?php echo _("Path") . ":"; ?></label>
        		<input id="rootPath" type="text" maxlength="256" value="<?php echo $settings['root_path']; ?>" name="rootPath"/>
        		<div class="info">
        		</div>
        		<br />
        	</fieldset>
        	<fieldset>
        		<legend><?php echo _("Timezone and Language"); ?></legend>
        		<label for="charset"><?php echo _("Charset") . ":"; ?></label>
        		<input id="charset" type="text" maxlength="256" value="<?php echo $settings['charset']; ?>" name="charset"/>
        		<div class="info">
        		</div>
        		<br />
        		<label for="timezone"><?php echo _("Timezone") . ":"; ?></label>
        		<select id="timezone" name="timezone">
        			<option value="Asia/Shanghai" <?php if($settings['timezone'] == "Asia/Shanghai")print("selected='selected'"); ?>>Asia/Shanghai</option>
        			<option value="America/New_York" <?php if($settings['timezone'] == "America/New_York")print("selected='selected'"); ?>>America/New_York</option>
        		</select>
        		<div class="info">
        		</div>
        		<br />
        		<label for="language"><?php echo _("Language") . ":"; ?></label>
        		<select id="language" name="language">
        			<option value="en_US" <?php if($settings['language'] == "en_US")print("selected='selected'"); ?>>English (US)</option>
        			<option value="zh_CN" <?php if($settings['language'] == "zh_CN")print("selected='selected'"); ?>>简体中文</option>
        		</select>
        		<div class="info">
        		</div>
        		<br />
        	</fieldset>
        	<fieldset>
        		<legend><?php echo _("Others Settings"); ?></legend>
        		<label for="titleName"><?php echo _("Title") . ":"; ?></label>
        		<input id="titleName" type="text" maxlength="256" value="<?php echo $settings['title_name']; ?>" name="titleName"/>
        		<div class="info">
        		</div>
        		<br />
        		<label for="lightbox"><?php echo _("Enable LightBox for pictures") . ":"; ?></label>
        		<select id="lightbox" name="lightbox">
        			<option value="1" <?php if($settings['lightbox'] == 1)print("selected='selected'"); ?>><?php echo _("Enable"); ?></option>
        			<option value="0" <?php if($settings['lightbox'] == 0)print("selected='selected'"); ?>><?php echo _("Disable"); ?></option>
        		</select>
        		<div class="info">
        		</div>
        		<br />
        	</fieldset>
        	<input type="submit" value="<?php echo _("OK"); ?>" />
        </form>