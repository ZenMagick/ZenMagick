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
 * Configuration.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
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
     */
    function createConfigValue($title, $key, $value, $groupId, $description='', $sortOrder=0) {
        // keys are always upper case
        $key = strtoupper($key);

        $db = $this->getDB();
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
        $results = $db->Execute($sql);
    }

    /**
     * Update config value.
     *
     * @param string key The config key.
     * @param string value The new value.
     */
    function updateConfigValue($key, $value) {
        $db = $this->getDB();
        $sql = "update " . TABLE_CONFIGURATION . "
                set configuration_value = :configValue
                where configuration_key = :configKey";
        $sql = $db->bindVars($sql, ":configKey", $key, "string");
        $sql = $db->bindVars($sql, ":configValue", $value, "string");
        $results = $db->Execute($sql);
    }

    /**
     * Remove config value.
     *
     * @param string key The config key.
     */
    function removeConfigValue($key) {
        $db = $this->getDB();
        $sql = "delete from " . TABLE_CONFIGURATION . " where configuration_key = :configKey";
        $sql = $db->bindVars($sql, ":configKey", $key, "string");
        $results = $db->Execute($sql);
    }

    /**
     * Get all configuration groups.
     *
     * @return array List of ZMConfigGroup instances.
     */
    function getConfigGroups() {
        $db = $this->getDB();
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
    function &_newConfigGroup($fields) {
        $group = $this->create("ConfigGroup");
        $group->setId($fields['configuration_group_id']);
        $group->setName($fields['configuration_group_title']);
        return $group;
    }

}

?>
