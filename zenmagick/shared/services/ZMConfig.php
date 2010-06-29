<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
     * Load config values for the given sql and args.
     *
     * @param string sql The sql.
     * @param array args The query args.
     * @return array A list of <code>ZMConfigValue</code> or <code>ZMWidget</code> instances.
     */
    protected function loadValuesForSql($sql, $args) {
        $values = array();
        foreach (Runtime::getDatabase()->query($sql, $args, TABLE_CONFIGURATION) as $value) {
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
                // try to convert into widget...
                $widget = null;
                $setFunction = $value['setFunction'];
                if (null != $setFunction) {
                    $tmp = explode('(', $setFunction);
                    $setFunction = trim($tmp[0]);
                }
                switch ($setFunction) {
                case null:
                    $widget = ZMBeanUtils::getBean('TextFormWidget');
                    $size = strlen($value['value'])+3;
                    $size = 64 < $size ? 64 : $size;
                    $widget->set('size', $size);
                    break;
                case 'zen_cfg_textarea':
                    $widget = ZMBeanUtils::getBean('TextAreaFormWidget');
                    $widget->setRows(5);
                    $widget->setCols(60);
                    break;
                case 'zen_cfg_textarea_small':
                    $widget = ZMBeanUtils::getBean('TextAreaFormWidget');
                    $widget->setRows(1);
                    $widget->setCols(35);
                    break;
                case 'zen_cfg_select_option':
                    // XXX: perhaps make radio group
                    $widget = ZMBeanUtils::getBean('SelectFormWidget');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    break;
                case 'zen_cfg_select_drop_down':
                    $widget = ZMBeanUtils::getBean('SelectFormWidget');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    break;
                case 'zen_cfg_pull_down_order_statuses':
                    $widget = ZMBeanUtils::getBean('OrderStatusSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list':
                    $widget = ZMBeanUtils::getBean('CountrySelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list_none':
                    $widget = ZMBeanUtils::getBean('CountrySelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_pull_down_htmleditors':
                    $widget = ZMBeanUtils::getBean('EditorSelectFormWidget');
                    break;

                //TODO: implement more...
                default:
                    echo $setFunction.": ".$value['setFunction']."<BR>";
                    $widget = ZMBeanUtils::map2obj('ConfigValue', $value);
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
        return $this->loadValuesForSql($sql, $args);
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
        return $this->loadValuesForSql($sql, $args);
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
     * Get a configuration group.
     *
     * @param int groupId The group id.
     * @return ZMConfigGroup A <code>ZMConfigGroup</code> instance or <code>null</code>.
     */
    public function getConfigGroupForId($groupId) {
        $sql = "SELECT *
                FROM " . TABLE_CONFIGURATION_GROUP . " 
                WHERE configuration_group_id = :id";
        return ZMRuntime::getDatabase()->querySingle($sql, array('id' => $groupId), TABLE_CONFIGURATION_GROUP, 'ConfigGroup');
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
        return ZMRuntime::getDatabase()->query($sql, array(), TABLE_CONFIGURATION_GROUP, 'ConfigGroup');
    }

    /**
     * Load all configuration values.
     *
     * @return array Map of all configuration values.
     */
    public function loadAll() {
        $map = array();
        $sql = "SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION;
        foreach(ZMRuntime::getDatabase()->query($sql) as $result) {
            $map[$result['configuration_key']] = $result['configuration_value'];
        }

        return $map;
    }

}
