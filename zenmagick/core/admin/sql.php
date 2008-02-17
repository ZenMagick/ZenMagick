<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 *
 * $Id$
 */
?>
<?php  

    /**
     * Execute a SQL patch.
     *
     * <p><strong>NOTE:</strong> This functionallity is only available in the context
     * of the ZenMagick installation or plugins page.</p>
     *
     * @package org.zenmagick.admin
     * @param string sql The sql.
     * @param array Result message list.
     * @param boolean Debug flag.
     * @return boolean <code>true</code> for success, <code>false</code> if the execution fails.
     */
    function zm_sql_patch($sql, &$messages, $debug=false) {
        if ($debug) {
            $_GET['debug'] = 'ON';
        }
        $sql = zen_db_prepare_input($sql);
        if (!zm_is_empty($sql)) {
            $results = executeSql($sql, DB_DATABASE, DB_PREFIX);
            foreach (_zm_process_sql_patch_results($results) as $msg) {
                $messages[] = $msg;
            }
            return zm_is_empty($results['error']);
        }

        return true;
    }

    /**
     * Process SQL patch messages.
     *
     * @param array The execution results.
     * @return array The results converted to messages.
     */
    function _zm_process_sql_patch_results($results) {
    global $zm_loader;

        $messages = array();
        if ($results['queries'] > 0 && $results['queries'] != $results['ignored']) {
            array_push($messages, $zm_loader->create("Message", $results['queries'].' statements processed.', 'success'));
        } else {
            array_push($messages, $zm_loader->create("Message", 'Failed: '.$results['queries'].'.', 'error'));
        }

        if (!zm_is_empty($results['errors'])) {
            foreach ($results['errors'] as $value) {
                array_push($messages, $zm_loader->create("Message", 'ERROR: '.$value.'.', 'error'));
            }
        }
        if ($results['ignored'] != 0) {
            array_push($messages, $zm_loader->create("Message", 'Note: '.$results['ignored'].' statements ignored. See "upgrade_exceptions" table for additional details.', 'warn'));
        }

        return $messages;
    }


?>
