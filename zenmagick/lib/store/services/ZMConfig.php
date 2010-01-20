<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @author DerManoMann
 * @package org.zenmagick.store.services
 * @version $Id$
 */
class ZMConfig extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Config');
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
    public function createConfigValue($title, $key, $value, $groupId, $description='', $sortOrder=0, $setFunction=null, $useFunction=null) {
        // keys are always upper case
        $key = strtoupper($key);

        $sql = "INSERT INTO " . TABLE_CONFIGURATION . " (
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
        ZMRuntime::getDatabase()->update($sql, $args, TABLE_CONFIGURATION);
    }

    /**
     * Update config value.
     *
     * @param string key The config key.
     * @param string value The new value.
     */
    public function updateConfigValue($key, $value) {
        $sql = "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_value = :value
                WHERE configuration_key = :key";
        $args = array("key" => $key, "value" => $value);
        ZMRuntime::getDatabase()->update($sql, $args, TABLE_CONFIGURATION);
    }

    /**
     * Get all config values for a given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     * @return array A list of <code>ZMConfigValue</code> or <code>ZMWidget</code> instances.
     */
    public function getConfigValues($pattern) {
        $sql = "SELECT *
                FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_key like :key
                ORDER BY configuration_id";
        $values = array();
        foreach (Runtime::getDatabase()->query($sql, array('key' => $pattern), TABLE_CONFIGURATION) as $value) {
            if (0 === strpos($value['setFunction'], 'widget@')) {
                $widgetDefinition = $value['setFunction'].'&'.$value['useFunction'];
                // build definition from both function values (just in case)
                $definition = str_replace('widget@', '', $widgetDefinition);
                $widget = ZMBeanUtils::getBean($definition);
                if (null !== $widget) {
                    $widget->setTitle($value['name']);
                    $widget->setDescription($value['description']);
                    $widget->setValue($value['value']);
                    // needed for generic plugin config support
                    $widget->set('configurationKey', $value['key']);
                    $values[] = $widget;
                } else {
                    ZMLogging::instance()->log('failed to create widget: '.$widgetDefinition, ZMLogging::WARN);
                }
            } else {
                $values[] = ZMBeanUtils::map2obj('ConfigValue', $value);
            }
        }
        return $values;
    }

    /**
     * Remove config value.
     *
     * @param string key The config key.
     */
    public function removeConfigValue($key) {
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_key = :key";
        ZMRuntime::getDatabase()->update($sql, array('key' => $key), TABLE_CONFIGURATION);
    }

    /**
     * Remove config value for the given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     */
    public function removeConfigValues($pattern) {
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_key like :key";
        ZMRuntime::getDatabase()->update($sql, array('key' => $pattern), TABLE_CONFIGURATION);
    }

    /**
     * Get all configuration groups.
     *
     * @return array List of ZMConfigGroup instances.
     */
    public function getConfigGroups() {
        $sql = "SELECT *
                FROM " . TABLE_CONFIGURATION_GROUP . " 
                WHERE visible = '1'
                ORDER BY sort_order";
        return ZMRuntime::getDatabase()->query($sql, array(), TABLE_CONFIGURATION_GROUP, 'ConfigGroup');
    }

}

?>
