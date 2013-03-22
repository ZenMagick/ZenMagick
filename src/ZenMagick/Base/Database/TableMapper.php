<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\Base\Database;

use ZMRuntime;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

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
 *       'productId' => array('column' => 'products_id', 'type' = 'integer', 'key' => true),
 *       'categoryId' => array('column' => categories_id', 'type' = 'integer', 'key' => true),
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
 *    <li>date - a date</li>
 *    <li>datetime - a date and time value</li>
 *    <li>blob - binary data</li>
 *   </ul>
 *  </dd>
 *  <dt>key</dt>
 *  <dd>If set to <code>true</code> this field is part of a table key.</dd>
 *  <dt>auto</dt>
 *  <dd>Indicates that this column is an auto-increment column, so new model instances will be updated with the new value on create.</dd>
 *  <dt>default</dt>
 *  <dd>A default value to be used if either the value given is <code>null</code>, or the corresponding field is not present at all.</dd>
 * </dl>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class TableMapper extends ZMObject
{
    private $tableMap;
    private $tablePrefix;

    /**
     * Create a new instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->tableMap = array();
        $this->tablePrefix = '';
        // load from file
        eval('$mappings = '.file_get_contents(Runtime::getInstallationPath().'/src/ZenMagick/StoreBundle/config/db_mappings.txt'));
        foreach ($mappings as $table => $mapping) {
            $this->setMappingForTable($table, $mapping);
        }

    }

    public function setTablePrefix($tablePrefix = '')
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Get a table map.
     *
     * @param mixed tables Either a single table or array of table names.
     * @return array The mapping or <code>null</code>.
     */
    public function getMapping($tables)
    {
        $mappings = array();
        foreach (array_reverse((array) $tables) as $table) {
            $table = str_replace($this->tablePrefix, '', $table);
            if (empty($table)) continue;

            if (!array_key_exists($table, $this->tableMap)) {
                $this->setMappingForTable($table, ZMRuntime::getDatabase()->getMetaData($table));
            }

            $mappings = array_merge($mappings, $this->tableMap[$table]);
        }

        return $mappings;
    }


    /**
     * Handle mixed mapping values.
     *
     * @param mixed mapping The field mappings or table name.
     * @return array A mapping or <code>null</code>.
     */
    public function ensureMapping($mapping)
    {
        if (!is_array($mapping)) { // table name

            return $this->getMapping($mapping);
        }
        // either mapping or table list
        return (0 < count($mapping) && is_array($mapping[0])) ? $mapping : $this->getMapping($mapping);
    }

    /**
     * Add a property to a table.
     */
    public function addPropertyForTable($table, $name, $info)
    {
        $table = str_replace($this->tablePrefix, '', $table);
        $defaults = array('property' => $name, 'key' => false, 'auto' => false, 'custom' => false, 'default' => null);
        $this->tableMap[$table][$name] = array_merge($defaults, (array) $info);
    }

    /**
     * Set the mapping for the given table.
     *
     * <p><strong>NOTE:</strong> This will silently override mappings for existing tables.</p>
     *
     * @param string table The (new) table.
     * @param array The new mapping.
     */
    public function setMappingForTable($table, $mapping)
    {
        $this->removeMappingForTable($table);
        foreach ((array) $mapping as $property => $info) {
            $this->addPropertyForTable($table, $property, $info);
        }
    }

    /**
     * Remove the mapping for the given table.
     *
     * @param string table The table to remove.
     */
    public function removeMappingForTable($table)
    {
        $table = str_replace($this->tablePrefix, '', $table);
        unset($this->tableMap[$table]);
    }

}
