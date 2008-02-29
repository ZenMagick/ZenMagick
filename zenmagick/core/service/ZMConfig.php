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
 * Configuration.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMConfig extends ZMService {

    /**
     * Default c'tor.
     */
    function ZMConfig() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMConfig();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Create config value.
     *
     * @param string title The title.
     * @param string key The configuration key (with or without the common prefix).
     * @param string value The value.
     * @param int groupId The config group id.
     * @param string description The description; defaults to <code>''</code>.
     * @param int sortOrder optional sort order; defaults to <code>0</code>.
     * @param string setFunction The set function; defaults to <code>null</code>.
     * @param string useFunction The use function; defaults to <code>null</code>.
     */
    function createConfigValue($title, $key, $value, $groupId, $description='', $sortOrder=0, $setFunction=null, $useFunction=null) {
        // keys are always upper case
        $key = strtoupper($key);

        $db = ZMRuntime::getDB();
        $sql = "insert into " . TABLE_CONFIGURATION . " (
                  configuration_title, configuration_key, configuration_value, configuration_group_id,
                  configuration_description, sort_order, 
                  date_added, use_function, set_function)
                values (:title, :key, :value, :groupId,
                  :description, :sortOrder,
                  :dateAdded, :useFunction, :setFunction)";
        $sql = $db->bindVars($sql, ":title", $title, "string");
        $sql = $db->bindVars($sql, ":key", $key, "string");
        $sql = $db->bindVars($sql, ":value", $value, "string");
        $sql = $db->bindVars($sql, ":groupId", $groupId, "integer");
        $sql = $db->bindVars($sql, ":description", $description, "string");
        $sql = $db->bindVars($sql, ":sortOrder", $sortOrder, "integer");
        $sql = $db->bindVars($sql, ":dateAdded", 'now()', "passthru");
        $sql = $db->bindVars($sql, ":useFunction", $useFunction, "string");
        $sql = $db->bindVars($sql, ":setFunction", $setFunction, "string");
        $results = $db->Execute($sql);
    }

    /**
     * Update config value.
     *
     * @param string key The config key.
     * @param string value The new value.
     */
    function updateConfigValue($key, $value) {
        $db = ZMRuntime::getDB();
        $sql = "update " . TABLE_CONFIGURATION . "
                set configuration_value = :value
                where configuration_key = :key";
        $sql = $db->bindVars($sql, ":key", $key, "string");
        $sql = $db->bindVars($sql, ":value", $value, "string");
        $results = $db->Execute($sql);
    }

    /**
     * Get all config values for a given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     * @return array A list of <code>ZMConfigValue</code> instances.
     */
    function getConfigValues($pattern) {
        $db = ZMRuntime::getDB();
        $sql = "select configuration_id, configuration_title, configuration_key, configuration_value,
                configuration_description,
                use_function, set_function
                from " . TABLE_CONFIGURATION . " where configuration_key like :key
                order by configuration_id";
        $sql = $db->bindVars($sql, ":key", $pattern, "string");
        $results = $db->Execute($sql);

        $values = array();
        while (!$results->EOF) {
            $value = $this->_newConfigValue($results->fields);
            array_push($values, $value);
            $results->MoveNext();
        }

        return $values;
    }

    /**
     * Remove config value.
     *
     * @param string key The config key.
     */
    function removeConfigValue($key) {
        $db = ZMRuntime::getDB();
        $sql = "delete from " . TABLE_CONFIGURATION . " where configuration_key = :key";
        $sql = $db->bindVars($sql, ":key", $key, "string");
        $results = $db->Execute($sql);
    }

    /**
     * Remove config value for the given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     */
    function removeConfigValues($pattern) {
        $db = ZMRuntime::getDB();
        $sql = "delete from " . TABLE_CONFIGURATION . " where configuration_key like :key";
        $sql = $db->bindVars($sql, ":key", $pattern, "string");
        $results = $db->Execute($sql);
    }

    /**
     * Get all configuration groups.
     *
     * @return array List of ZMConfigGroup instances.
     */
    function getConfigGroups() {
        $db = ZMRuntime::getDB();
        $sql = "select configuration_group_id, configuration_group_title
                from " . TABLE_CONFIGURATION_GROUP . " 
                where visible = '1' order by sort_order";
        $results = $db->Execute($sql);

        $groups = array();
        while (!$results->EOF) {
            $group = $this->_newConfigGroup($results->fields);
            array_push($groups, $group);
            $results->MoveNext();
        }

        return $groups;
    }


    /**
     * Create new config group.
     */
    function _newConfigGroup($fields) {
        $group = $this->create("ConfigGroup");
        $group->setId($fields['configuration_group_id']);
        $group->setName($fields['configuration_group_title']);
        return $group;
    }

    /**
     * Create new config value instance.
     */
    function _newConfigValue($fields) {
        $value = $this->create("ConfigValue");
        $value->id_ = $fields['configuration_id'];
        $value->name_ = $fields['configuration_title'];
        $value->key_ = $fields['configuration_key'];
        $value->value_ = $fields['configuration_value'];
        $value->description_ = $fields['configuration_description'];
        $value->useFunction_ = $fields['use_function'];
        $value->setFunction_ = $fields['set_function'];
        return $value;
    }

}

?>
