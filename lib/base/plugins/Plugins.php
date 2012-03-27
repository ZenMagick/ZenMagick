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
use zenmagick\base\classloader\ClassLoader;

use zenmagick\apps\store\utils\ContextConfigLoader;

/**
 * Basic plugin service.
 *
 * <p>Plugins may consist of either:</p>
 * <dl>
 *  <dt>a single file</dt>
 *  <dd>In this case the filename is expected to reflect the classname and the class, in
 *  turn, to extend from <code>Plugin</code>.</dd>
 *  <dt>a directory containing multiple files</dt>
 *  <dd>In this case the convention require a <code>.php</code> with the same name as the
 *  directory in the directory, containing the main plugin class. Again, the classname is
 *  expected to be the same as the filename (without the <code>.php</code> extension).
 *  It is the plugins responsibility to set use the appropricate <em>loader policy</em in
 *  order to expose all required code/classes to the loader..</dd>
 * <dl>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class Plugins extends ZMObject {
    const PLUGIN_BASE_NAMESPACE = 'zenmagick\plugins';
    // TODO: this should be zenmagick\base\plugins\Plugin - or a configured value
    const DEFAULT_PLUGIN_CLASS = 'Plugin';
    // internal plugin cache with some details
    protected $plugins_;
    // plugin status details
    protected $pluginStatus_;
    // plugin/basePath map
    protected $pathIdMap_;
    protected $classLoader;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->plugins_ = array();
        $this->pluginStatus_ = $this->loadStatus();
        if (!is_array($this->pluginStatus_)) {
            $this->pluginStatus_ = array();
        }
        $this->pathIdMap_ = null;
        $this->classLoader = new ClassLoader();
        foreach (Runtime::getPluginBasePath() as $basePath) {
            $this->classLoader->addNamespace(self::PLUGIN_BASE_NAMESPACE, sprintf('%s@%s', $basePath, self::PLUGIN_BASE_NAMESPACE));
        }
        $this->classLoader->register();
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
                if (file_exists($basePath) && is_dir($basePath) && false !== ($handle = @opendir($basePath))) {
                    while (false !== ($file = readdir($handle))) {
                        if ('.' == $file[0]) {
                            continue;
                        }

                        $id = str_replace('.php', '', $file);
                        // todo: drop
                        $id = preg_replace('/^ZM/', '', $id);
                        // single file plugin
                        if (is_file($basePath.'/'.$file)) {
                            $id = preg_replace('/Plugin$/', '', $id);
                        }
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
     * @param string context Optional context; default is <code>null</code> for all.
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return array A list of <code>Plugin</code> instances.
     */
    public function getAllPlugins($context=null, $enabled=true) {
        $pathIdMap = array();
        // populate list of plugin ids to load
        if ($enabled) {
            // use plugin status to select plugins
            foreach ($this->pluginStatus_ as $id => $status) {
                if ($status['enabled'] && (null === $context || Runtime::isContextMatch($status['context'], $context))) {
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
                          'context' => null,
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
     * Get plugin class.
     */
    protected function getPluginClass($id) {
        $classBase = ClassLoader::className($id);
        $basePath = $this->getBasePathForId($id);
        $pluginDir = $basePath.'/'.$id;

        // 1) namespaced
        $class = sprintf('zenmagick\plugins\%s\%sPlugin', $id, $classBase);
        if (class_exists($class)) {
            return $class;
        }

        // 2) traditional
        $class = 'ZM'.$classBase.'Plugin';
        if (is_dir($pluginDir)) {
            $file = $pluginDir.'/'.$class.'.php';
            if (!file_exists($file)) {
                $class = 'Plugin';
            }
        } else {
            $file = $basePath.'/'.$class.'.php';
            if (!is_file($file)) {
                $class = 'Plugin';
            }
        }
        return $class;
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

        $pluginClassBase = ClassLoader::className($id);
        $basePath = $this->getBasePathForId($id);
        $pluginDir = $basePath.'/'.$id;
        $pluginClasses = array();
        if (is_dir($pluginDir)) {
            $namespace = sprintf('zenmagick\plugins\%s', $id);
            $pluginClasses[] = sprintf('%s\%sPlugin', $namespace, $pluginClassBase);
            $this->classLoader->addNamespace($namespace, sprintf('%s@%s', $pluginDir, $namespace));
        } else {
            $pluginClasses[] = sprintf('zenmagick\plugins\%sPlugin', $pluginClassBase);
            $pluginDir = $basePath;
        }
        // todo: drop ZM stuff
        $defaultClass = $pluginClasses[] = sprintf('ZM%sPlugin', $pluginClassBase);
        // TODO: remove once we are all namespaced
        $this->classLoader->addDefault($defaultClass, sprintf('%s/%s.php', $pluginDir, $defaultClass));
        // TODO: make the default plugin class configurable?
        $pluginClasses[] = self::DEFAULT_PLUGIN_CLASS;

        foreach ($pluginClasses as $pluginClass) {
            if (class_exists($pluginClass)) {
                break;
            }
            $pluginClass = null;
        }

        $plugin = Beans::getBean($pluginClass);
        $plugin->setId($id);
        $plugin->setPluginDirectory($pluginDir);

        $this->plugins_[$id] = array('plugin' => $plugin, 'init' => false);
        return $plugin;
    }

    /**
     * Init all plugins.
     *
     * @param int context Optional context flag; default is <code>null</code> for all.
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return array List of initialized plugins.
     */
    public function initAllPlugins($context=null, $enabled=true) {
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
        $classLoader = $this->container->get('classLoader');

        $plugins = array();
        foreach ($ids as $id) {
            // get list
            $plugin = $this->getPluginForId($id);
            if (null != $plugin && ($plugin && $plugin->isEnabled() || !$enabled)) {
                $libPath = $plugin->getPluginDirectory().'/lib';
                $classLoader->addNamespace('zenmagick\\plugins\\'.$id, $libPath);
                // allow custom class loading config
                $classLoader->addConfig($libPath);
                $plugins[$id] = $plugin;
            }
        }

        // plugins prevail over defaults, *and* themes
        $classLoader->register();

        $localeService = $this->container->get('localeService');

        // do the actual init only after all plugins have been loaded to allow
        // them to depend on each other
        foreach ($plugins as $id => $plugin) {
            if ($this->needsInit($id)) {
                $pluginConfig = $plugin->getPluginDirectory().'/plugin.yaml';
                if (file_exists($pluginConfig)) {
                    $configLoader = $this->container->get('contextConfigLoader');
                    $configLoader->setConfig(Toolbox::loadWithEnv($pluginConfig));
                    $configLoader->process();
                    // if the plugin is a generic class, try to set some meta data
                    if (get_class($plugin) == self::DEFAULT_PLUGIN_CLASS) {
                        $config = $configLoader->getConfig();
                        if (array_key_exists('meta', $config)) {
                            Beans::setAll($plugin, $config['meta']);
                        }
                    }
                }

                // call init only after everything set up
                $plugin->init();
                $this->plugins_[$id] = array('plugin' => $plugin, 'init' => true);
            }

            // plugins can only contribute translations
            $path = $plugin->getPluginDirectory().'/locale/'.Runtime::getSettings()->get('zenmagick.base.locales.locale');
            $localeService->getLocale()->addResource($path);
        }

        return $plugins;
    }

}
