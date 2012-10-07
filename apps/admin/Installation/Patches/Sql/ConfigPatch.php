<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace ZenMagick\apps\admin\Installation\Patches\Sql;

use ZenMagick\Base\Runtime;
use ZenMagick\apps\admin\Installation\Patches\SQLPatch;


/**
 * Patch to create ZenMagick config basics.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo migrate all this to a fixture for installation
 */
class ConfigPatch extends SQLPatch {
    var $sqlUndoFiles_ = array(
        "/apps/admin/installation/etc/config_undo.sql"
    );


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('sqlConfig');
        $this->label_ = 'Setup ZenMagick config groups and initial values';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        return true;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $configService = Runtime::getContainer()->get('configService');

        // Create configuration groups
        $group = $configService->getConfigGroupForName('ZenMagick Configuration');
        if (null == $group) {
            $group = $configService->createConfigGroup('ZenMagick Configuration', 'ZenMagick Configuration', false);
        }

        $pluginGroup = $configService->getConfigGroupForName('ZenMagick Plugins');
        if (null == $pluginGroup) {
            $pluginGroup = $configService->createConfigGroup('ZenMagick Plugins', 'ZenMagick Plugins', false);
        }
        $groupId = $group->getId();
        $pluginGroupId = $pluginGroup->getId();

        // create configuration values
        if (null == $configService->getConfigValue('ZENMAGICK_CONFIG_GROUP_ID')) {
            $configService->createConfigValue('ZenMagick Configuration Group Id', 'ZENMAGICK_CONFIG_GROUP_ID', $groupId, $groupId);
        }
        if (null == $configService->getConfigValue('ZENMAGICK_PLUGIN_STATUS')) {
            $configService->createConfigValue('ZenMagick Plugin Status', 'ZENMAGICK_PLUGIN_STATUS', '', $groupId);
        }
        if (null == $configService->getConfigValue('ZENMAGICK_PLUGIN_GROUP_ID')) {
            $configService->createConfigValue('ZenMagick Plugins Group Id', 'ZENMAGICK_PLUGIN_GROUP_ID', $pluginGroupId, $groupId);
        }

        $adminDir = $configService->getConfigValue('ZENCART_ADMIN_FOLDER');
        $guessedDir = basename($this->guessZcAdminPath());
        if (null == $adminDir) {
            $configService->createConfigValue('zencart admin folder', 'ZENCART_ADMIN_FOLDER', $guessedDir, $groupId);
            $adminDir = $guessedDir;
            Runtime::getSettings()->set('zencart.admin_dir', $adminDir);
        }
        if ($adminDir != $guessedDir) { // Update
            $configService->updateConfigValue('ZENCART_ADMIN_FOLDER', $guessedDir);
        }

        $sessionGroupId = $configService->getConfigGroupForName('Sessions')->getId();

        if (null == $configService->getConfigValue('SESSION_USE_ROOT_COOKIE_PATH')) {
            $title = 'Use web root path for cookie path';
            $description = 'This setting allows you to set the cookie path to the web root of the domain
            rather than the store directory. It should only be used if you have problems with sessions.<br>
            <strong>You must clear your cookies after changing this setting.</strong>';
            $setFunction = "zen_cfg_select_option(array('True', 'False'),";
            $configService->createConfigValue($title, 'SESSION_USE_ROOT_COOKIE_PATH', 'False', $sessionGroupId, $description, 0, $setFunction);
        }

        if (null == $configService->getConfigValue('SESSION_ADD_PERIOD_PREFIX')) {
            $title = 'Add period prefix to cookie domain';
            $description = 'Normally a period will be added to the cookie domain, (<strong>.www.mydomain.com</strong>).
                This sometimes causes problems with some server configurations. Try setting this to False if you have having session problems.';
            $setFunction = "zen_cfg_select_option(array('True', 'False'),";
            $configService->createConfigValue($title, 'SESSION_ADD_PERIOD_PREFIX', 'True', $sessionGroupId, $description, 0, $setFunction);
        }

        $modulesGroup = $configService->getConfigGroupForName('Module Options')->getId();

        if (null == $configService->getConfigValue('PRODUCTS_OPTIONS_TYPE_SELECT')) {
            $title = 'Product option type Select';
            $description = 'Numeric value of the text product option type.';
            $configService->createConfigValue($title, 'PRODUCTS_OPTIONS_TYPE_SELECT', 0, $modulesGroup, $description);
        }

        if (null == $configService->getConfigValue('TEXT_PREFIX')) {
            $title = 'Text Prefix';
            $description = 'Prefix used to differentiate between text option values and other option values';
            $configService->createConfigValue($title, 'TEXT_PREFIX', 'txt_', $modulesGroup, $description);
        }

        if (null == $configService->getConfigValue('UPLOAD_PREFIX')) {
            $title = 'Upload Prefix';
            $description = 'Prefix used to differentiate between upload option values and other option values';
            $configService->createConfigValue($title, 'UPLOAD_PREFIX', 'upload_', $modulesGroup, $description);
        }

        return true;
    }

}
