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
 */
?>
<?php


/**
 * SQL/database utils.
 *
 * @author DerManoMann
 * @package org.zenmagick.database
 * @version $Id$
 */
class ZMDbUtils {

    /**
     * Bind a list of values to a given SQL query.
     *
     * <p>Converts the values in the given array into a comma separated list of the specified type.</p>
     *
     * @param string sql The sql query to work on.
     * @param string bindName The name to bind the list to.
     * @param array values An array of values.
     * @param string type The value type; default is 'string'
     * @return string The sql with <code>$bindName</code> replaced with a properly formatted value list.
     */
    public static function bindValueList($sql, $bindName, $values, $type='string') {
        $db = ZMRuntime::getDB();
        $fragment = '';
        foreach ($values as $value) {
            if ('' != $fragment) $fragment .= ', ';
            $fragment .= $db->bindVars(":value", ":value", $value, $type);
        }

        return $db->bindVars($sql, $bindName, $fragment, 'passthru');
    }

    /**
     * Bind object to a given SQL query.
     *
     * <p>This is based on introspection/reflection on the given object and the available
     * <code>getXXX()</code> or <code>isXXX()</code> methods.</p>
     * <p>SQL label must follow the listed convenctions:</p>
     * <ul>
     *  <li>label start with the prefix '<code>:</code>'</li>
     *  <li>label match the objetcs <code>getXXX()</code> method excl the <code>get</code> prefix</li>
     *  <li>label are suffixed with the data type with a semicolon '<code>;</code>' as separator</li>
     * </ul>
     *
     * <p>Examples:</p>
     * <ul>
     *  <li><code>:firstName;string</code> - maps to the <code>getFirstName()</code> method; data type string</li>
     *  <li><code>:dob;date</code> - maps to the <code>getDob()</code> method; data type date</li>
     *  <li><code>:newsletterSubscriber;integer</code> - maps to the <code>isNewsletterSubscriber()</code> method; data type integer</li>
     * </ul>
     *
     * @param string sql The sql to work on.
     * @param mixed obj The data object instance.
     * @return string The updated SQL query.
     */
    public static function bindObject($sql, $obj) {
        // prepare label
        preg_match_all('/:\w+;\w+/m', $sql, $matches);
        $labels = array();
        foreach ($matches[0] as $name) {
            $label = explode(';', $name);
            $labels[str_replace(':', '', $label[0])] = array($name, $label[1]);
        }

        $data = ZMBeanUtils::obj2map($obj, array_keys($labels));

        $db = ZMRuntime::getDB();
        foreach ($labels as $property => $info) {
            $value = null;
            if (array_key_exists($property, $data)) {
                $value = $data[$property];
            }
            
            // bind
            if ('date' == $info[1]) {
                // if not empty nothing, otherwise assume NULL
                if (empty($value)) {
                    $value = ZM_DB_NULL_DATETIME;
                    $info[1] = 'date';
                }
            }
            $sql = $db->bindVars($sql, $info[0], $value, $info[1]);
        }

        return $sql;
    }

    /**
     * Execute a SQL patch.
     *
     * <p><strong>NOTE:</strong> This functionallity is only available in the context
     * of the ZenMagick installation or plugins page.</p>
     *
     * @param string sql The sql.
     * @param array Result message list.
     * @param boolean Debug flag.
     * @return boolean <code>true</code> for success, <code>false</code> if the execution fails.
     */
    public static function executePatch($sql, $messages, $debug=false) {
        if (!ZMSettings::get('isAdmin')) {
            return false;
        }

        if ($debug) {
            $_GET['debug'] = 'ON';
        }
        $sql = ZMTools::sanitize($sql);
        if (!empty($sql)) {
            $results = executeSql($sql, DB_DATABASE, DB_PREFIX);
            foreach (ZMDbUtils::processPatchResults($results) as $msg) {
                $messages[] = $msg;
            }
            return empty($results['error']);
        }

        return true;
    }

    /**
     * Process SQL patch messages.
     *
     * @param array The execution results.
     * @return array The results converted to messages.
     */
    private static function processPatchResults($results) {
        $messages = array();
        if ($results['queries'] > 0 && $results['queries'] != $results['ignored']) {
            array_push($messages, ZMLoader::make("Message", $results['queries'].' statements processed.', 'success'));
        } else {
            array_push($messages, ZMLoader::make("Message", 'Failed: '.$results['queries'].'.', 'error'));
        }

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $value) {
                array_push($messages, ZMLoader::make("Message", 'ERROR: '.$value.'.', 'error'));
            }
        }
        if ($results['ignored'] != 0) {
            array_push($messages, ZMLoader::make("Message", 'Note: '.$results['ignored'].' statements ignored. See "upgrade_exceptions" table for additional details.', 'warn'));
        }

        return $messages;
    }

}

?>
