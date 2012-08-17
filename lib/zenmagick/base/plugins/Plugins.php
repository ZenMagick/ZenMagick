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
use zenmagick\base\cache\Cache;
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
    const STATUS_MAP_KEY = 'zenmagick.plugins.status_map';
    protected $plugins;
    protected $cache;
    protected $statusMap;
    protected $loggingService;
    protected $pluginStatusMapBuilder;
    protected $localeService;
    protected $contextConfigLoader;


    /**
     * Create new instance.
     */
    public function __construct($loggingService, $pluginStatusMapBuilder, $localeService, $contextConfigLoader) {
        parent::__construct();
        $this->loggingService = $loggingService;
        $this->pluginStatusMapBuilder = $pluginStatusMapBuilder;
        $this->localeService = $localeService;
        $this->contextConfigLoader = $contextConfigLoader;
        $this->plugins = array();
        $this->cache = null;
        $this->statusMap = null;
    }


    /**
     * Set the cache.
     *
     * @param zenmagick\base\cache\Cache cache The cache.
     */
    public function setCache(Cache $cache) {
        $this->cache = $cache;
    }

    /**
     * Get the cache.
     *
     * @return zenmagick\base\cache\Cache The cache.
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * Refresh plugin status map.
     */
    public function refreshStatusMap() {
        $this->loggingService->debug('Building plugin status map...');
        // update the instance var as we'll need a status map for getPluginsForContext()
        $this->statusMap = $this->pluginStatusMapBuilder->buildStatusMap();
        // keep values
        foreach ($this->getPluginsForContext(null, false) as $pluginId => $plugin) {
            // values
            if ($options = $plugin->getOptions()) {
                $values = array();
                foreach (array_keys($options) as $key) {
                    $values[$key] = $plugin->get($key);
                }
                $this->statusMap[$pluginId]['values'] = $values;
            }
        }
        if ($this->cache) {
            // store values
            $this->cache->save($this->statusMap, self::STATUS_MAP_KEY);
        }
    }

    /**
     * Get plugin status map.
     *
     * @param boolean refresh Optional flag to force a refresh; default is <code>false</code>.
     * @return array Plugin status map.
     */
    protected function getStatusMap($refresh=false) {
        if (null === $this->statusMap || $refresh) {
            if (null != $this->cache) {
                $this->statusMap = $this->cache->lookup(self::STATUS_MAP_KEY);
            }

            if (!$this->statusMap || $refresh) {
                $this->refreshStatusMap();
            }
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
                    // plugins can only contribute translations
                    $path = $plugin->getPluginDirectory().'/locale';
                    $this->localeService->addResource($path);
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
