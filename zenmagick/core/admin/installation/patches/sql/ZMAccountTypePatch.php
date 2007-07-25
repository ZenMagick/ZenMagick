<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Patch to create ZenMagick account tpye table and data.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin.installation.patches.sql
 * @version $Id$
 */
class ZMAccountTypePatch extends ZMSQLPatch {

    var $sqlFiles_ = array(
        "etc/sql/account_type.sql"
    );
    var $sqlUndoFiles_ = array(
        "etc/sql/account_type_undo.sql"
    );



    /**
     * Default c'tor.
     */
    function ZMAccountTypePatch() {
        parent::__construct('sqlAccountType');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAccountTypePatch();
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
        return !$this->_isAccountTypeTableExist();
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

            $db = $zm_runtime->getDB();
            // create entries for all existing accounts
            $sql = "select customers_id from " . TABLE_CUSTOMERS;
            $results = $db->Execute($sql);
            $accountIds = array();
            while (!$results->EOF) {
                $accountIds[] = $results->fields['customers_id'];
                $results->MoveNext();
            }
            $sql = "insert into " . ZM_TABLE_ACCOUNT_TYPE . " (account_id, account_type) values ";
            $first = true;
            foreach ($accountIds as $id) {
                if (!$first) $sql .= ",";
                $sql .= "(".$id.", '" . ZM_ACCOUNT_TYPE_REGULAR . "')";
                $first = false;
            }
            $sql .= ";";
            $results = $db->Execute($sql);

            return $status;
        }
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
     * Check for ZenMagick account_type tables.
     *
     * @return bool <code>true</code> if the database contains the account_type table.
     */
    function _isAccountTypeTableExist() {
    global $zm_runtime;

        $db = $zm_runtime->getDB();
        // check for existence
        $results = $db->Execute("show tables");

        $count = 0;
        while (!$results->EOF) {
            $table = array_pop($results->fields);
            if (ZM_TABLE_ACCOUNT_TYPE == $table) {
                return true;
            }
            $results->MoveNext();
        }

        return false;
    }

}

?>
