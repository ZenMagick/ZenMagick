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
 * @author mano
 * @package org.zenmagick
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
     * @param boolean isRead Optional flag to indicate read or write; default is <code>true</code> for reads.
     * @return string The updated SQL query.
     */
    public static function bindObject($sql, $obj, $isRead=true) {
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

        $db = ZMRuntime::getDB();
        foreach ($getter as $lcproperty => $method) {
            if (isset($labels[$lcproperty])) {
                $label = $labels[$lcproperty];

                // execute getXXX()
                $value = call_user_func(array($obj, $method));

                // bind
                if ('date' == $label[1]) {
                    if ($isRead) {
                        $value = zen_date_raw($value);
                    } else {
                        // if not empty nothing, otherwise assume NULL
                        if (empty($value)) {
                            $value = 'NULL';
                            $label[1] = 'passthru';
                        }
                    }
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
     * Bind custom fields to a given sql query.
     * 
     * @param string sql The sql to work on.
     * @param mixed obj The data object instance.
     * @param string table The table name.
     * @param string valueMarker The string to be replaced with custom values; default is <em>:customFields</em>.
     * @return string The updated SQL query.
     */
    public static function bindCustomFields($sql, $obj, $table, $valueMarker=':customFields') {
        $fields = ZMDbUtils::getCustomFields($table);
        if (0 == count($fields)) {
            return str_replace($valueMarker.',', '', $sql);
        }

        $db = ZMRuntime::getDB();
        $fragment = '';
        foreach ($fields as $field) {
            $name = $field[0];
            $type = $field[1];
            $fragment .= $db->bindVars($field[0]." = :value, ", ":value", $obj->get($name), $type);
        }

        return str_replace($valueMarker.',', $fragment, $sql);
    }

    /**
     * Get the setting name for custom fields for the given table name.
     *
     * @param string table The table name.
     * @return string The name of the ZenMagick setting to be used to lookup
     *  custom fields for the table.
     */
    public static function getCustomFieldKey($table) {
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
    public static function getCustomFieldsSQL($table, $prefix='') {
        $fields = ZMDbUtils::getCustomFields($table);
        if (0 == count($fields)) {
            return '';
        }

        $customFields = '';
        if (!empty($prefix) && !zm_ends_with($prefix, '.')) {
            $prefix .= '.';
        }
        foreach ($fields as $field) {
            $customFields .= ', '.$prefix.$field[0];
        }
        return $customFields;
    }

    /**
     * Get a field list of custom fields for the given table.
     *
     * <p>The returned list of field information consists of two element arrays. The
     * first element is the field name and the second the field type.</p>
     *
     * @param string table The table name.
     * @return array A list of field lists (may be empty).
     */
    public static function getCustomFields($table) {
        $setting = zm_setting(ZMDbUtils::getCustomFieldKey($table));
        if (empty($setting)) {
            return array();
        }

        $customFields = array();
        foreach (explode(',', $setting) as $field) {
            $customFields[] = explode(';', trim($field));
        }

        return $customFields;
    }

    /**
     * Add a field list of custom fields for the given table.
     *
     * @param array mapping The existing mapping.
     * @param string table The table name.
     * @return array The updated mapping
     */
    public static function addCustomFields($mapping, $table) {
        $setting = zm_setting(ZMDbUtils::getCustomFieldKey($table));
        foreach (explode(',', $setting) as $field) {
            $fieldInfo = explode(';', trim($field));
            $mapping[$fieldInfo[0]] = $fieldInfo[0].':'.$fieldInfo[1];
        }

        return $mapping;
    }

    /**
     * Create model and populate using the given data and field map.
     *
     * @param string clazz The model class.
     * @param array data The data (keys are object property names)
     * @param array fieldMap The field mapping; default is <code>null</code> which will default to this service <code>fieldMap_</code>.
     * @return mixed The model instance.
     */
    public static function map2obj($clazz, $data, $fieldMap=null) {
        $obj = ZMDbUtils::create($clazz);
        if (null === $fieldMap) {
            $fieldMap = ZMDbUtils::fieldMap_;
        }
        // create col => property mapping
        $map = array();
        foreach ($fieldMap as $field) {
            $map[$field[0]] = $field[1];
        }

        foreach ($data as $key => $value) {
            if (isset($map[$key])) {
                // got mapping
                $name = $map[$key];
                $ucName = ucwords($name);
                $setter = 'set' . $ucName;
                if (method_exists($obj, $setter)) {
                    $obj->$setter($value);
                }
            }
        }

        return $obj;
    }

}

?>
