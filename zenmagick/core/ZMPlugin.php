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
 * Plugin base class.
 *
 * <p>Plugins are compatible with zen-cart modules, so extending from here will also
 * give you a valid zen-cart module.<br>
 * Note, however, that accessing class variables and a few of the original zen-cart
 * methods are deprecated in ZenMagick...</p>
 *
 * <p>The plugin code is based on the plugin class and/or file name as zen-cart expects those
 * to be the same.</p>
 *
 * <p>When using the default install method to generate common plugin settings, make sure that
 * additional configuration settings do not use a sortOrder <em>&lt; 2</em>, as <em>0</em>
 * and <em>1</em> are used for those.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMPlugin extends ZMService {
    /** @deprecated use <code>getId()</code> instead. */
    var $code;
    /** @deprecated use <code>getName()</code> instead. */
    var $title;
    /** @deprecated use <code>getDescription()</code> instead. */
    var $description;
    /** @deprecated use <code>isEnabled()</code> instead. */
    var $enabled;
    /** @deprecated use <code>getSortOrder()</code> instead. */
    var $sort_order;
    var $installed_;
    var $enabled_;
    var $configPrefix_;
    var $enabledValue_;
    var $keys_;
    var $type_;
    var $messages_ = null;


    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param int sortOrder The default sortOrder; defaults to <code>0</code>.
     * @param string configPrefix The configuration key prefix; defaults to [PLUGIN]_[PLUGIN-CODE]_.
     */
    function ZMPlugin($title='', $description='', $sortOrder=0, $configPrefix=null) {
        parent::__construct();

        $this->type_ = "request";
        $this->code = get_class($this);
        $this->title = $title;
        $this->description = $description;
        $this->enabled = false;
        $this->enabledValue_ = zm_l10n_get('Enabled');
        $this->sort_order = $sortOrder;
        $this->installed_ = null;
        $this->enabled_ = null;
        $this->configPrefix_ = $configPrefix;
        if (null === $this->configPrefix_) {
            $this->configPrefix_ = ZM_PLUGIN_PREFIX . $this->type_ . '_'. $this->code . '_';
        }
        $this->configPrefix_ = strtoupper($this->configPrefix_);
        $this->keys_ = array();
        if (defined($this->configPrefix_.ZM_PLUGIN_SORT_ORDER_SUFFIX)) {
            $this->sort_order = constant($this->configPrefix_.ZM_PLUGIN_SORT_ORDER_SUFFIX);
        }
        $this->enabled = $this->isEnabled();
        $this->messages_ = array();
    }

    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param int sortOrder The default sortOrder; defaults to <code>0</code>.
     * @param string configPrefix The configuration key prefix; defaults to [PLUGIN]_[PLUGIN-CODE]_.
     */
    function __construct($title='', $description='', $sortOrder=0, $configPrefix=null) {
        $this->ZMPlugin($title, $description, $sortOrder, $configPrefix);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get optional installation messages.
     *
     * @return array List of <code>ZMMessage</code> instances.
     */
    function getMessages() {
        return $this->messages_;
    }

    /**
     * Support generic getter method for plugin config values.
     *
     * <p>Supports <code>getXXX()</code> methods for all keys returned by <code>getKeys()</code>.</p>
     *
     * @param string name The property name.
     * @return mixed The value or <code>null</code>.
     */
    function __get($name) {
        $dname = strtoupper($this->configPrefix_ . $name);
        if (defined($dname)) {
            return constant($dname);
        }
        return null;
    }

    /**
     * Support to access plugin config values by name.
     *
     * @param string name The property name.
     * @return mixed The value or <code>null</code>.
     */
    function get($name) {
        return $this->__get($name);
    }

    /**
     * Get Id.
     *
     * @return string A unique id.
     */
    function getId() {
        return $this->code;
    }

    /**
     * Get name.
     *
     * @return string The name.
     */
    function getName() {
        return $this->title;
    }

    /**
     * Get description.
     *
     * @return string The description.
     */
    function getDescription() {
        return $this->description;
    }

    /**
     * Get the sort order of this plugin.
     *
     * @return int The sort order.
     */
    function getSortOrder() {
        return $this->sort_order;
    }

    /**
     * Check if the plugin is installed.
     *
     * @return int If this plugin is installed (code>1</code>).
     * @deperecated Use <code>isInstalled()</code> instead.
     */
    function check() {
        return $this->isInstalled() ? 1 : 0;
    }

    /**
     * Check if the plugin is installed.
     *
     * @return bool <code>true</code> if the plugin is installed, <code>false</code> if not.
     */
    function isInstalled() {
        if (null === $this->installed_) {
            if (null === $this->configPrefix_) {
                zm_backtrace('plugin without configuration prefix');
            }
            $sql = "select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key like :key";
            $sql = $this->getDB()->bindVars($sql, ":key", $this->configPrefix_.'%', "string");
            $results = $this->getDB()->Execute($sql);

            $this->installed_ = 0 < $results->RecordCount();
        }
        return $this->installed_;
    }

    /**
     * The list of configuration keys used by this plugin.
     *
     * @return array List of configuration keys.
     * @deperecated Use <code>isInstalled()</code> instead.
     */
    function keys() {
        return $this->getKeys();
    }

    /**
     * Get a list of configuration keys used by this plugin.
     *
     * <p>This default implementation will return the keys of automatically generated settings.</p.
     *
     * @return array List of configuration keys.
     */
    function getKeys() {
        return array_merge(array($this->configPrefix_.ZM_PLUGIN_ENABLED_SUFFIX, $this->configPrefix_.ZM_PLUGIN_SORT_ORDER_SUFFIX), $this->keys_);
    }

    /**
     * Set the list of configuration keys the actual implementation is using.
     *
     * @param array keys List of configuration keys with or without the config prefix.
     */
    function setKeys($keys) {
        foreach ($keys as $key) {
            if (!zm_starts_with($key, $this->configPrefix_)) {
                $key = strtoupper($this->configPrefix_ . $key);
            }
            array_push($this->keys_, $key);
        }
    }

    /**
     * Check if the plugin is enabled or not.
     *
     * @return bool <code>true</code> if the plugin is enabled, <code>false</code> if not.
     */
    function isEnabled() {
        if (null === $this->enabled_) {
            $sql = "select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = :key";
            $sql = $this->getDB()->bindVars($sql, ":key", $this->configPrefix_.ZM_PLUGIN_ENABLED_SUFFIX, "string");
            $results = $this->getDB()->Execute($sql);
            $lcvalue = strtolower($results->fields['configuration_value']);
            $this->enabled_ = zm_is_in_array($lcvalue, "1,true,yes,".strtolower($this->enabledValue_));
        }
        return $this->enabled_;
    }

    /**
     * Set the plugin type.
     *
     * @param string type The type.
     */
    function setType($type) { $this->type_ = $type; }

    /**
     * Get the plugin type.
     *
     * @return string The type.
     */
    function getType() { return $this->type_; }

    /**
     * Add a configuration value.
     *
     * <p>If no sort order is specified, entries will be listed in the order they are added. Effectively,
     * this means sort order can be easier accomplished by adding values in the order they should be
     * displayed.</p>
     *
     * @param string title The title.
     * @param string key The configuration key (with or without the common prefix).
     * @param string value The value.
     * @param string description The description; defaults to <code>''</code>.
     * @param string setFunction The set function; defaults to <code>null</code>.
     * @param string useFunction The use function; defaults to <code>null</code>.
     * @param int sortOrder The sort order; defaults to <code>0</code>.
     */
    function addConfigValue($title, $key, $value, $description='', $setFunction=null, $useFunction=null, $sortOrder=0) {
        $groupId = 6;
        if (!zm_starts_with($key, $this->configPrefix_)) {
            $key = $this->configPrefix_ . $key;
        }
        // keys are always upper case
        $key = strtoupper($key);

        $sql = "insert into " . TABLE_CONFIGURATION . " (
                  configuration_title, configuration_key, configuration_value, configuration_group_id,
                  configuration_description, sort_order, 
                  date_added, use_function, set_function)
                values (:title, :key, :value, :groupId,
                  :description, :sortOrder,
                  :dateAdded, :useFunction, :setFunction)";
        $sql = $this->getDB()->bindVars($sql, ":title", $title, "string");
        $sql = $this->getDB()->bindVars($sql, ":key", $key, "string");
        $sql = $this->getDB()->bindVars($sql, ":value", $value, "string");
        $sql = $this->getDB()->bindVars($sql, ":groupId", $groupId, "integer");
        $sql = $this->getDB()->bindVars($sql, ":description", $description, "string");
        $sql = $this->getDB()->bindVars($sql, ":sortOrder", $sortOrder, "integer");
        $sql = $this->getDB()->bindVars($sql, ":dateAdded", 'now()', "passthru");
        $sql = $this->getDB()->bindVars($sql, ":useFunction", $useFunction, "string");
        $sql = $this->getDB()->bindVars($sql, ":setFunction", $setFunction, "string");
        $results = $this->getDB()->Execute($sql);
    }

    /**
     * Install this plugin.
     *
     * <p>This default implementation will automatically create the following settings:</p>
     * <ul>
     *  <li>Enable/disable plugin</li>
     *  <li>Sort Order</li>
     * </ul>
     */
    function install() {
        // enable/disable drop down
        $enabledName = $this->configPrefix_.ZM_PLUGIN_ENABLED_SUFFIX;
        // zen-cart casts drop_down values to int!!
        /*
        $enabledValues = "array(array(\'id\' => \'".$this->enabledValue_."\', \'text\' => \'".$this->enabledValue_."\'),
          array(\'id\' => \'".$disabled."\', \'text\' => \'".$disabled."\'))";
        $this->addConfigValue('Plugin Status', $enabledName, $this->enabledValue_, 6, 'Enable/Disable this plugin.', 0,
          'zen_cfg_select_drop_down('.$enabledValues.', ');
         */
        $this->addConfigValue('Plugin Status', $enabledName, $this->enabledValue_, 'Change the plugin status.', 0,
          'zen_cfg_select_option(array(\''.$this->enabledValue_.'\', \''.'Disabled'.'\'), ');

        // plugin sort order
        $sortOrderName = $this->configPrefix_.ZM_PLUGIN_SORT_ORDER_SUFFIX;
        $this->addConfigValue('Sort Order', $sortOrderName, $this->sort_order, 'Display sort order.', 1);
    }

    /**
     * Remove this plugin.
     */
    function remove() {
        $sql = "delete from " . TABLE_CONFIGURATION . " where configuration_key like (:keys)";
        $sql = $this->getDB()->bindVars($sql, ":keys", $this->configPrefix_.'%', "string");
        $results = $this->getDB()->Execute($sql);
    }

    /**
     * Init this plugin.
     *
     * <p>This method is part of the lifecylce of a plugin during storefront request handling.</p>
     * <p>Code to set up internal resources should be placed here, rather than in the * constructor.</p>
     *
     * <p>This default implementation will load all plugin configuration values and create constants (<code>defines</code>).</p>
     */
    function init() {
        $sql = "select configuration_key, configuration_value
                from " . TABLE_CONFIGURATION . "
                where configuration_key like :key";
        $sql = $this->getDB()->bindVars($sql, ":key", $this->configPrefix_.'%', "string");
        $results = $this->getDB()->Execute($sql);
        while (!$results->EOF) {
            define($results->fields['configuration_key'], $results->fields['configuration_value']);
            $results->MoveNext();
        }
    }

    /**
     * Get all the config values.
     *
     * @return array A list of <code>ZMConfigValue</code> instances.
     */
    function getConfigValues() {
        $sql = "select configuration_id, configuration_title, configuration_key, configuration_value,
                configuration_description,
                use_function, set_function
                from " . TABLE_CONFIGURATION . " where configuration_key like :key
                order by configuration_id";
        $sql = $this->getDB()->bindVars($sql, ":key", $this->configPrefix_.'%', "string");
        $results = $this->getDB()->Execute($sql);

        $values = array();
        while (!$results->EOF) {
            $value = $this->_newConfigValue($results->fields);
            array_push($values, $value);
            $results->MoveNext();
        }
        return $values;
    }

    /**
     * Register this plugin as zen-cart zco subscriber.
     */
    function zcoSubscribe() {
    global $zm_events;

        $zm_events->attach($this);
    }

    /**
     * Un-register this plugin as zen-cart zco subscriber.
     */
    function zcoUnsubscribe() {
    global $zm_events;

        $zm_events->detach($this);
    }

    /**
     * Create new config value instance.
     */
    function _newConfigValue($fields) {
        $value =& $this->create("ConfigValue");
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
