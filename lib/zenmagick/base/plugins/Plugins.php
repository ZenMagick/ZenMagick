<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\base\plugins;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;
use zenmagick\apps\store\utils\ContextConfigLoader;

/**
 * Basic plugin service.
 *
 * Plugins consist of a directory containing either
 * a plugin.yaml file or a named like FooBarPlugin.php
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Plugins extends ZMObject {
    const PLUGIN_BASE_NAMESPACE = 'zenmagick\plugins';
    protected $plugins;
    protected $statusMap;
    protected $pluginStatusMapBuilder;
    protected $contextConfigLoader;


    /**
     * Create new instance.
     */
    public function __construct($pluginStatusMapBuilder, $contextConfigLoader) {
        parent::__construct();
        $this->pluginStatusMapBuilder = $pluginStatusMapBuilder;
        $this->contextConfigLoader = $contextConfigLoader;
        $this->plugins = array();
        $this->statusMap = null;
    }


    /**
     * Get plugin status map.
     *
     * @return array Plugin status map.
     */
    protected function getStatusMap() {
        if (null === $this->statusMap) {
            $this->statusMap = $this->pluginStatusMapBuilder->getStatusMap();
        }
        return $this->statusMap;
    }

    /**
     * Get all plugins for the given context.
     *
     * @param int context Optional context flag; default is <code>null</code> for current context.
     * @param boolean enabled If <code>true</code>, return only enabled plugins; default is <code>true</code>.
     * @return array List of initialized plugins.
     */
    public function getPluginsForContext($context=null, $enabled=true) {
        $context = $context ?: $this->contextConfigLoader->getContext();
        return $this->getPlugins($context, $enabled);
    }

    /**
     * Get all plugins.
     *
     * @param boolean enabled If <code>true</code>, return only enabled plugins; default is <code>true</code>.
     * @return array List of initialized plugins.
     */
    public function getAllPlugins($enabled=true) {
        return $this->getPlugins(null, $enabled);
    }

    /**
     * Get plugins for the given context.
     *
     * @param int context Optional context flag; default is <code>null</code> for all.
     * @param boolean enabled If <code>true</code>, return only enabled plugins; default is <code>true</code>.
     * @return array List of initialized plugins.
     */
    protected function getPlugins($context=null, $enabled=true) {
        $plugins = array();
        foreach ($this->getStatusMap() as $id => $status) {
            if (array_key_exists($id, $this->plugins)) {
                $plugins[$id] = $this->plugins[$id];
                continue;
            }
            $meta = $status['meta'];
            if (($meta['enabled'] || !$enabled) && (null === $context || Runtime::isContextMatch($meta['context'], $context))) {
                $plugin = new $meta['class']($status);
                $plugin->setContainer($this->container);
                if ($plugin->isEnabled() && Runtime::isContextMatch($plugin->getContext(), $context)) {
                    $this->contextConfigLoader->setConfig($status);
                    $config = $this->contextConfigLoader->process();
                    if (array_key_exists('autoload', $config)) { // Fold this into process() once it knows about pluginDir
                        $this->contextConfigLoader->registerAutoLoaders($config['autoload'], $plugin->getPluginDirectory());
                    }

                    // @todo make obsolete
                    $plugin->init();
                }

                $this->plugins[$id] = $plugins[$id] = $plugin;
            }
        }

        return $plugins;
    }

    /**
     * Get the plugin for the given id.
     *
     * @param string id The plugin id.
     * @param boolean force Optional flag to bypass context/enabled restrictions; default is <code>false</code>.
     * @return Plugin A plugin instance or <code>null</code>.
     */
    public function getPluginForId($id, $force = false) {
        $plugins = $force ? $this->getPlugins(null, false) : $this->getPluginsForContext();
        if (array_key_exists($id, $plugins)) {
            return $plugins[$id];
        }

        return null;
    }

}
