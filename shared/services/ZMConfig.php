<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;

/**
 * Configuration.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services
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
        return Runtime::getContainer()->get('configService');
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
        ZMRuntime::getDatabase()->update($sql, $args, 'configuration');
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
        ZMRuntime::getDatabase()->update($sql, $args, 'configuration');
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
     * Build a collection of ZMWidget and/or ZMConfigValue objects
     *
     * @todo most of this should be part of the ZMConfigValue entity
     * @param array array of config values
     * @return array A list of <code>ZMConfigValue</code> or <code>ZMWidget</code> instances.
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
                    $widget = Beans::getBean('ZMTextFormWidget');
                    $size = strlen($value['value'])+3;
                    $size = 64 < $size ? 64 : $size;
                    $widget->set('size', $size);
                    break;
                case 'zen_cfg_textarea':
                    $widget = Beans::getBean('ZMTextAreaFormWidget');
                    $widget->setRows(5);
                    $widget->setCols(60);
                    break;
                case 'zen_cfg_textarea_small':
                    $widget = Beans::getBean('ZMTextAreaFormWidget');
                    $widget->setRows(1);
                    $widget->setCols(35);
                    break;
                case 'zen_cfg_select_option':
                    // XXX: perhaps make radio group
                    $widget = Beans::getBean('ZMSelectFormWidget#style=radio');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    if (3 < count($widget->getOptions(null))) {
                        $widget->setStyle('select');
                    }
                    break;
                case 'zen_cfg_select_drop_down':
                    $widget = Beans::getBean('ZMSelectFormWidget');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    break;
                case 'zen_cfg_pull_down_order_statuses':
                    $widget = Beans::getBean('ZMOrderStatusSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list':
                    $widget = Beans::getBean('ZMCountrySelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list_none':
                    $widget = Beans::getBean('ZMCountrySelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_pull_down_htmleditors':
                    $widget = Beans::getBean('ZMTextFormWidget');
                    $widget->set('readonly', true);
                    //$widget = Beans::getBean('ZMEditorSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_zone_list';
                    $widget = Beans::getBean('ZMZoneSelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_select_coupon_id';
                    $widget = Beans::getBean('ZMCouponSelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;

                default:
                    //echo $setFunction.": ".$value['setFunction']."<BR>";
                    $widget = Beans::map2obj('ConfigValue', $value);
                    break;
                }
                if ($widget instanceof ZMWidget) {
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
     * @return mixed A single <code>ZMConfigValue</code> instance, <code>ZMWidget</code> instance or <code>null</code>.
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
     * @return array A list of <code>ZMConfigValue</code> or <code>ZMWidget</code> instances.
     */
    public function getConfigValues($pattern) {
        $sql = "SELECT *
                FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_key like :key
                ORDER BY sort_order, configuration_id";
        $args = array('key' => $pattern);
        $values = ZMRuntime::getDatabase()->query($sql, $args, 'configuration');
        return $this->buildObjects($values);
    }

    /**
     * Get all config values for a given group id.
     *
     * @param int groupId The group id.
     * @return array A list of <code>ZMConfigValue</code> or <code>ZMWidget</code> instances.
     */
    public function getValuesForGroupId($groupId) {
        $sql = "SELECT *
                FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_group_id like :groupId
                ORDER BY sort_order, configuration_id";
        $args = array('groupId' => $groupId);
        $values = ZMRuntime::getDatabase()->query($sql, $args, 'configuration');
        return $this->buildObjects($values);
    }

    /**
     * Remove config value.
     *
     * @param string key The config key.
     */
    public function removeConfigValue($key) {
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_key = :key";
        ZMRuntime::getDatabase()->update($sql, array('key' => $key), 'configuration');
    }

    /**
     * Remove config value for the given key pattern.
     *
     * @param string pattern The key pattern; for example 'foo_%'.
     */
    public function removeConfigValues($pattern) {
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . "
                WHERE configuration_key like :key";
        ZMRuntime::getDatabase()->update($sql, array('key' => $pattern), 'configuration');
    }

    /**
     * Get a configuration group.
     *
     * @param int groupId The group id.
     * @return ZMConfigGroup A <code>ZMConfigGroup</code> instance or <code>null</code>.
     */
    public function getConfigGroupForId($groupId) {
        $sql = "SELECT *
                FROM " . TABLE_CONFIGURATION_GROUP . "
                WHERE configuration_group_id = :id";
        return ZMRuntime::getDatabase()->querySingle($sql, array('id' => $groupId), 'configuration_group', 'ZMConfigGroup');
    }

    /**
     * Get all configuration groups.
     *
     * @return array List of ZMConfigGroup instances.
     */
    public function getConfigGroups() {
        $sql = "SELECT *
                FROM " . TABLE_CONFIGURATION_GROUP . "
                ORDER BY sort_order";
        return ZMRuntime::getDatabase()->query($sql, array(), 'configuration_group', 'ZMConfigGroup');
    }

    /**
     * Load all configuration values.
     *
     * @return array Map of all configuration values.
     */
    public function loadAll() {
        $map = array();
        $sql = "SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION;
        foreach (ZMRuntime::getDatabase()->query($sql) as $result) {
            $map[$result['configuration_key']] = $result['configuration_value'];
        }

        return $map;
    }

}
