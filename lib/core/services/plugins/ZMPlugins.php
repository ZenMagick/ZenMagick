<?php
/*
 * ZenMagick - Another PHP framework.
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

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Basic plugin service.
 *
 * <p>Plugins may consist of either:</p>
 * <dl>
 *  <dt>a single file</dt>
 *  <dd>In this case the filename is expected to reflect the classname and the class, in
 *  turn, to extend from <code>ZMPlugin</code>.</dd>
 *  <dt>a directory containing multiple files</dt>
 *  <dd>In this case the convention require a <code>.php</code> with the same name as the
 *  directory in the directory, containing the main plugin class. Again, the classname is
 *  expected to be the same as the filename (without the <code>.php</code> extension).
 *  It is the plugins responsibility to set use the appropricate <em>loader policy</em in
 *  order to expose all required code/classes to the loader..</dd>
 * <dl>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.plugins
 */
class ZMPlugins extends ZMObject {
    // internal plugin cache with some details
    protected $plugins_;
    // plugin status details
    protected $pluginStatus_;
    // plugin/basePath map
    protected $pathIdMap_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->plugins_ = array();
        $this->pluginStatus_ = $this->loadStatus();
        if (!is_array($this->pluginStatus_)) {
            $this->pluginStatus_ = array();
        }
        $this->pathIdMap_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMRuntime::singleton('Plugins');
    }


    /**
     * Load the plugin status data.
     *
     * <p>The default implementation is to look at settings in the form <em>zenmagick.core.plugins.[id].enabled</em>, so this
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
            foreach (ZMRuntime::getPluginBasePath() as $basePath) {
                $this->pathIdMap_[$basePath] = array();
                if (false !== ($handle = @opendir($basePath))) {
                    while (false !== ($file = readdir($handle))) {
                        if (ZMLangUtils::startsWith($file, '.')) {
                            continue;
                        }

                        $id = str_replace('.php', '', $file);
                        // single file plugin
                        $id = str_replace(array('Plugin', ZMLoader::DEFAULT_CLASS_PREFIX), '', $id);
                        $id{0} = strtolower($id{0});
                        $this->pathIdMap_[$basePath][] = $id;
                    }
                    @closedir($handle);
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
     * @return array A list of <code>ZMPlugin</code> instances.
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
            // sort
            usort($plugins, array($this, "comparePlugins"));
        }

        return $plugins;
    }

    /**
     * Compare plugins.
     *
     * @param ZMPlugin a First plugin.
     * @param ZMPlugin b Second plugin.
     * @return integer Value less than, equal to, or greater than zero if the first argument is
     *  considered to be respectively less than, equal to, or greater than the second.
     */
    protected function comparePlugins($a, $b) {
        $an = $a->getName();
        $bn = $b->getName();
        if ($an == $bn) {
            return 0;
        }
        return ($an < $bn) ? -1 : 1;
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
     * @return ZMPlugin A plugin instance or <code>null</code>.
     */
    public function getPluginForId($id) {
        if (array_key_exists($id, $this->plugins_)) {
            return $this->plugins_[$id]['plugin'];
        }

        $pluginClassSuffix = ZMLoader::makeClassname($id);
        $basePath = $this->getBasePathForId($id);
        $pluginDir = $basePath.$id;
        if (is_dir($pluginDir)) {
            // expect plugin file in the directory as 'ZM[CamelCaseId]Plugin.php.php' extension
            $pluginClass = ZMLoader::DEFAULT_CLASS_PREFIX . $pluginClassSuffix . 'Plugin';
            $file = $pluginDir . DIRECTORY_SEPARATOR . $pluginClass . '.php';
            if (!file_exists($file)) {
                ZMLogging::instance()->log("can't find plugin file(dir) for id = '".$id."'; dir = '".$pluginDir."'", ZMLogging::DEBUG);
                return null;
            }
        } else {
            // single file, so either the id is just the id or the filename; let's try both...
            $pluginClass = $pluginClassSuffix;
            $file = $basePath . $pluginClass . '.php';
            if (!is_file($file)) {
                $pluginClass = ZMLoader::DEFAULT_CLASS_PREFIX . $pluginClassSuffix . 'Plugin';
                $file = $basePath . $pluginClass . '.php';
                if (!is_file($file)) {
                    ZMLogging::instance()->log("can't find plugin file for id = '".$id."'; dir = '".$pluginDir."'", ZMLogging::DEBUG);
                    return null;
                }
            }
        }

        // load if required
        if (!class_exists($pluginClass)) {
            // load plugin class
            require_once($file);
        }

        $plugin = ZMLoader::make($pluginClass);
        $id = substr(preg_replace('/Plugin$/', '', $pluginClass), 2);
        $id[0] = strtolower($id[0]);
        $plugin->setId($id);
        //PHP5.3 only: $plugin->setId(lcfirst(substr(preg_replace('/Plugin$/', '', $pluginClass), 2)));
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
     * @return ZMPlugin A plugin or <code>null</code>.
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
        $pluginLoader = new ZMLoader('plugins');

        $plugins = array();
        foreach ($ids as $id) {
            // get list
            $plugin = $this->getPluginForId($id);
            if (null != $plugin && ($plugin && $plugin->isEnabled() || !$enabled)) {
                if (ZMPlugin::LP_LIB == $plugin->getLoaderPolicy()) {
                    $pluginLoader->addPath($plugin->getPluginDirectory().'lib'.DIRECTORY_SEPARATOR);
                } else if (ZMPlugin::LP_ALL == $plugin->getLoaderPolicy()) {
                    $pluginLoader->addPath($plugin->getPluginDirectory());
                } else if (ZMPlugin::LP_FOLDER == $plugin->getLoaderPolicy()) {
                    $pluginLoader->addPath($plugin->getPluginDirectory(), '', false);
                }
                $plugins[$id] = $plugin;
            }
        }

        // plugins prevail over defaults, *and* themes
        ZMLoader::instance()->setParent($pluginLoader);

        // do *after* the loader is active to allow to use plugin classes in static contents!
        $pluginLoader->loadStatic();

        // do the actual init only after all plugins have been loaded to allow
        // them to depend on each other
        foreach ($plugins as $id => $plugin) {
            if ($this->needsInit($id)) {
                // call init only after everything set up
                $plugin->init();
                $this->plugins_[$id] = array('plugin' => $plugin, 'init' => true);
            }
        }

        Runtime::getEventDispatcher()->notify(new Event($this, 'init_plugin_group_done', array('ids' => $ids, 'plugins' => $plugins)));

        return $plugins;
    }

}
