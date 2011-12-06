<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\base\plugins;

use zenmagick\base\Runtime;
use zenmagick\base\ClassLoader;
use zenmagick\base\events\Event;

/**
 * Basic plugin service.
 *
 * <p>Plugins may consist of either:</p>
 * <dl>
 *  <dt>a single file</dt>
 *  <dd>In this case the filename is expected to reflect the classname and the class, in
 *  turn, to extend from <code>\zenmagick\base\pluginsPlugin</code>.</dd>
 *  <dt>a directory containing multiple files</dt>
 *  <dd>In this case the convention require a <code>.php</code> with the same name as the
 *  directory, containing the main plugin class. Again, the classname is
 *  expected to be the same as the filename (without the <code>.php</code> extension).
 * <dl>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.plugins
 */
class Plugins {
    // internal plugin cache with some details
    protected $plugins_;
    // plugin status details
    protected $pluginStatus_;
    // plugin/basePath map
    protected $pathIdMap_;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->plugins_ = array();
        $this->pluginStatus_ = $this->loadStatus();
        if (!is_array($this->pluginStatus_)) {
            $this->pluginStatus_ = array();
        }
        $this->pathIdMap_ = null;
    }


    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('pluginService');
    }


    /**
     * Load the plugin status data.
     *
     * <p>The default implementation is to look at settings in the form <em>zenmagick.base.plugins.[id].enabled</em>, so this
     * implementation returns just an empty array.</p>
     *
     * @return array The status of all plugins.
     */
    protected function loadStatus() {
        return array();
    }

    /**
     * Generate a full map of plugins and their base path.
     *
     * @return array Map of plugin ids with the plugin base path as key.
     */
    protected function getPluginBasePathMap() {
        if (null === $this->pathIdMap_) {
            $this->pathIdMap_ = array();
            foreach (Runtime::getPluginBasePath() as $basePath) {
                $this->pathIdMap_[$basePath] = array();
                foreach (new \DirectoryIterator($basePath) as $fileInfo) {
                    if ($fileInfo->isDot()) {
                        continue;
                    }
                    $id = str_replace(array('Plugin', '.php'), '', $fileInfo->getFilename());
                    $id{0} = strtolower($id{0});
                    $this->pathIdMap_[$basePath][] = $id;
                }
            }
        }

        return $this->pathIdMap_;
    }

    /**
     * Get all plugins for a given context.
     *
     * @param int context Optional context flag; default is <em>0</em> for all.
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return array A list of <code>Plugin</code> instances.
     */
    public function getAllPlugins($context=0, $enabled=true) {
        $pathIdMap = array();
        // populate list of plugin ids to load
        if ($enabled) {
            // use plugin status to select plugins
            foreach ($this->pluginStatus_ as $id => $status) {
                if ($status['enabled'] && (0 == $context || ($context&$status['context']))) {
                    $basePath = array_key_exists('basePath', $status) ? $status['basePath'] : $this->getBasePathForId($id);
                    if (!array_key_exists($basePath, $pathIdMap)) {
                        $pathIdMap[$basePath] = array();
                    }
                    $pathIdMap[$basePath][] = $id;
                }
            }
        } else {
            // do it the long way...
            $pathIdMap = $this->getPluginBasePathMap();
            // make sure we have valid pluginStatus data
            foreach ($pathIdMap as $basePath => $idList) {
                foreach ($idList as $id) {
                    if (!array_key_exists($id, $this->pluginStatus_)) {
                        $this->pluginStatus_[$id] = array(
                          'context' => 0,
                          'basePath' => $basePath,
                          'enabled' => false
                        );
                    }
                }
            }
        }

        $plugins = array();
        foreach ($pathIdMap as $basePath => $idList) {
            foreach ($idList as $id) {
                $plugin = $this->getPluginForId($id);
                if (null != $plugin) {
                    $plugins[$id] = $plugin;
                }
            }
        }

        if (!$enabled) {
            ksort($plugins);
        }

        return $plugins;
    }

    /**
     * Get the base path for the given plugin id.
     *
     * @param string id The plugin id.
     * @return string The base path for this plugin or <code>null</code> if not found.
     */
    protected function getBasePathForId($id) {
        foreach ($this->getPluginBasePathMap() as $basePath => $idList) {
            if (in_array($id, $idList)) {
                return $basePath;
            }
        }
        return null;
    }

    /**
     * Get the plugin for the given id.
     *
     * @param string id The plugin id.
     * @return Plugin A plugin instance or <code>null</code>.
     */
    public function getPluginForId($id) {
        if (array_key_exists($id, $this->plugins_)) {
            return $this->plugins_[$id]['plugin'];
        }

        $pluginClassSuffix = ClassLoader::className($id);
        $basePath = $this->getBasePathForId($id);
        $pluginDir = $basePath.$id;
        if (is_dir($pluginDir)) {
            // expect plugin file in the directory as '[CamelCaseId]Plugin.php' extension
            $pluginClass = $pluginClassSuffix . 'Plugin';
            $file = $pluginDir . DIRECTORY_SEPARATOR . $pluginClass . '.php';
            if (!file_exists($file)) {
                Runtime::getLogging($this)->debug("can't find plugin file(dir) for id = '".$id."'; dir = '".$pluginDir."'");
                return null;
            }
        } else {
            // single file, so either the id is just the id or the filename; let's try both...
            $pluginClass = $pluginClassSuffix;
            $file = $basePath . $pluginClass . '.php';
            if (!is_file($file)) {
                $pluginClass = $pluginClassSuffix . 'Plugin';
                $file = $basePath . $pluginClass . '.php';
                if (!is_file($file)) {
                    Runtime::getLogging($this)->debug("can't find plugin file for id = '".$id."'; dir = '".$pluginDir."'");
                    return null;
                }
            }
        }

        // load if required
        if (!class_exists($pluginClass)) {
            // load plugin class
            require_once($file);
        }

        $plugin = new $pluginClass();
        $plugin->setId(lcfirst(substr(preg_replace('/Plugin$/', '', $pluginClass), 2)));
        $pluginDir = dirname($file) . DIRECTORY_SEPARATOR;
        $plugin->setPluginDirectory($pluginDir == $basePath ? $basePath : $pluginDir);

        $this->plugins_[$id] = array('plugin' => $plugin, 'init' => false);
        return $plugin;
    }

    /**
     * Init all plugins.
     *
     * @param int context Optional context flag; default is <em>0</em> for all.
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return array List of initialized plugins.
     */
    public function initAllPlugins($context=0, $enabled=true) {
        $ids = array();
        foreach ($this->getAllPlugins($context, $enabled) as $plugin) {
            $ids[] = $plugin->getId();
        }

        return $this->initPluginsForId($ids, $enabled);
    }

    /**
     * Check if a plugin needs be initialized.
     *
     * @param string id The plugin id.
     * @return boolean <code>true</code> if the plugin needs to be initialized.
     */
    protected function needsInit($id) {
        return !array_key_exists($id, $this->plugins_) || false == $this->plugins_[$id]['init'];
    }

    /**
     * Convenience method to init a single plugin.
     *
     * @param string id Either a single id or an id list.
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return Plugin A plugin or <code>null</code>.
     */
    public function initPluginForId($id, $enabled=true) {
        $plugins = $this->initPluginsForId($id, $enabled);
        if (1 == count($plugins)) {
            return array_pop($plugins);
        }
        return null;
    }

    /**
     * Init all plugins of the given type and scope.
     *
     * <p><strong>NOTE:</strong> This method does not check for enabled or similar.
     * It is the responsibility of the calling code to make sure that all ids are
     * actually wanted!</p>
     *
     * @param mixed ids Either a single id or an id list.
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return array List of initialized plugins.
     */
    public function initPluginsForId($ids, $enabled=true) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        // plugins get their own loader
        $pluginClassLoader = new ClassLoader();

        $plugins = array();
        foreach ($ids as $id) {
            // get list
            $plugin = $this->getPluginForId($id);
            if (null != $plugin && ($plugin && $plugin->isEnabled() || !$enabled)) {
                $pluginClassLoader->addConfig($plugin->getPluginDirectory());
                $plugins[$id] = $plugin;
            }
        }

        // do the actual init only after all plugins have been loaded to allow
        // them to depend on each other
        foreach ($plugins as $id => $plugin) {
            if ($this->needsInit($id)) {
                // call init only after everything set up
                $plugin->init();
                $this->plugins_[$id] = array('plugin' => $plugin, 'init' => true);
            }
        }
        Runtime::getEventDispatcher()->dispatch('init_plugin_group_done', new Event($this, array('ids' => $ids, 'plugins' => $plugins)));

        return $plugins;
    }

}
