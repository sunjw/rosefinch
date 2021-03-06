        <form id="phpfmSettingForm" class="container" action="<?php echo get_URI(); ?>" method="post">
            <input type="hidden" name="settingsForm" value="settingsForm" />
            <fieldset>
                <legend><?php echo _('Basic Settings'); ?></legend>
                <div>
                    <label for="rootType"><?php echo _('Type of root directory path:'); ?></label>
                    <select id="rootType" name="rootType">
                        <option value="absolute" <?php if ($settings['root_type'] == 'absolute') print('selected="selected"'); ?>><?php echo _('Absolute path'); ?></option>
                        <option value="relative" <?php if ($settings['root_type'] == 'relative') print('selected="selected"'); ?>><?php echo _('Relative path'); ?></option>
                    </select>
                    <div class="info">
                        <?php 
                        echo _('Type of path you entered. "Absolute" means the path is an absolute path in filesystem. "Relative" means the path will be determined from directory of this Rosefinch.');
                        ?>
                    </div>
                </div>
                <div>
                    <label for="rootPath"><?php echo _('Path:'); ?></label>
                    <input id="rootPath" type="text" maxlength="256" value="<?php echo $settings['root_path']; ?>" name="rootPath"/>
                    <div class="info">
                        <?php 
                        echo _('The path of your files which you want to show in this Rosefinch.');
                        ?>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo _('Timezone and Language'); ?></legend>
                <div>
                    <label for="charset"><?php echo _('Charset:'); ?></label>
                    <input id="charset" type="text" maxlength="256" value="<?php echo $settings['charset']; ?>" name="charset"/>
                    <div class="info">
                        <?php 
                        echo _('Charset which operation system running this Rosefinch use in file system. Windows uses local charset like GB2312 and *nixs mostly use UTF-8.');
                        ?>
                    </div>
                </div>
                <div>
                    <label for="timezone"><?php echo _('Timezone:'); ?></label>
                    <select id="timezone" name="timezone">
                        <option value="Asia/Shanghai" <?php if ($settings['timezone'] == 'Asia/Shanghai') print('selected="selected"'); ?>>Asia/Shanghai</option>
                        <option value="America/New_York" <?php if ($settings['timezone'] == 'America/New_York') print('selected="selected"'); ?>>America/New_York</option>
                    </select>
                    <div class="info">
                        <?php 
                        echo _('Time zone server which runs Rosefinch use.');
                        ?>
                    </div>
                </div>
                <div>
                    <label for="language"><?php echo _('Language:'); ?></label>
                    <select id="language" name="language">
                        <option value="en_US" <?php if ($settings['language'] == 'en_US') print('selected="selected"'); ?>>English (US)</option>
                        <option value="zh_CN" <?php if ($settings['language'] == 'zh_CN') print('selected="selected"'); ?>>简体中文</option>
                    </select>
                    <div class="info">
                        <?php 
                        echo _('Language you want to use in this Rosefinch.');
                        ?>
                    </div>
                </div>
            </fieldset>
            <?php 
            if (!(isset($settings['install']) && $settings['install'])) {
            ?>
            <fieldset>
                <legend><?php echo _('User Management'); ?></legend>
                <div>
                    <label for="usermng"><?php echo _('Enable user management:'); ?></label>
                    <select id="usermng" name="usermng">
                        <option value="1" <?php if ($settings['usermng'] == 1) print('selected="selected"'); ?>><?php echo _('Enable'); ?></option>
                        <option value="0" <?php if ($settings['usermng'] == 0) print('selected="selected"'); ?>><?php echo _('Disable'); ?></option>
                    </select>
                    <div class="info">
                        <?php 
                        echo _('If enable user management, you should setup your database.');
                        ?>
                    </div>
                </div>
            </fieldset>
            <?php 
            }
            ?>
            <fieldset>
                <legend><?php echo _('Others Settings'); ?></legend>
                <div>
                    <label for="titleName"><?php echo _('Title:'); ?></label>
                    <input id="titleName" type="text" maxlength="256" value="<?php echo $settings['title_name']; ?>" name="titleName"/>
                    <div class="info">
                        <?php 
                        echo _('Title you want to display in this Rosefinch.');
                        ?>
                    </div>
                </div>
            </fieldset>
            <input type="submit" value="<?php echo _('OK'); ?>" />
        </form>