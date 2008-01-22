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
 * Base service class.
 *
 * <p>A service can be something like a <code>DAO</code>, providing access
 * to database data or any other sort of service that a controller or request handler
 * might want to use. Examples for services that are not <code>DAO</code> are {@link org.zenmagick.service.ZMMessages ZMMessages}
 * or {@link org.zenmagick.service.ZMPlugins ZMPlugins}.</p>
 *
 * @author mano
 * @package org.zenmagick
 * @version $Id$
 */
class ZMService extends ZMObject {
    var $_db_;


    /**
     * Default c'tor.
     */
    function ZMService() {
    global $zm_runtime;

        parent::__construct();
        $this->_db_ = $zm_runtime->getDB();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMService();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get a <code>db</code> instance.
     *
     * @return queryFactory A <code>queryFactory</code> instance.
     */
    function getDB() { return $this->_db_; }

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
    function bindValueList($sql, $bindName, $values, $type='string') {
        $db = $this->getDB();
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
    function bindObject($sql, $obj) {
        // prepare label
        preg_match_all('/:\w+;\w+/m', $sql, $matches);
        $labels = array();
        foreach ($matches[0] as $name) {
            $label = explode(';', $name);
            $labels[strtolower(str_replace(':', '', $label[0]))] = array($name, $label[1]);
        }

        // prepare methods
        $methods = get_class_methods($obj);

        // strip 'get' prefix and ignore other
        $getter = array();
        foreach ($methods as $method) {
            if (0 === strpos($method, 'get')) {
                $lcproperty = substr(strtolower($method), 3);
                $getter[$lcproperty] = $method;
            } else if (0 === strpos($method, 'is')) {
                $lcproperty = substr(strtolower($method), 2);
                $getter[$lcproperty] = $method;
            }
        }

        $db = $this->getDB();
        foreach ($getter as $lcproperty => $method) {
            if (isset($labels[$lcproperty])) {
                $label = $labels[$lcproperty];

                // execute getXXX()
                $value = call_user_func(array($obj, $method));

                // bind
                if ('date' == $label[1]) {
                    $value = zen_date_raw($value);
                }
                $sql = $db->bindVars($sql, $label[0], $value, $label[1]);

                // unset so we end up with unmapped properties and/or labels ...
                unset($labels[$lcproperty]);
                unset($getter[$lcproperty]);
            }
        }

        return $sql;
    }

    /**
     * Get the setting name for custom fields for the given table name.
     *
     * @param string table The table name.
     * @return string The name of the ZenMagick setting to be used to lookup
     *  custom fields for the table.
     */
    function getCustomFieldKey($table) {
        $table = str_replace(ZM_DB_PREFIX, '', $table);
        return 'sql.'.$table.'.customFields';
    }

    /**
     * Get a SQL field list of custom fields for the given table.
     *
     * @param string table The table name.
     * @param string prefix Optional fieldname prefix; default is blank <em>''</em>.
     * @return string A field list or empty string.
     */
    function getCustomFieldsSQL($table, $prefix='') {
        $setting = zm_setting($this->getCustomFieldKey($table));
        if (zm_is_empty($setting)) {
            return '';
        }

        $customFields = '';
        if (!zm_is_empty($prefix) && !zm_ends_with($prefix, '.')) {
            $prefix .= '.';
        }
        foreach (explode(',', $setting) as $field) {
            $customFields .= ', '.$prefix.trim($field);
        }
        return $customFields;
    }

    /**
     * Get a field list of custom fields for the given table.
     *
     * @param string table The table name.
     * @return array A list of field lists (may be empty).
     */
    function getCustomFields($table) {
        $setting = zm_setting($this->getCustomFieldKey($table));
        if (zm_is_empty($setting)) {
            return array();
        }

        $customFields = array();
        foreach (explode(',', $setting) as $field) {
            $customFields[] = trim($field);
        }

        return $customFields;
    }

}

?>
