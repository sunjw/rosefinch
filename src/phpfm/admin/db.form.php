<form id="phpfmSettingForm" action="<?php echo get_URI(); ?>" method="post">
	<input type="hidden" name="settingsForm" value="settingsForm" />
	<fieldset>
		<legend><?php echo _("Basic Settings"); ?></legend>
		<div>
			<label for="dbUser"><?php echo _("Database user name:"); ?></label>
			<input id="dbUser" type="text" maxlength="256" value="<?php echo $settings['db_user']; ?>" name="dbUser"/>
			<div class="info">
			</div>
		</div>
		<div>
			<label for="dbPswd"><?php echo _("Database user password:"); ?></label>
			<input id="dbPswd" type="password" maxlength="256" value="" name="dbPswd"/>
			<div class="info">
			</div>
		</div>
		<div>
			<label for="dbName"><?php echo _("Database name:"); ?></label>
			<input id="dbName" type="text" maxlength="256" value="<?php echo $settings['db_name']; ?>" name="dbName"/>
			<div class="info">
			</div>
		</div>
		<div>
			<label for="dbHost"><?php echo _("Database host:"); ?></label>
			<input id="dbHost" type="text" maxlength="256" value="<?php echo $settings['db_host']; ?>" name="dbHost"/>
			<div class="info">
			</div>
	   	</div>
		<div>
			<label for="rootPassword"><?php echo _("Root user password:"); ?></label>
			<input id="rootPassword" type="password" maxlength="256" value="" name="rootPassword"/>
			<div class="info">
			</div>
		</div>
	</fieldset>
	<input type="submit" value="<?php echo _("OK"); ?>" />
</form>