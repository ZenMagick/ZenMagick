<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package zenmagick.store.shared.services.plugins
 */
class Plugin extends ZMPlugin {
    /** internal key constant */
    const KEY_PREFIX = 'PLUGIN_';
    /** internal key constant */
    const KEY_ENABLED = 'ENABLED';
    /** internal key constant */
    const KEY_SORT_ORDER = 'SORT_ORDER';

    /** Store context. */
    const CONTEXT_STOREFRONT = 1;
    /** Admin context. */
    const CONTEXT_ADMIN = 2;

    private $configPrefix_;
    private $enabledKey_;
    private $orderKey_;
    private $preferredSortOrder_;
    private $messages_ = null;


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
        // both
        $this->setContext(self::CONTEXT_STOREFRONT|self::CONTEXT_ADMIN);
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
     * @param ZMRequest request The current request.
     * @return array List of filenames relative to the plugin location.
     */
    public function getGlobal($request) {
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
            'widget@BooleanFormWidget#name='.self::KEY_ENABLED.'&default=true&label.true=Enabled&label.false=Disabled&style=checkbox', 0);
        $this->addConfigValue('Plugin sort order', self::KEY_SORT_ORDER, $this->preferredSortOrder_, 'Controls the execution order of plugins',
            'widget@TextFormWidget#name='.self::KEY_SORT_ORDER.'&default=0&size=6&maxlength=5', 0);
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    public function remove($keepSettings=false) {
        $config = ZMConfig::instance();

        // always remove these keys
        $config->removeConfigValue($this->enabledKey_);
        //$config->removeConfigValue($this->orderKey_);

        if (!$keepSettings) {
            $config->removeConfigValues($this->configPrefix_.'%');
        }
    }

    /**
     * Upgrade this plugin.
     *
     * @return boolean <code>true</code> on success.
     */
    public function upgrade() {
        $this->remove(true);
        $this->install();
        return true;
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
     * Set the status.
     *
     * @param boolean status The new status.
     */
    public function setEnabled($status) {
        $this->set(self::KEY_ENABLED, $status);
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
     * @param string widget The widget definitio; default is <code>null</code> for a default text field.
     * @param int sortOrder The sort order; defaults to <code>0</code>.
     */
    public function addConfigValue($title, $key, $value, $description='', $widget=null, $sortOrder=1) {
        if (null == $widget) {
            // do this first before fiddling with $key
            $widget = 'widget@TextFormWidget#name='.$key.'&default=&size=12&maxlength=56';
        }

        if (!ZMLangUtils::startsWith($key, $this->configPrefix_)) {
            $key = $this->configPrefix_ . $key;
        }
        // keys are always upper case
        $key = strtoupper($key);

        $tmp = ZMConfig::instance()->getConfigValues($key);
        // check if value exists
        if (0 == count($tmp)) {
            // ZENMAGICK_PLUGIN_GROUP_ID is created via config.sql SQL
            ZMConfig::instance()->createConfigValue($title, $key, $value, ZENMAGICK_PLUGIN_GROUP_ID, $description, $sortOrder, $widget);
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
        if (ZMSettings::get('isAdmin')) {
            ZMAdminMenu::addItem(ZMLoader::make("AdminMenuItem", $menuKey, $id, $title, $function));
        }
    }

    /**
     * Add custom plugin admin page to admin navigation.
     *
     * <p>Plugins are expected to implement a corresponding controller for the configured reuqestId.</p>
     *
     * @param string id The page id.
     * @param string title The page title.
     * @param string function The function to render the contents.
     * @param string menuKey Optional key determining where the menu item should appear; default is <em>ZMAdminMenu::MENU_PLUGINS</em>.
     */
    public function addMenuItem2($title, $requestId, $menuKey=ZMAdminMenu::MENU_PLUGINS) {
        if (ZMSettings::get('isAdmin')) {
            ZMAdminMenu::addItem(ZMLoader::make("AdminMenuItem2", $menuKey, $requestId, $title, $requestId));
        }
    }

    /**
     * Resolve a plugin relative URI.
     *
     * <p>The given <code>uri</code> is assumed to be relative to the plugin folder.</p>
     *
     * @param string uri The relative URI.
     * @return string An absolute URL or <code>null</code>.
     */
    public function pluginURL($uri) {
        if (null == $this->getPluginDirectory()) {
            throw new ZMException('pluginDirectory missing');
        }

        $type = basename(dirname($this->getPluginDirectory()));
        return ZMHtmlUtils::encode(Runtime::getPluginPathPrefix() . $type . '/' . $this->getId() . '/' . $uri);
    }

    /**
     * Check if this plugin has options configurable via the default plugin options dialog.
     *
     * @return boolean <code>true</code> if options are available.
     */
    public function hasOptions() {
        return 2 < count($this->getConfigValues());
    }

    /**
     * Get admin menu.
     *
     * @return ZMAdminMenu An admin menu instance or <code>null</code> if not available.
     */
    public function getAdminMenu() {
        if (ZMSettings::get('isAdmin')) {
            return ZMAdminMenu::instance();
        }

        return null;
    }

}
