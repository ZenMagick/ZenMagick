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


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
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
                $this->container->get('loggingService')->debug('Loading plugin status map...');
                $statusMapBuilder = $this->container->get('pluginStatusMapBuilder');
                $this->statusMap = $statusMapBuilder->buildStatusMap();
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
        $app = $this->container->get('application');

        $localeService = $this->container->get('localeService');
        $settingsService = $this->container->get('settingsService');

        $plugins = array();
        foreach ($this->getStatusMap() as $id => $status) {
            if (array_key_exists($id, $this->plugins)) {
                $plugins[$id] = $this->plugins[$id];
                continue;
            }

            if (($status['enabled'] || !$enabled) && (null === $context || Runtime::isContextMatch($status['context'], $context))) {
                $this->classLoader->addNamespace($status['namespace'], sprintf('%s@%s', $status['pluginDir'], $status['namespace']));
                if ('ZM' == substr($status['class'], 0, 2)) {
                    // todo: remove
                    $this->classLoader->addDefault($status['class'], sprintf('%s/%s.php', $status['pluginDir'], $status['class']));
                }

                if ($plugin = Beans::getBean($status['class'])) {
                    $plugin->setId($id);
                    $plugin->setPluginDirectory($status['pluginDir']);

                    if ($status['enabled'] && $status['installed'] && Runtime::isContextMatch($status['context'], $context)) {
                        // no matter what, if disabled or not installed we'll never init
                        if ($status['lib']) {
                            $libDir = $status['pluginDir'].'/lib';
                            $this->classLoader->addNamespace($status['namespace'], $libDir);
                            // allow custom class loading config
                            $this->classLoader->addConfig($libDir);
                        }

                        if ($status['config']) {
                            $configLoader = $this->container->get('contextConfigLoader');
                            $configLoader->setConfig($status['config']);
                            $configLoader->process();
                        }

                        $plugin->init();

                        // plugins can only contribute translations
                        $path = $plugin->getPluginDirectory().'/locale/'.$settingsService->get('zenmagick.base.locales.locale');
                        $localeService->getLocale()->addResource($path);
                    }

                    if ($status['config'] && array_key_exists('meta', $status['config'])) {
                        Beans::setAll($plugin, $status['config']['meta']);
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
        if (array_key_exists($id, $this->plugins)) {
            return $this->plugins[$id];
        }

        return null;
    }

}
