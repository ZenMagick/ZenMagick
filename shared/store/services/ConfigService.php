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
use zenmagick\http\widgets\Widget;
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

        $sql = "INSERT INTO " . DB_PREFIX . "configuration (
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
        $sql = "UPDATE " . DB_PREFIX . "configuration
                SET configuration_value = :value
                WHERE configuration_key = :key";
        $args = array("key" => $key, "value" => $value);
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'configuration');
    }

    /**
     * Split options into map.
     *
     * @param string value The set function value.
     * @return array An options map.
     */
    private function splitOptions($value) {
        // some initial stripping
        $value = preg_replace('/.*\(array\((.*)\).*/', '\1', $value);

        $idText = false;
        if (false !== strpos($value, 'id') && false !== strpos($value, 'text') && false !== strpos($value, '=>')) {
            // we do have an id/text mapping (nested arrays)
            $idText = true;
        }

        $options = array();
        if ($idText) {
            foreach (explode(', array(', $value) as $option) {
                $tmp = explode(',', $option);
                $value = str_replace(array("'id'", '"id"', '=>', '"', "'"), '', trim($tmp[0]));
                $text = str_replace(array("'text'", '"text"', '=>', '"', "'"), '', trim($tmp[1]));
                $text = substr($text, 0, -1);
                $options[$value] = $text;
            }
        } else {
            foreach (explode(',', $value) as $option) {
                $option = str_replace(array('"', "'"), '', trim($option));
                $options[$option] = $option;
            }
        }
        return $options;
    }

    /**
     * Build a collection of Widget and/or ConfigValue objects
     *
     * @todo most of this should be part of the ConfigValue entity
     * @param array array of config values
     * @return array A list of <code>ConfigValue</code> or <code>Widget</code> instances.
     */
    protected function buildObjects($configValues) {
        $values = array();
        foreach ($configValues as $value) {
            if (0 === strpos($value['setFunction'], 'widget@')) {
                $widgetDefinition = $value['setFunction'].'&'.$value['useFunction'];
                // build definition from both function values (just in case)
                $definition = str_replace('widget@', '', $widgetDefinition);
                $widget = Beans::getBean($definition);
                if (null !== $widget) {
                    $widget->setTitle($value['name']);
                    $widget->setDescription($value['description']);
                    $widget->setValue($value['value']);
                    // needed for generic plugin config support
                    $widget->set('configurationKey', $value['key']);
                    $values[] = $widget;
                } else {
                    Runtime::getLogging()->warn('failed to create widget: '.$widgetDefinition);
                }
            } else {
                // try to convert into widget...
                $widget = null;
                $setFunction = $value['setFunction'];
                if (null != $setFunction) {
                    $tmp = explode('(', $setFunction);
                    $setFunction = trim($tmp[0]);
                }
                switch ($setFunction) {
                case null:
                    $widget = Beans::getBean('textFormWidget');
                    $size = strlen($value['value'])+3;
                    $size = 64 < $size ? 64 : $size;
                    $widget->set('size', $size);
                    break;
                case 'zen_cfg_textarea':
                    $widget = Beans::getBean('textAreaFormWidget');
                    $widget->setRows(5);
                    $widget->setCols(60);
                    break;
                case 'zen_cfg_textarea_small':
                    $widget = Beans::getBean('textAreaFormWidget');
                    $widget->setRows(1);
                    $widget->setCols(35);
                    break;
                case 'zen_cfg_select_option':
                    // XXX: perhaps make radio group
                    $widget = Beans::getBean('selectFormWidget#style=radio');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    if (3 < count($widget->getOptions(null))) {
                        $widget->setStyle('select');
                    }
                    break;
                case 'zen_cfg_select_drop_down':
                    $widget = Beans::getBean('selectFormWidget');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    break;
                case 'zen_cfg_pull_down_order_statuses':
                    $widget = Beans::getBean('orderStatusSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list':
                    $widget = Beans::getBean('countrySelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list_none':
                    $widget = Beans::getBean('countrySelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_pull_down_htmleditors':
                    $widget = Beans::getBean('textFormWidget');
                    $widget->set('readonly', true);
                    //$widget = Beans::getBean('ZMEditorSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_zone_list';
                    $widget = Beans::getBean('zoneSelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_select_coupon_id';
                    $widget = Beans::getBean('couponSelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;

                default:
                    //echo $setFunction.": ".$value['setFunction']."<BR>";
                    $widget = Beans::map2obj('ConfigValue', $value);
                    break;
                }
                if ($widget instanceof Widget) {
                    // common stuff
                    $widget->setName($value['key']);
                    $widget->setTitle($value['name']);
                    $widget->setDescription(htmlentities($value['description']));
                    $widget->setValue(htmlentities($value['value']));
                    $widget->set('id', $value['key']);
                    // needed for generic plugin config support
                    $widget->set('configurationKey', $value['key']);
                }

                $values[] = $widget;
            }
        }
        return $values;
    }

    /**
     * Get a single config value for the given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     * @return mixed A single <code>ConfigValue</code> instance, <code>Widget</code> instance or <code>null</code>.
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
     * @return array A list of <code>ConfigValue</code> or <code>Widget</code> instances.
     */
    public function getConfigValues($pattern) {
        $sql = "SELECT *
                FROM " . DB_PREFIX . "configuration
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
     * @return array A list of <code>ConfigValue</code> or <code>Widget</code> instances.
     */
    public function getValuesForGroupId($groupId) {
        $sql = "SELECT *
                FROM " . DB_PREFIX . "configuration
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
        $sql = "DELETE FROM " . DB_PREFIX . "configuration
                WHERE configuration_key = :key";
        ZMRuntime::getDatabase()->updateObj($sql, array('key' => $key), 'configuration');
    }

    /**
     * Remove config value for the given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     */
    public function removeConfigValues($pattern) {
        $sql = "DELETE FROM " . DB_PREFIX . "configuration
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
                FROM " . DB_PREFIX . "configuration_group
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
                FROM " . DB_PREFIX . "configuration_group
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
                FROM " . DB_PREFIX . "configuration_group
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
        $sql = "SELECT configuration_key, configuration_value FROM " . DB_PREFIX . "configuration";
        foreach (ZMRuntime::getDatabase()->fetchAll($sql) as $result) {
            $map[$result['configuration_key']] = $result['configuration_value'];
        }

        return $map;
    }

}
