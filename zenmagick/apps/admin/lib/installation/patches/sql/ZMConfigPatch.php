<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
?>
<?php


/**
 * Patch to create ZenMagick config basics.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.installation.patches.sql
 */
class ZMConfigPatch extends ZMSQLPatch {
    var $sqlFiles_ = array(
        "shared/etc/sql/mysql/config.sql"
    );
    var $sqlUndoFiles_ = array(
        "shared/etc/sql/mysql/config_undo.sql"
    );


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('sqlConfig');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        return !defined('ZENMAGICK_CONFIG_GROUP_ID');
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $baseDir = Runtime::getInstallationPath();
        // do only interactive
        if ($force) {
            $status = true;
            foreach ($this->sqlFiles_ as $file) {
                $sql = file($baseDir.$file);
                $status |= $this->_runSQL($sql);
            }
            return $status;
        }

        return true;
    }
    
    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        if ($this->isOpen()) {
            return true;
        }

        $baseDir = Runtime::getInstallationPath();
        $status = true;
        foreach ($this->sqlUndoFiles_ as $file) {
            $sql = file($baseDir.$file);
            $status |= $this->_runSQL($sql);
        }
        return $status;
    }
    
}
