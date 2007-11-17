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
 * @author mano
 * @package org.zenmagick.plugins
 * @version $Id$
 */
class ZMPlugin extends ZMObject {
    var $id_;
    var $title_;
    var $description_;
    var $version_;
    var $installed_;
    var $configPrefix_;
    var $enabledKey_;
    var $orderKey_;
    var $keys_;
    var $type_;
    var $messages_ = null;
    var $pluginDir_ = null;
    var $loaderSupport_;
    var $handler_;
    var $traditional_;


    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param string version The version.
     * @param string type The plugin type; default is <em>request</em>.
     */
    function ZMPlugin($title='', $description='', $version='0.0', $type='request') {
        parent::__construct();

        $this->id_ = get_class($this);
        $this->title_ = $title;
        $this->description_ = $description;
        $this->version_ = $version;
        $this->type_ = $this->setType($type);
        $this->keys_ = array();
        $this->messages_ = array();
        $this->pluginDir_ = null;
        $this->loaderSupport_ = 'PLUGIN';
        $this->handler_ = null;
        $this->traditional_ = true;
    }

    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param string version The version.
     * @param string type The plugin type; default is <em>request</em>.
     */
    function __construct($title='', $description='', $version='0.0', $type='request') {
        $this->ZMPlugin($title, $description, $version, $type);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get a <code>db</code> instance.
     *
     * @return queryFactory A <code>queryFactory</code> instance.
     */
    function getDB() {
    global $zm_runtime;

        return $zm_runtime->getDB();
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
     * Support generic setter method for plugin config values.
     *
     * <p>Supports <code>setXXX()</code> methods for all keys returned by <code>getKeys()</code>.</p>
     *
     * @param string name The property name.
     * @param mixed value The value.
     */
    function __set($name, $value) {
        $dname = strtoupper($this->configPrefix_ . $name);
        if (defined($dname)) {
            $config = new ZMConfig();
            $config->updateConfigValue($dname, $value);
        }
    }

    /**
     * Support to set plugin config values by name.
     *
     * @param string name The property name.
     * @param mixed value The value.
     */
    function set($name, $value) {
        $this->__set($name, $value);
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
     * Get version.
     *
     * @return string The version.
     */
    function getVersion() {
        return $this->version_;
    }

    /**
     * Get the traditional flag.
     *
     * @return boolean <code>true</code> if this plugin required traditional configuration handling, <code>false</code> if not.
     */
    function isTraditional() {
        return $this->traditional_;
    }

    /**
     * Set the traditional flag.
     *
     * @param boolean traditional <code>true</code> if this plugin required traditional configuration handling, <code>false</code> if not.
     */
    function setTraditional($traditional) {
        $this->traditional_ = $traditional;
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
        $this->addConfigValue('Plugin Status', $this->enabledKey_, true,
            zm_l10n_get('Enable/disable this plugin.'),
            "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>'Enabled'), array('id'=>'0', 'text'=>'Disabled')), ");
        $this->addConfigValue('Plugin sort order', $this->orderKey_, 0,
            zm_l10n_get('Controls the execution order of plugins.'));
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        $config = new ZMConfig();

        // always remove enable/disable key
        $config->removeConfigValue($this->enabledKey_);
        $config->removeConfigValue($this->orderKey_);

        if (!$keepSettings) {
            $config->removeConfigValues($this->configPrefix_.'%');
        }
    }

    /**
     * Init this plugin.
     *
     * <p>This method is part of the lifecylce of a plugin during storefront request handling.</p>
     * <p>Code to set up internal resources should be placed here, rather than in the * constructor.</p>
     */
    function init() {
    }

    /**
     * Check if the plugin is installed.
     *
     * @return boolean <code>true</code> if the plugin is installed, <code>false</code> if not.
     */
    function isInstalled() {
        return null !== $this->__get(ZM_PLUGIN_ENABLED_SUFFIX);
    }

    /**
     * Check if the plugin is enabled.
     *
     * @return boolean <code>true</code> if the plugin is enabled, <code>false</code> if not.
     */
    function isEnabled() {
        $enabled = $this->get(ZM_PLUGIN_ENABLED_SUFFIX);
        return null !== $enabled && 0 != $enabled;
    }

    /**
     * Get the order index.
     *
     * @return int The order index.
     */
    function getOrder() { return (int)$this->get(ZM_PLUGIN_ORDER_SUFFIX); }

    /**
     * Get a list of configuration keys used by this plugin.
     *
     * @return array List of configuration keys.
     */
    function getKeys() {
        return $this->keys_;
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
     * Get the plugin type.
     *
     * @return string The type.
     */
    function getType() { return $this->type_; }

    /**
     * Set the plugin type.
     *
     * @param string type The type.
     */
    function setType($type) { 
        $this->type_ = $type; 
        $this->configPrefix_ = strtoupper(ZM_PLUGIN_PREFIX . $this->type_ . '_'. $this->id_ . '_');
        $this->enabledKey_ = $this->configPrefix_.ZM_PLUGIN_ENABLED_SUFFIX;
        $this->orderKey_ = $this->configPrefix_.ZM_PLUGIN_ORDER_SUFFIX;
    }

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
        $groupId = ZENMAGICK_PLUGIN_GROUP_ID;
        if (!zm_starts_with($key, $this->configPrefix_)) {
            $key = $this->configPrefix_ . $key;
        }
        // keys are always upper case
        $key = strtoupper($key);

        $config = new ZMConfig();
        $config->createConfigValue($title, $key, $value, ZENMAGICK_PLUGIN_GROUP_ID, $description, $sortOrder, $setFunction, $useFunction);
    }

    /**
     * Get all the config values.
     *
     * @param boolean prefix If <code>true</code>, the plugin prefix will be kept, otherwise it will be stripped.
     * @return array A list of <code>ZMConfigValue</code> instances.
     */
    function getConfigValues($prefix=true) {
        $config = new ZMConfig();
        $values = $config->getConfigValues($this->configPrefix_.'%');
        if (!$prefix) {
            foreach ($values as $name => $value) {
                $key = $value->getKey();
                $values[$name]->setKey(str_replace($this->configPrefix_, '', $key));
            }
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

}

?>
