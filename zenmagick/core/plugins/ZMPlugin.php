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
 * <p>Plugins are <strong>NOT</strong> compatible with zen-cart modules. If you need that,
 * please have a look at <code>ZMModule</code>.</p>
 *
 * <p>The plugin code (id) is based on the plugin class/file name.</p>
 *
 * <p>When using the default install method to generate common plugin settings, make sure that
 * additional configuration settings do not use a sortOrder <em>&lt; 2</em>, as <em>0</em>
 * and <em>1</em> are used for those.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.plugins
 * @version $Id$
 */
class ZMPlugin extends ZMService {
    var $id_;
    var $title_;
    var $description_;
    var $installed_;
    var $enabled_;
    var $configPrefix_;
    var $enabledValue_;
    var $keys_;
    var $type_;
    var $messages_ = null;
    var $pluginDir_ = null;
    var $loaderSupport_;
    var $handler_;


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

        $this->type_ = 'request';
        $this->id_ = get_class($this);
        $this->title_ = $title;
        $this->description_ = $description;
        $this->enabledValue_ = zm_l10n_get('Enabled');
        $this->sort_order = $sortOrder;
        $this->installed_ = null;
        $this->enabled_ = null;
        $this->configPrefix_ = $configPrefix;
        if (null === $this->configPrefix_) {
            $this->configPrefix_ = ZM_PLUGIN_PREFIX . $this->type_ . '_'. $this->id_ . '_';
        }
        $this->configPrefix_ = strtoupper($this->configPrefix_);
        $this->keys_ = array();
        if (defined($this->configPrefix_.ZM_PLUGIN_SORT_ORDER_SUFFIX)) {
            $this->sort_order = constant($this->configPrefix_.ZM_PLUGIN_SORT_ORDER_SUFFIX);
        }
        $this->messages_ = array();
        $this->pluginDir_ = null;
        $this->loaderSupport_ = 'PLUGIN';
        $this->handler_ = null;
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
        return $this->id_;
    }

    /**
     * Get name.
     *
     * @return string The name.
     */
    function getName() {
        return $this->title_;
    }

    /**
     * Get description.
     *
     * @return string The description.
     */
    function getDescription() {
        return $this->description_;
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
     * @return bool <code>true</code> if the plugin is installed, <code>false</code> if not.
     */
    function isInstalled() {
        if (null === $this->installed_) {
            if (null === $this->configPrefix_) {
                zm_backtrace('plugin without configuration prefix');
            }
            $db = $this->getDB();
            $sql = "select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key like :key";
            $sql = $db->bindVars($sql, ":key", $this->configPrefix_.'%', "string");
            $results = $db->Execute($sql);

            $this->installed_ = 0 < $results->RecordCount();
        }
        return $this->installed_;
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
            $db = $this->getDB();
            $sql = "select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = :key";
            $sql = $db->bindVars($sql, ":key", $this->configPrefix_.ZM_PLUGIN_ENABLED_SUFFIX, "string");
            $results = $db->Execute($sql);
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
     * Set the plugin directory.
     *
     * @param string pluginDir The directory.
     */
    function setPluginDir($pluginDir) { $this->pluginDir_ = $pluginDir; }

    /**
     * Get the plugin directory.
     *
     * @return string The directory or <code>null</code> if this plugin is single file only.
     */
    function getPluginDir() { return $this->pluginDir_; }

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
        $sql = $db->bindVars($sql, ":useFunction", $useFunction, "string");
        $sql = $db->bindVars($sql, ":setFunction", $setFunction, "string");
        $results = $db->Execute($sql);
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
        $this->addConfigValue('Plugin Status', $enabledName, $this->enabledValue_, 'Change the plugin status.',
          'zen_cfg_select_option(array(\''.$this->enabledValue_.'\', \''.'Disabled'.'\'), ');

        // plugin sort order
        //$sortOrderName = $this->configPrefix_.ZM_PLUGIN_SORT_ORDER_SUFFIX;
        //$this->addConfigValue('Sort Order', $sortOrderName, $this->sort_order, 'Display sort order.');
    }

    /**
     * Remove this plugin.
     */
    function remove() {
        $db = $this->getDB();
        $sql = "delete from " . TABLE_CONFIGURATION . " where configuration_key like (:keys)";
        $sql = $db->bindVars($sql, ":keys", $this->configPrefix_.'%', "string");
        $results = $db->Execute($sql);
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
        $db = $this->getDB();
        $sql = "select configuration_key, configuration_value
                from " . TABLE_CONFIGURATION . "
                where configuration_key like :key";
        $sql = $db->bindVars($sql, ":key", $this->configPrefix_.'%', "string");
        $results = $db->Execute($sql);
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
        $db = $this->getDB();
        $sql = "select configuration_id, configuration_title, configuration_key, configuration_value,
                configuration_description,
                use_function, set_function
                from " . TABLE_CONFIGURATION . " where configuration_key like :key
                order by configuration_id";
        $sql = $db->bindVars($sql, ":key", $this->configPrefix_.'%', "string");
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
     * Create the plugin handler.
     *
     * <p>This is the method to be implemented by plugins that require a handler.</p>
     *
     * @return ZMPluginHandler A <code>ZMPluginHandler</code> instance or <code>null</code> if
     *  not supported.
     */
    function &createPluginHandler() {
        return null;
    }

    /**
     * Get the plugin handler.
     *
     * @return ZMPluginHandler A <code>ZMPluginHandler</code> instance or <code>null</code> if
     *  not supported.
     */
    function &getPluginHandler() {
        if (null == $this->handler_) {
            $this->handler_ = $this->createPluginHandler();
        }
        return $this->handler_;
    }

    /**
     * Set the plugin handler.
     *
     * @param ZMPluginHandler handler A <code>ZMPluginHandler</code> instance.
     */
    function setPluginHandler(&$handler) {
        return $this->handler_ =& $handler;
    }

    /**
     * Add plugin maintenance screen to navigation.
     *
     * <p>The provided function is free to implement content generation in one of two different
     * ways:</p>
     * <ol>
     *   <li>BASIC:<br>
     *     The page contents is generated as-is. No output buffering or similar. Expected return value
     *     is <code>null</code>.</li>
     *   <lI>ADVANCED:<br>
     *     Content is not generated directly, but included as part of the returned <code>ZMPluginPage</code>
     *     instance.</li>
     * </ol> 
     *
     * @param string id The page id.
     * @param string title The page title.
     * @param string function The function to render the contents.
     */
    function addMenuItem($id, $title, $function) {
    global $zm_request;

        if ($zm_request->isAdmin()) {
            zm_add_menu_item(new ZMMenuItem('plugins', $id, $title, null, $function));
        }
    }

    /**
     * Get the loader support flag for this plugin.
     *
     * <p>This flag tells the core compresser the extend of support for adding this plugin
     * to a compressed version of <code>core.php</code>. Valid values are:</p>
     * <dl>
     *   <dt>NONE</dt><dd>Not supported.</dd>
     *   <dt>PLUGIN</dt><dd>Only the plugin class may be added; this is the default.</dd>
     *   <dt>ALL</dt><dd>All (<code>.php</code>) files can be added to <code>core.php</code>.</dd>
     * </dl>
     *
     * @return string The loader support flag.
     */
    function getLoaderSupport() { return $this->loaderSupport_; }

    /**
     * Set the loader support flag for this plugin.
     *
     * @param string loaderSupport The loader support flag.
     */
    function setLoaderSupport($loaderSupport) { $this->loaderSupport_ = $loaderSupport; }

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
