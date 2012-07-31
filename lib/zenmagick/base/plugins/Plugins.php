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
use zenmagick\base\classloader\ClassLoader;

use zenmagick\apps\store\utils\ContextConfigLoader;

/**
 * Basic plugin service.
 *
 * <p>Plugins may consist of either:</p>
 * <ul>
 *  <li>a single file</li>
 *  <li>a directory containing multiple files</li>
 * <ul>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Plugins extends ZMObject {
    const PLUGIN_BASE_NAMESPACE = 'zenmagick\plugins';
    const STATUS_MAP_KEY = 'zenmagick.plugins.status_map';
    protected $plugins;
    protected $cache;
    protected $statusMap;
    protected $classLoader;
    protected $loggingService;
    protected $pluginStatusMapBuilder;
    protected $localeService;
    protected $settingsService;
    protected $contextConfigLoader;


    /**
     * Create new instance.
     */
    public function __construct($loggingService, $pluginStatusMapBuilder, $localeService, $settingsService, $contextConfigLoader) {
        parent::__construct();
        $this->loggingService = $loggingService;
        $this->pluginStatusMapBuilder = $pluginStatusMapBuilder;
        $this->localeService = $localeService;
        $this->settingsService = $settingsService;
        $this->contextConfigLoader = $contextConfigLoader;
        $this->plugins = array();
        $this->cache = null;
        $this->statusMap = null;
        $this->classLoader = new ClassLoader();
        $this->classLoader->register();
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
        $this->getStatusMap(true);
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
                $this->loggingService->debug('Loading plugin status map...');
                $this->statusMap = $this->pluginStatusMapBuilder->buildStatusMap();
                if ($this->cache) {
                    $this->cache->save($this->statusMap, self::STATUS_MAP_KEY);
                }
            }
        }
        return $this->statusMap;
    }

    /**
     * Get all plugins for the given context.
     *
     * @param int context Optional context flag; default is <code>null</code> for all.
     * @param boolean enabled If <code>true</code>, return only enabled plugins; default is <code>true</code>.
     * @return array List of initialized plugins.
     */
    public function getPluginsForContext($context=null, $enabled=true) {
        $context = $context ?: $this->contextConfigLoader->getContext();

        $plugins = array();
        foreach ($this->getStatusMap() as $id => $status) {
            if (array_key_exists($id, $this->plugins)) {
                $plugins[$id] = $this->plugins[$id];
                continue;
            }

            if (($status['enabled'] || !$enabled) && (null === $context || Runtime::isContextMatch($status['context'], $context))) {
                if ($plugin = Beans::getBean($status['class'])) {
                    $plugin->setId($id);
                    $plugin->setPluginDirectory($status['pluginDir']);

                    if ($status['enabled'] && $status['installed'] && Runtime::isContextMatch($status['context'], $context)) {
                        // no matter what, if disabled or not installed we'll never init
                        if ($status['lib']) {
                            $libDir = $status['pluginDir'].'/lib';
                            // allow custom class loading config
                            $this->classLoader->addConfig($libDir);
                        }

                        if ($status['config']) {
                            $this->contextConfigLoader->setConfig($status['config']);
                            $this->contextConfigLoader->process();
                        }

                        $plugin->init();

                        // plugins can only contribute translations
                        $path = $plugin->getPluginDirectory().'/locale/'.$this->settingsService->get('zenmagick.base.locales.locale');
                        $this->localeService->getLocale()->addResource($path);
                    }

                    if ($status['config'] && array_key_exists('meta', $status['config'])) {
                        $meta = $status['config']['meta'];
                        Beans::setAll($plugin, $meta);
                    }

                    $this->plugins[$id] = $plugins[$id] = $plugin;
                }
            }
        }

        return $plugins;
    }

    /**
     * Get the plugin for the given id.
     *
     * @param string id The plugin id.
     * @return Plugin A plugin instance or <code>null</code>.
     */
    public function getPluginForId($id) {
        $plugins = $this->getPluginsForContext();
        if (array_key_exists($id, $plugins)) {
            return $plugins[$id];
        }

        return null;
    }

}
