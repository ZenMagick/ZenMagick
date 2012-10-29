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
namespace ZenMagick\Base\Plugins;

use DirectoryIterator;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Yaml\Yaml;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Cache\Cache;



/**
 * Builder for a cacheable plugin status map.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class PluginStatusMapBuilder extends ZMObject implements CacheWarmerInterface {
    const STATUS_MAP_KEY = 'zenmagick.plugins.status_map';
    const  PLUGIN_CLASS_PATTERN = 'Plugin.php';
    private $defaultPluginClass;
    private $pluginDirs;
    private $pluginOptionsLoader;


    /**
     * Create new instance.
     *
     * @param string defaultPluginClass The default plugin class name.
     * @param array pluginDirs List of plugin base directories.
     * @param PluginOptionsLoader pluginOptionsLoader The plugin options loader.
     * @param Cache cache Cache to be used.
     */
    public function __construct($defaultPluginClass, array $pluginDirs, PluginOptionsLoader $pluginOptionsLoader, Cache $cache) {
        parent::__construct();
        $this->defaultPluginClass = $defaultPluginClass;
        $this->pluginDirs = $pluginDirs;
        $this->pluginOptionsLoader = $pluginOptionsLoader;
        $this->cache = $cache;
    }


    /**
     * {@inheritDoc}
     */
    public function isOptional() {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function warmUp($cacheDir) {
        $this->getStatusMap(true);
    }


    /**
     * Get status map.
     *
     * @param boolean force Optional flag to force a rebuild; default is <code>false</code.
     * @return array Plugin status map.
     */
    public function getStatusMap($force = false) {
        $statusMap = null;
        if ($force || !$statusMap = $this->fromCache()) {
            $statusMap = $this->buildStatusMap();
            $this->toCache($statusMap);
        } else {
            if (!$statusMap = $this->fromCache()) {
                $statusMap = $this->buildStatusMap();
                $this->toCache($statusMap);
            }
        }

        return $statusMap;
    }

    /**
     * Get the status map from cache.
     *
     * @return array A status map or <code>null</code.
     */
    protected function fromCache() {
        return $this->cache ? $this->cache->lookup(self::STATUS_MAP_KEY) : null;
    }

    /**
     * Save the status map in cache.
     *
     * @param array statusMap A status map.
     */
    protected function toCache(array $statusMap) {
        if ($this->cache) {
            $this->cache->save($statusMap, self::STATUS_MAP_KEY);
        }
    }

    /**
     * Build status map.
     *
     * @return array Plugin status map.
     */
    protected function buildStatusMap() {
        // this could be merged, but it seems simpler to avoid more nesting...
        $pathIdMap = array();
        foreach ($this->pluginDirs as $basePath) {
            if (file_exists($basePath) && is_dir($basePath)) {
                $pathIdMap[$basePath] = array();
                foreach (new DirectoryIterator($basePath) as $filename => $fileInfo) {
                    if ($fileInfo->isDir() && !$fileInfo->isDot() && file_exists($fileInfo->getPathname().'/plugin.yaml')) {
                        $id = $fileInfo->getFilename();
                        $pathIdMap[$basePath][] = array('id' => $fileInfo->getFilename(), 'pluginDir' => $fileInfo->getPathname());
                    }
                }
            }
        }

        $statusMap = array();
        foreach ($pathIdMap as $basePath => $pathInfo) {
            foreach ($pathInfo as $info) {
                $id = $info['id'];
                $pluginDir = $info['pluginDir'];

                $pluginClass = Toolbox::className($id);

                $pluginClasses = array();
                $pluginClasses[] = sprintf(Plugins::PLUGIN_BASE_NAMESPACE.'\%s\%sPlugin', $id, $pluginClass);
                $pluginClasses[] = $this->defaultPluginClass;
                foreach ($pluginClasses as $pluginClass) {
                    if (class_exists($pluginClass)) {
                        break;
                    }
                    $pluginClass = null;
                }
                if ($pluginClass && class_exists($pluginClass)) {
                    $config = array();
                    $pluginConfig = $pluginDir.'/plugin.yaml';
                    $config = Yaml::parse($pluginConfig);

                    // add some stuff
                    $config['meta']['id'] = $id;
                    $config['meta']['pluginDir'] = $pluginDir;
                    $config['meta']['class'] = $pluginClass;
                    // final adjustments
                    $statusMap[$id] = $this->pluginOptionsLoader->load($id, $config);
                }
            }
        }

        return $statusMap;
    }

}
