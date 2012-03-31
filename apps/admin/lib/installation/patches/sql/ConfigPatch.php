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
namespace zenmagick\apps\store\admin\installation\patches\sql;

use zenmagick\base\Runtime;
use zenmagick\base\Beans;
use zenmagick\apps\store\admin\installation\patches\SQLPatch;


/**
 * Patch to create ZenMagick config basics.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ConfigPatch extends SQLPatch {
    var $sqlUndoFiles_ = array(
        "/shared/etc/sql/mysql/config_undo.sql"
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
            Runtime::getSettings()->set('apps.store.zencart.admindir', $adminDir);
        }
        if ($adminDir != $guessedDir) { // Update
            $configService->updateConfigValue('ZENCART_ADMIN_FOLDER', $guessedDir);
        }

        return true;
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        $baseDir = Runtime::getInstallationPath();
        $status = true;
        foreach ($this->sqlUndoFiles_ as $file) {
            $sql = file($baseDir.$file);
            $status |= $this->_runSQL($sql);
        }
        return $status;
    }
}
