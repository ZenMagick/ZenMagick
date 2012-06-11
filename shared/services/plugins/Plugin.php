<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMException;

use zenmagick\apps\store\menu\MenuElement;

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
class Plugin extends zenmagick\http\plugins\HttpPlugin {
    /** internal key constant */
    const KEY_PREFIX = 'PLUGIN_';
    /** internal key constant */
    const KEY_ENABLED = 'ENABLED';
    /** internal key constant */
    const KEY_SORT_ORDER = 'SORT_ORDER';

    private $configPrefix_;
    private $configValues_;
    private $enabledKey_;
    private $orderKey_;
    private $messages_ = null;


    /**
     * Create new plugin.
     *
     * @param string title The title.
     * @param string description The description.
     * @param string version The version.
     */
    public function __construct($title='', $description='', $version='0.0') {
        parent::__construct();
        $this->setName($title);
        $this->setDescription($description);
        $this->setVersion($version);
        $this->messages_ = array();
        $this->configValues_ = null;
        // all
        $this->setContext('admin,storefront');
    }


    /**
     * {@inheritDoc}
     */
    public function setId($id) {
        parent::setId($id);

        $this->configPrefix_ = strtoupper(self::KEY_PREFIX . $id . '_');
        $this->enabledKey_ = $this->configPrefix_.self::KEY_ENABLED;
        $this->orderKey_ = $this->configPrefix_.self::KEY_SORT_ORDER;

        foreach ($this->getConfigValues() as $configValue) {
            $this->set($configValue->getName(), $configValue->getValue());
        }
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
     * @param Request request The current request.
     * @return array List of filenames relative to the plugin location.
     */
    public function getGlobal($request) {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function __get($name) {
        if (array_key_exists($name, $this->configValues_)) {
            // config value
            return $this->configValues_[$name]->getValue();
        }

        // regular dynamic property
        return parent::__get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $default=null) {
        return $this->__get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function __set($name, $value) {
        if ($this->configValues_ && array_key_exists($name, $this->configValues_)) {
            $dname = strtoupper($this->configPrefix_ . $name);
            $this->container->get('configService')->updateConfigValue($dname, $value);
        } else {
            // regular dynamic property
            parent::__set($name, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value) {
        $this->__set($name, $value);
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
            'widget@booleanFormWidget#name='.self::KEY_ENABLED.'&default=true&label.true=Enabled&label.false=Disabled&style=checkbox', 0);
        $this->addConfigValue('Plugin sort order', self::KEY_SORT_ORDER, $this->getPreferredSortOrder(), 'Controls the execution order of plugins',
            'widget@textFormWidget#name='.self::KEY_SORT_ORDER.'&default=0&size=6&maxlength=5', 0);
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    public function remove($keepSettings=false) {
        $configService = $this->container->get('configService');

        // always remove these keys
        $configService->removeConfigValue($this->enabledKey_);
        //$configService->removeConfigValue($this->orderKey_);

        if (!$keepSettings) {
            $configService->removeConfigValues($this->configPrefix_.'%');
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
        return null !== $enabled && Toolbox::asBoolean($enabled);
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
     * {@inheritDoc}
     */
    public function getSortOrder() { return (int)$this->get(self::KEY_SORT_ORDER); }

    /**
     * {@inheritDoc}
     */
    public function setSortOrder($sortOrder) { $this->set(self::KEY_SORT_ORDER, $sortOrder); }

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
     * @param int sortOrder The sort order; defaults to <code>1</code>.
     */
    public function addConfigValue($title, $key, $value, $description='', $widget=null, $sortOrder=1) {
        if (null == $widget) {
            // do this first before fiddling with $key
            $widget = 'widget@textFormWidget#name='.$key.'&default=&size=12&maxlength=56';
        }

        if (0 !== strpos($key, $this->configPrefix_)) {
            $key = $this->configPrefix_ . $key;
        }
        // keys are always upper case
        $key = strtoupper($key);

        $tmp = $this->container->get('configService')->getConfigValues($key);
        // check if value exists
        if (0 == count($tmp)) {
            // ZENMAGICK_PLUGIN_GROUP_ID is created via config.sql SQL
            $this->container->get('configService')->createConfigValue($title, $key, $value, ZENMAGICK_PLUGIN_GROUP_ID, $description, $sortOrder, $widget);
        }
    }

    /**
     * Get all the config values.
     *
     * @return array A list of <code>ConfigValue</code> instances.
     */
    public function getConfigValues() {
        if (null === $this->configValues_) {
            $this->configValues_ = array();
            foreach ($this->container->get('configWidgetService')->getConfigValues($this->configPrefix_.'%') as $configValue) {
                $this->configValues_[$configValue->getName()] = $configValue;
            }
        }
        return $this->configValues_;
    }

    /**
     * Add new plugin menu group.
     *
     * @param string title The page title.
     * @param string parentid Optional parent id; default is <em>plugins</em>.
     * @return string The menu key to be used to add items to this group.
     * @todo: fix and allow optional other parameter, etc...
     */
    public function addMenuGroup($title, $parentId='configuration') {
        if ($this->container->has('adminMenu')) {
            $adminMenu = $this->container->get('adminMenu');
            if (null != ($parent = $adminMenu->getElement($parentId))) {
                $id = $parentId.'-'.$this->getId().microtime();
                $item = new MenuElement($id, $title);
                $parent->addChild($item);
                return $id;
            }
        }
        return null;
    }

    /**
     * Add custom plugin admin page to admin navigation.
     *
     * <p>Plugins are expected to implement a corresponding controller for the configured reuqestId.</p>
     *
     * @param string title The page title.
     * @param string requestId The corresponding requestId.
     * @param string menuKey Optional key determining where the menu item should appear; default is <em>'configuration-plugins'</em>.
     */
    public function addMenuItem($title, $requestId, $menuKey='configuration-plugins') {
        if ($this->container->has('adminMenu')) {
            $adminMenu = $this->container->get('adminMenu');
            if (null != ($parent = $adminMenu->getElement($menuKey))) {
                $item = new MenuElement($menuKey.'-'.$requestId, $title);
                $item->setRequestId($requestId);
                $parent->addChild($item);
            }
        }
    }

    /**
     * Resolve a plugin relative URI.
     *
     * <p>The given <code>uri</code> is assumed to be relative to the plugin folder.</p>
     *
     * @param string uri The relative URI.
     * @return string An absolute URI or <code>null</code>.
     */
    public function pluginURL($uri) {
        if (null == $this->getPluginDirectory()) {
            throw new ZMException('pluginDirectory missing');
        }

        $path = $this->getPluginDirectory().'/'.$uri;
        $templateView = $this->container->get('defaultView');
        return $templateView->getResourceManager()->file2uri($path);
    }

    /**
     * Check if this plugin has options configurable via the default plugin options dialog.
     *
     * @return boolean <code>true</code> if options are available.
     */
    public function hasOptions() {
        $count = 0;
        foreach ($this->getConfigValues() as $value) {
            if (!$value->isHidden()) {
                ++$count;
            }
        }
        return 2 < $count;
    }

    /**
     * Get dependencies.
     *
     * @return array List of plugin names this plugin depends on.
     */
    public function getDependencies() {
        return array();
    }

}
