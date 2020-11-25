        <form id="phpfmSettingForm" class="container" action="<?php echo get_URI(); ?>" method="post">
            <input type="hidden" name="settingsForm" value="settingsForm" />
            <fieldset>
                <legend><?php echo _("User Permission"); ?></legend>
                <div>
                    <label for="roseBrowser"><?php echo _("Browse Permission:"); ?></label>
                    <select id="roseBrowser" name="roseBrowser">
                        <option value="<?php echo User::$NOBODY; ?>" <?php if($settings['rose_browser'] == User::$NOBODY)print("selected='selected'"); ?>>Everyone</option>
                        <option value="<?php echo User::$USER; ?>" <?php if($settings['rose_browser'] == User::$USER)print("selected='selected'"); ?>>User</option>
                        <option value="<?php echo User::$ADMIN; ?>" <?php if($settings['rose_browser'] == User::$ADMIN)print("selected='selected'"); ?>>Administrator</option>
                        <option value="<?php echo User::$ROOT; ?>" <?php if($settings['rose_browser'] == User::$ROOT)print("selected='selected'"); ?>>Root</option>
                    </select>
                    <div class="info">
                        <?php 
                        echo _("Who can browse directories and files.");
                        ?>
                    </div>
                </div>
                <div>
                    <label for="roseModify"><?php echo _("Modification Permission:"); ?></label>
                    <select id="roseModify" name="roseModify">
                        <option value="<?php echo User::$NOBODY; ?>" <?php if($settings['rose_modify'] == User::$NOBODY)print("selected='selected'"); ?>>Everyone</option>
                        <option value="<?php echo User::$USER; ?>" <?php if($settings['rose_modify'] == User::$USER)print("selected='selected'"); ?>>User</option>
                        <option value="<?php echo User::$ADMIN; ?>" <?php if($settings['rose_modify'] == User::$ADMIN)print("selected='selected'"); ?>>Administrator</option>
                        <option value="<?php echo User::$ROOT; ?>" <?php if($settings['rose_modify'] == User::$ROOT)print("selected='selected'"); ?>>Root</option>
                    </select>
                    <div class="info">
                        <?php 
                        echo _("Who can modify (cut/copy, rename, upload, etc.) directories and files.");
                        ?>
                    </div>
                </div>
                <div>
                    <label for="roseAdmin"><?php echo _("Administration Permission:"); ?></label>
                    <select id="roseAdmin" name="roseAdmin">
                        <option value="<?php echo User::$ADMIN; ?>" <?php if($settings['rose_admin'] == User::$ADMIN)print("selected='selected'"); ?>>Administrator</option>
                        <option value="<?php echo User::$ROOT; ?>" <?php if($settings['rose_admin'] == User::$ROOT)print("selected='selected'"); ?>>Root</option>
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
        <div id="phpfmChangePswd">
            <fieldset>
                <legend><?php echo _("Account Management"); ?></legend>
                <div id="divUserMng">
                    <div>
                        <input id="changePswd" type="button" value="<?php echo _("Change Password"); ?>" />
                        <div class="info">
                            <?php 
                            echo _("Change your account password.");
                            ?>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div  id="phpfmUserMng">
            <fieldset>
                <legend><?php echo _("User Management"); ?></legend>
                <div id="divUserMng">
                    <div>
                        <input id="addUser" type="button" value="<?php echo _("Add User"); ?>" /><span id="statUserMng"></span>
                    </div>
                    <table id="tableUserMng" border="0">
                        <tr>
                            <th class="id"><?php echo _("ID"); ?></th><th class="username"><?php echo _("Username"); ?></th><th class="permission"><?php echo _("Permission"); ?></th><th class="operation"><?php echo _("Operation"); ?></th>
                        </tr>
                    </table>
                </div>
            </fieldset>
        </div>