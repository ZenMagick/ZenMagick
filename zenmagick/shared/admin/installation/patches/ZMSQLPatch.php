<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Generic SQL patch.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.installation.patches
 * @version $Id$
 */
class ZMSQLPatch extends ZMInstallationPatch {

    /**
     * Create new patch.
     *
     * @param string id Id of the patch.
     */
    function __construct($id) {
        parent::__construct($id);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    function getGroupId() {
        return 'sql';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return "";
    }

    /**
     * Execute the given SQL.
     *
     * @param string sql Some sql.
     */
    function _runSQL($sql) {
        $sql = ZMSecurityUtils::sanitize($sql);
        if (!empty($sql)) {
            $results = zm_zen_execute_sql($sql, DB_DATABASE, DB_PREFIX);
            $this->_processSQLMessages($results);
            return empty($results['error']);
        }

        return true;
    }

    /**
     * Process messages.
     */
    function _processSQLMessages($results) {
        if ($results['queries'] > 0 && $results['queries'] != $results['ignored']) {
            array_push($this->messages_, ZMLoader::make("Message", $results['queries'].' statements processed.', 'success'));
        } else {
            array_push($this->messages_, ZMLoader::make("Message", 'Failed: '.$results['queries'].'.', 'error'));
        }

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $value) {
                array_push($this->messages_, ZMLoader::make("Message", 'ERROR: '.$value.'.', 'error'));
            }
        }
        if ($results['ignored'] != 0) {
            array_push($this->messages_, ZMLoader::make("Message", 'Note: '.$results['ignored'].' statements ignored. See "upgrade_exceptions" table for additional details.', 'warn'));
        }
    }

}
