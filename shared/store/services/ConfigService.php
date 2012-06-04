<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\services;

use ZMRuntime;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\apps\store\model\ConfigGroup;
use zenmagick\apps\store\model\ConfigValue;

/**
 * Config service.
 *
 * @author DerManoMann
 */
class ConfigService extends ZMObject {

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
    public function createConfigValue($title, $key, $value, $groupId, $description='', $sortOrder=0, $setFunction=null, $useFunction=null) {
        // keys are always upper case
        $key = strtoupper($key);

        $sql = "INSERT INTO %table.configuration% (
                  configuration_title, configuration_key, configuration_value, configuration_group_id,
                  configuration_description, sort_order,
                  date_added, use_function, set_function)
                VALUES (:name, :key, :value, :groupId,
                  :description, :sortOrder,
                  now(), :useFunction, :setFunction)";
        $args = array(
            "name" => $title,
            "key" => $key,
            "value" => $value,
            "groupId" => $groupId,
            "description" => $description,
            "sortOrder" => $sortOrder,
            "useFunction" => $useFunction,
            "setFunction" => $setFunction
        );
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'configuration');
    }

    /**
     * Create Configuration Group
     *
     * @param string name The name of the configuration group
     * @param string description The description of the configuration group
     * @param bool visible Is the configuration group visible
     * @param int sortOder The sort order of the configuration group
     */
    public function createConfigGroup($name, $description = '', $visible = true, $sortOrder = 0) {
        $configGroup = new ConfigGroup();
        $configGroup->setName($name);
        $configGroup->setDescription($description);
        $configGroup->setVisible($visible);
        $configGroup->setSortOrder($sortOrder);
        return ZMRuntime::getDatabase()->createModel('configuration_group', $configGroup);
    }

    /**
     * Update config value.
     *
     * @param string key The config key.
     * @param string value The new value.
     */
    public function updateConfigValue($key, $value) {
        $sql = "UPDATE %table.configuration%
                SET configuration_value = :value
                WHERE configuration_key = :key";
        $args = array("key" => $key, "value" => $value);
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'configuration');
    }

    /**
     * Build a collection of ConfigValue objects
     *
     * @param array array of config values
     * @return array A list of <code>ConfigValue</code>s.
     */
    protected function buildObjects($configValues) {
        $values = array();
        foreach ($configValues as $value) {
            $values[] = Beans::map2obj('zenmagick\apps\store\model\ConfigValue', $value);
        }
        return $values;
    }

    /**
     * Get a single config value for the given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     * @return mixed A single <code>ConfigValue</code> instance or <code>null</code>.
     */
    public function getConfigValue($pattern) {
        $values = $this->getConfigValues($pattern);
        if (null != $values && 0 < count($values)) {
            return $values[0];
        }
        return null;
    }

    /**
     * Get all config values for a given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     * @return array A list of <code>ConfigValue</code> instances.
     */
    public function getConfigValues($pattern) {
        $sql = "SELECT *
                FROM %table.configuration%
                WHERE configuration_key like :key
                ORDER BY sort_order, configuration_id";
        $args = array('key' => $pattern);
        $values = ZMRuntime::getDatabase()->fetchAll($sql, $args, 'configuration');
        return $this->buildObjects($values);
    }

    /**
     * Get all config values for a given group id.
     *
     * @param int groupId The group id.
     * @return array A list of <code>ConfigValue</code> instances.
     */
    public function getValuesForGroupId($groupId) {
        $sql = "SELECT *
                FROM %table.configuration%
                WHERE configuration_group_id like :groupId
                ORDER BY sort_order, configuration_id";
        $args = array('groupId' => $groupId);
        $values = ZMRuntime::getDatabase()->fetchAll($sql, $args, 'configuration');
        return $this->buildObjects($values);
    }

    /**
     * Remove config value.
     *
     * @param string key The config key.
     */
    public function removeConfigValue($key) {
        $sql = "DELETE FROM %table.configuration%
                WHERE configuration_key = :key";
        ZMRuntime::getDatabase()->updateObj($sql, array('key' => $key), 'configuration');
    }

    /**
     * Remove config value for the given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     */
    public function removeConfigValues($pattern) {
        $sql = "DELETE FROM %table.configuration%
                WHERE configuration_key like :key";
        ZMRuntime::getDatabase()->updateObj($sql, array('key' => $pattern), 'configuration');
    }

    /**
     * Get a configuration group.
     *
     * @param int groupId The group id.
     * @return ConfigGroup A <code>ConfigGroup</code> instance or <code>null</code>.
     */
    public function getConfigGroupForId($groupId) {
        $sql = "SELECT *
                FROM %table.configuration_group%
                WHERE configuration_group_id = :id";
        return ZMRuntime::getDatabase()->querySingle($sql, array('id' => $groupId), 'configuration_group', 'zenmagick\apps\store\model\ConfigGroup');
    }

    /**
     * Get a configuration group for name.
     *
     * @param string name The name of the group.
     * @return ConfigGroup A <code>ConfigGroup</code> instance or <code>null</code>.
     */
    public function getConfigGroupForName($name) {

        $sql = "SELECT *
                FROM %table.configuration_group%
                WHERE configuration_group_title = :name";
        return ZMRuntime::getDatabase()->querySingle($sql, array('name' => $name), 'configuration_group', 'zenmagick\apps\store\model\ConfigGroup');
    }

    /**
     * Get all configuration groups.
     *
     * @return array List of ConfigGroup instances.
     */
    public function getConfigGroups() {
        $sql = "SELECT *
                FROM %table.configuration_group%
                ORDER BY sort_order";
        return ZMRuntime::getDatabase()->fetchAll($sql, array(), 'configuration_group', 'zenmagick\apps\store\model\ConfigGroup');
    }

    /**
     * Load all configuration values.
     *
     * @return array Map of all configuration values.
     */
    public function loadAll() {
        $map = array();
        $sql = "SELECT configuration_key, configuration_value FROM %table.configuration%";
        foreach (ZMRuntime::getDatabase()->fetchAll($sql) as $result) {
            $map[$result['configuration_key']] = $result['configuration_value'];
        }

        return $map;
    }

}
