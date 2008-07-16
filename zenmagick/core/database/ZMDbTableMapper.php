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
 * Database table mapping *service*.
 *
 * <p>This class will read and parse file(s) containing model-database mappings. The code can
 * map a single model class to a database table. Mappings are configured using nested arrays and
 * wll be parsed into something <code>ZMDatabase</code> implementations can understand and use.</p>
 *
 * <p>A simple mapping could look like this:</p>
 * <code><pre>
 *   'products_to_categories' => array(
 *       'productId' => 'column=products_id;type=integer;key=true',
 *       'categoryId' => 'column=categories_id;type=integer;key=true',
 *    ),
 * </pre></code>
 *
 * <p>Each mapping has the table name as key (without any prefix) and an array of field mappings as value.</p>
 * <p>Each entry maps a single model property to a table column. The key is the model property name (starting lowercase).
 * Available <em>*attributes*</em> are:/p>
 * <dl>
 *  <dt>column</dt>
 *  <dd>The column in the table this model property is mapped to.</dd>
 *  <dt>type</dt>
 *  <dd>The data type. Supported types are:<br>
 *   <ul>
 *    <li>integer - an integer</li>
 *    <li>boolean - a boolean</li>
 *    <li>string - a string</li>
 *    <li>float - a float</li>
 *    <li>date - a date in the format <em>'yyyy-mm-dd'<em> (as defined by <code>ZM_DATE_FORMAT</code>)</li>
 *    <li>datetime - a date and time value in the format <em>'yyyy-mm-dd hh:ii:ss'<em> (as defined by <code>ZM_DATETIME_FORMAT</code>)</li>
 *    <li>blob - binary data</li>
 *   </ul>
 *  </dd>
 *  <dt>key</dt>
 *  <dd>If set to <code>true</code> this field is part of a table key.</dd>
 *  <dt>auto</dt>
 *  <dd>Indicates that this column is an auto-increment column, so new model instances will be updated with the new value on create.</dd>
 * </dl>
 *
 * @author mano
 * @package org.zenmagick.database
 * @version $Id$
 */
class ZMDbTableMapper extends ZMObject {
    private $tableMap;


    /**
     * Create a new instance.
     *
     * @param string configFolder The folder that contains the mapping files.
     */
    function __construct() {
        parent::__construct();
        eval('$mappings = '.file_get_contents(ZMRuntime::getZMRootPath().'/'.ZMSettings::get('dbMappings')));
        $this->tableMap = array();
        foreach ($mappings as $table => $mapping) {
            $this->tableMap[$table] = $this->parseTable($mapping);
        }
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('DbTableMapper');
    }


    /**
     * Parse mapping for a single table.
     *
     * @param array mapping The mapping.
     * @return array The parsed mapping.
     */
    protected function parseTable($mapping) {
        $defaults = array('key' => false, 'auto' => false);
        $tableInfo = array();
        foreach ($mapping as $property => $info) {
            $arr = array();
            parse_str(str_replace(';', '&', $info), $arr);
            $tableInfo[$property] = array_merge($defaults, $arr);
            $tableInfo[$property]['property'] = $property;
            $tableInfo[$property]['ucwp'] = ucwords($property);
            // handle boolean values
            foreach ($tableInfo[$property] as $name => $value) {
                if ('false' == $value) {
                    $tableInfo[$property][$name] = false;
                } else if ('true' == $value) {
                    $tableInfo[$property][$name] = true;
                } 
            }
        }

        return $tableInfo;
    }

    /**
     * Get a table map.
     *
     * @param mixed tables Either a single table or array of table names.
     * @return array The mapping or <code>null</code>.
     */
    public function getMapping($tables) {
        if (!is_array($tables)) {
            $tables = array($tables);
        }
        foreach ($tables as $ii => $table) {
            $tables[$ii] = str_replace(ZM_DB_PREFIX, '', $table);
        }

        $mappings = array();
        foreach (array_reverse($tables) as $table) {
            if (!isset($this->tableMap[$table])) {
                return null;
            }
            //TODO: do only once?
            // add the current custom fields
            $tableMap = $this->addCustomFields($this->tableMap[$table], $table);
            $mappings = array_merge($mappings, $tableMap);
        }

        return $mappings;
    }
    /**
     * Handle mixed mapping values.
     *
     * @param mixed mapping The field mappings or table name.
     * @return array A mapping or <code>null</code>.
     */
    public function ensureMapping($mapping) {
        if (!is_array($mapping)) {
            // table name
            return $this->getMapping($mapping);
        }
        // either mapping or table list
        return is_array($mapping[0]) ? $mapping : $this->getMapping($mapping);
    }

    /**
     * Set the mapping for the given table.
     *
     * <p><strong>NOTE:</strong> This will silently override mappings for existing tables.</p>
     *
     * @param string table The (new) table.
     * @param array The new mapping.
     */
    public function setMappingForTable($table, $mapping) {
        $this->tableMap[$table] = $this->parseTable($mapping);
    }

    /**
     * Get the setting name for custom fields for the given table name.
     *
     * @param string table The table name.
     * @return string The name of the ZenMagick setting to be used to lookup
     *  custom fields for the table.
     */
    protected function getCustomFieldKey($table) {
        $table = str_replace(ZM_DB_PREFIX, '', $table);
        return 'sql.'.$table.'.customFields';
    }

    /**
     * Add a field list of custom fields for the given table.
     *
     * @param array mapping The existing mapping.
     * @param string table The table name.
     * @return array The updated mapping
     */
    protected function addCustomFields($mapping, $table) {
        $setting = ZMSettings::get($this->getCustomFieldKey($table));
        if (!empty($setting)) {
            foreach (explode(',', $setting) as $field) {
                if (!empty($field)) {
                    $fieldInfo = explode(';', trim($field));
                    $fieldId = (count($fieldInfo) > 2 ? $fieldInfo[2] : $fieldInfo[0]);
                    $mapping[$fieldId] = array('column' => $fieldInfo[0], 'type' => $fieldInfo[1], 'property' => $fieldId, 'ucwp' => ucwords($fieldId));
                }
            }
        }

        return $mapping;
    }

}

?>
