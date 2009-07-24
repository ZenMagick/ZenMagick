<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Store plugin base class.
 *
 * <p>Plugins are <strong>NOT</strong> compatible with zen-cart modules.</p>
 *
 * <p>The plugin code (id) is based on the plugin class/file name.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.plugins
 * @version $Id: ZMPlugin.php 2308 2009-06-24 11:03:11Z dermanomann $
 */
class Plugin extends ZMPlugin {
    /** internal key constant */
    const KEY_PREFIX = 'PLUGIN_';
    /** internal key constant */
    const KEY_ENABLED = 'ENABLED';
    /** internal key constant */
    const KEY_SORT_ORDER = 'SORT_ORDER';

    /** Load plugin files for storefront only. */
    const SCOPE_STORE = 'store';
    /** Load plugin files for admin only. */
    const SCOPE_ADMIN = 'admin';
    /** Load plugin files for both storefront and admin. */
    const SCOPE_ALL =  'all';

    private $configPrefix_;
    private $enabledKey_;
    private $orderKey_;
    private $preferredSortOrder_;
    private $messages_ = null;
    private $scope_;


    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param string version The version.
     */
    function __construct($title='', $description='', $version='0.0') {
        parent::__construct();

        $this->setName($title);
        $this->setDescription($description);
        $this->setVersion($version);
        $this->setLoaderPolicy(ZMPlugin::LP_PLUGIN);
        $this->messages_ = array();
        $this->preferredSortOrder_ = 0;
        $this->scope_ = self::SCOPE_ALL;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get optional installation messages.
     *
     * @return array List of <code>ZMMessage</code> instances.
     */
    public function getMessages() {
        return $this->messages_;
    }

    /**
     * Get optional files to be loaded in global scope.
     *
     * <p>Files returned here would typically have an extension different to <em>.php</em> as otherwise
     * the loader will load them as static.</p> 
     *
     * <p>Global
     *
     * @return array List of filenames relative to the plugin location.
     */
    public function getGlobal() {
        return array();
    }

    /**
     * Support generic getter method for plugin config values.
     *
     * <p>Supports <code>getXXX()</code> methods for all keys.</p>
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
     * {@inheritDoc}
     *
     * <p>Here, the <code>$default</code> parameter is always ingnored.</p>
     */
    public function get($name, $default=null) {
        return $this->__get($name);
    }

    /**
     * Support generic setter method for plugin config values.
     *
     * <p>Supports <code>setXXX()</code> methods for all keys.</p>
     *
     * @param string name The property name.
     * @param mixed value The value.
     */
    function __set($name, $value) {
        $dname = strtoupper($this->configPrefix_ . $name);
        if (defined($dname)) {
            ZMConfig::instance()->updateConfigValue($dname, $value);
        } else {
            ZMLogging::instance()->trace('invalid plugin config key: '.$dname, ZMLogging::TRACE);
        }
    }

    /**
     * Support to set plugin config values by name.
     *
     * @param string name The property name.
     * @param mixed value The value.
     */
    public function set($name, $value) {
        $this->__set($name, $value);
    }

    /**
     * Set the preferred sort order.
     *
     * @param int sortOrder The preferred sort order.
     */
    public function setPreferredSortOrder($sortOrder) {
        $this->preferredSortOrder_ = $sortOrder;
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
    public function install() {
        $this->addConfigValue('Plugin Enabled', self::KEY_ENABLED, 'true', 'Enable/disable this plugin',
            'widget@BooleanFormWidget#name='.self::KEY_ENABLED.'&default=true&label.true=Enabled&label.false=Disabled&style=checkbox');
        $this->addConfigValue('Plugin sort order', self::KEY_SORT_ORDER, $this->preferredSortOrder_, 'Controls the execution order of plugins',
            'widget@TextFormWidget#name='.self::KEY_SORT_ORDER.'&default=0&size=6&maxlength=5');
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    public function remove($keepSettings=false) {
        $config = ZMConfig::instance();

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
    public function init() {
    }

    /**
     * Check if the plugin is installed.
     *
     * @return boolean <code>true</code> if the plugin is installed, <code>false</code> if not.
     */
    public function isInstalled() {
        return null !== $this->__get(self::KEY_ENABLED);
    }

    /**
     * Check if the plugin is enabled.
     *
     * @return boolean <code>true</code> if the plugin is enabled, <code>false</code> if not.
     */
    public function isEnabled() {
        $enabled = $this->get(self::KEY_ENABLED);
        return null !== $enabled && ZMLangUtils::asBoolean($enabled);
    }

    /**
     * Get the sort order.
     *
     * @return int The sort order index.
     */
    public function getSortOrder() { return (int)$this->get(self::KEY_SORT_ORDER); }

    /**
     * Set the sort order.
     *
     * @param int sortOrder The sort order index.
     */
    public function setSortOrder($sortOrder) { $this->set(self::KEY_SORT_ORDER, $sortOrder); }

    /**
     * {@inheritDoc}
     */
    public function setGroup($group) { 
        parent::setGroup($group);
        $this->configPrefix_ = strtoupper(self::KEY_PREFIX . $group . '_'. $this->getId() . '_');
        $this->enabledKey_ = $this->configPrefix_.self::KEY_ENABLED;
        $this->orderKey_ = $this->configPrefix_.self::KEY_SORT_ORDER;
    }

    /**
     * Get a plugin config file path.
     *
     * <p>Return a fully qualified filename; resolved either against the plugin directory or <code>config/</code>.
     * If neither file exists, the <code>config/</code> based filename is returned.</p>
     *
     * @param string file The filename.
     * @return string A fully qualified filename.
     */
    public function getConfigPath($file) { 
        $configPath = Runtime::getInstallationPath().'config'.DIRECTORY_SEPARATOR;
        $configFile = $configPath.$this->getId().DIRECTORY_SEPARATOR.$file;

        if (file_exists($configFile) || !file_exists($this->getPluginDirectory().$file)) {
            return $configFile;
        }

        return $this->getPluginDirectory().$file;
    }

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
    public function addConfigValue($title, $key, $value, $description='', $setFunction=null, $useFunction=null, $sortOrder=0) {
        if (!ZMLangUtils::startsWith($key, $this->configPrefix_)) {
            $key = $this->configPrefix_ . $key;
        }
        // keys are always upper case
        $key = strtoupper($key);
        // XXX: not a great test but will work while being based on zen-cart configuration
        if (!defined($key)) {
            // ZENMAGICK_PLUGIN_GROUP_ID is created via config.sql SQL
            ZMConfig::instance()->createConfigValue($title, $key, $value, ZENMAGICK_PLUGIN_GROUP_ID, $description, $sortOrder, $setFunction, $useFunction);
        }
    }

    /**
     * Get all the config values.
     *
     * @return array A list of <code>ZMConfigValue</code> instances.
     */
    public function getConfigValues() {
        return ZMConfig::instance()->getConfigValues($this->configPrefix_.'%');
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
     * @param string menuKey Optional key determining where the menu item should appear; default is <em>ZMAdminMenu::MENU_PLUGINS</em>.
     */
    public function addMenuItem($id, $title, $function, $menuKey=ZMAdminMenu::MENU_PLUGINS) {
        if (ZMRequest::instance()->isAdmin()) {
            ZMAdminMenu::addItem(ZMLoader::make("AdminMenuItem", $menuKey, $id, $title, 'zmPluginPage.php', $function));
        }
    }

    /**
     * Set the scope.
     *
     * <p>This determines where a plugin is active. Allowed values are:</p>
     * <ul>
     *  <li><em>store</em>
     *   <br>Plugin only active in storefront requests.</li>
     *  <li><em>admin</em>
     *   <br>Plugin only active in admin request.</li>
     *  <li><em>all</em>
     *   <br>Plugin active for all requests.</li>
     * </ul>
     *
     * <p>Please note that there are constants that may be used intead of plain strings:</p>
     * <ul>
     *  <li><code>self::SCOPE_STORE</code></li>
     *  <li><code>self::SCOPE_ADMIN</code></li>
     *  <li><code>self::SCOPE_ALL</code></li>
     * </ul>
     *
     * <p>The default scope is <li><code>self::SCOPE_ALL</code></li>.</p>
     * @param string scope The scope.
     */
    public function setScope($scope) { $this->scope_ = $scope; }

    /**
     * Get this plugins scope.
     *
     * @return string The scope.
     */
    public function getScope() { return $this->scope_; }

    /**
     * Resolve a plugin relative URI.
     *
     * <p>The given <code>uri</code> is assumed to be relative to the plugin folder.</p>
     *
     * @param string uri The relative URI.
     * @param boolean echo If <code>true</code>, the URL will be echo'ed as well as returned.
     * @return string An absolute URL or <code>null</code>.
     */
    public function pluginURL($uri, $echo=ZM_ECHO_DEFAULT) {
        if (null == $this->getPluginDirectory()) {
            throw new ZMException('pluginDirectory missing');
        }

        $type = basename(dirname($this->getPluginDirectory()));
        $url = ZMToolbox::instance()->html->encode(Runtime::getPluginPathPrefix() . $type . '/' . $this->getId() . '/' . $uri, false);

        if ($echo) echo $url;
        return $url;
    }

}

?>
