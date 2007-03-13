<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Patch to create ZenMagick features database tables.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches.sql
 * @version $Id$
 */
class ZMFeaturesPatch extends ZMSQLPatch {
    var $tables_ = array(
       ZM_TABLE_FEATURE_TYPES => ZM_TABLE_FEATURE_TYPES,
       ZM_TABLE_PRODUCT_FEATURES => ZM_TABLE_PRODUCT_FEATURES,
       ZM_TABLE_FEATURES => ZM_TABLE_FEATURES,
    );

    var $sqlFiles_ = array(
        "etc/sql/features.sql"
    );
    var $sqlUndoFiles_ = array(
        "etc/sql/features_undo.sql"
    );



    /**
     * Default c'tor.
     */
    function ZMFeaturesPatch() {
        parent::__construct('sqlFeatures');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMFeaturesPatch();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return bool <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        return !$this->_isFeatureTablesExist();
    }

    /**
     * Execute this patch.
     *
     * @param bool force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
    global $zm_runtime;

        $baseDir = $zm_runtime->getZMRootPath();
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
     * @return bool <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
    global $zm_runtime;

        if ($this->isOpen()) {
            return true;
        }

        $baseDir = $zm_runtime->getZMRootPath();
        $status = true;
        foreach ($this->sqlUndoFiles_ as $file) {
            $sql = file($baseDir.$file);
            $status |= $this->_runSQL($sql);
        }
        return $status;
    }
    
    /**
     * Check for ZenMagick feature tables.
     *
     * @return bool <code>true</code> if the database contains all ZenMagick tables.
     */
    function _isFeatureTablesExist() {
    global $zm_runtime;

        $db = $zm_runtime->getDB();
        // check for existence
        $results = $db->Execute("show tables");

        $count = 0;
        while (!$results->EOF) {
            $table = array_pop($results->fields);
            if (array_key_exists($table, $this->tables_)) {
                $count++;
            }
            $results->MoveNext();
        }

        return $count == count($this->tables_);
    }

}

?>
