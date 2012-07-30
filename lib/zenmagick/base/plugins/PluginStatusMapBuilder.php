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

use DirectoryIterator;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\classloader\ClassLoader;

use Symfony\Component\Yaml\Yaml;

/**
 * Builder for a cacheable plugin status map.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class PluginStatusMapBuilder extends ZMObject {
    const  PLUGIN_CLASS_PATTERN = 'Plugin.php';
    private $defaultPluginClass;
    private $pluginDirs;

    /**
     * Set the default plugin class.
     *
     * @param string class The class name.
     */
    public function setDefaultPluginClass($class) {
        $this->defaultPluginClass = $class;
    }

    public function setPluginDirs(array $dirs) {
        $this->pluginDirs = $dirs;
    }
    /**
     * Generate a full map of plugins and their base path.
     *
     * @return array Map of plugin ids with the plugin base path as key.
     */
    protected function getPathIdMap() {
        $pathIdMap = array();
        foreach ($this->pluginDirs as $basePath) {
            if (file_exists($basePath) && is_dir($basePath)){
                $pathIdMap[$basePath] = array();
                foreach (new DirectoryIterator($basePath) as $filename => $fileInfo) {
                    if ($fileInfo->isFile() && false !== strpos($fileInfo->getFilename(), self::PLUGIN_CLASS_PATTERN)) {
                        $id = str_replace(self::PLUGIN_CLASS_PATTERN, '', $fileInfo->getFilename());
                        $id[0] = strtolower($id[0]);
                        $pathIdMap[$basePath][] = array('id' => $id, 'pluginDir' => $basePath, 'standalone' => true);
                    } else if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                        $id = $fileInfo->getFilename();
                        $pathIdMap[$basePath][] = array('id' => $fileInfo->getFilename(), 'pluginDir' => $fileInfo->getPathname(), 'standalone' => false);
                    }
                }
            }
        }

        return $pathIdMap;
    }

    /**
     * Build status map.
     *
     * @return array Plugin status map.
     */
    public function buildStatusMap() {
        // temp. classloader
        $classLoader = new ClassLoader();
        $classLoader->register();

        $statusMap = array();

        foreach ($this->getPathIdMap() as $basePath => $pathInfo) {
            foreach ($pathInfo as $info) {
                $id = $info['id'];
                $pluginDir = $info['pluginDir'];

                $pluginClasses = array();
                $pluginClassBase = ClassLoader::className($id);
                $namespace = Plugins::PLUGIN_BASE_NAMESPACE;
                if ($info['standalone']) {
                    $pluginClasses[] = sprintf('zenmagick\plugins\%sPlugin', $pluginClassBase);
                } else {
                    $namespace = sprintf('zenmagick\plugins\%s', $id);
                    $pluginClasses[] = sprintf('%s\%sPlugin', $namespace, $pluginClassBase);
                }
                $pluginClasses[] = $this->defaultPluginClass;

                foreach ($pluginClasses as $pluginClass) {
                    if (class_exists($pluginClass)) {
                        break;
                    }
                    $pluginClass = null;
                }

                if ($pluginClass && ($plugin = Beans::getBean($pluginClass))) {
                    $plugin->setId($id);

                    $config = null;
                    $pluginConfig = $pluginDir.'/plugin.yaml';
                    if (file_exists($pluginConfig)) {
                        $config = Yaml::parse($pluginConfig);
                    }

                    $statusMap[$id] = array_merge($info, array(
                        'class' => $pluginClass,
                        'installed' => $plugin->isInstalled(),
                        'enabled' => $plugin->isEnabled(),
                        'context' => $plugin->getContext(),
                        'order' => $plugin->getSortOrder(),
                        'namespace' => $namespace,
                        'lib' => !$info['standalone'] && file_exists($pluginDir.'/lib'),
                        'config' => $config
                    ));
                }
            }
        }

        $classLoader->unregister();

        return $statusMap;
    }

}
