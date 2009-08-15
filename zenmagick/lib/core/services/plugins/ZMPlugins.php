<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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


/**
 * Basic plugin service.
 *
 * <p>Plugins are grouped via the filesystem. The plugin base directory is expected to
 * contain a subfolder for each group with the folder name being used as group name.</p>
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
 * @version $Id: ZMPlugins.php 2356 2009-06-29 09:52:41Z dermanomann $
 */
class ZMPlugins extends ZMObject {
    // list of loaded plugins
    protected $plugins_;
    // plugin status details
    protected $pluginStatus_;
    // plugin base dir
    protected $pluginBaseDir_;


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
        $this->pluginBaseDir_ = ZMRuntime::getPluginsDirectory();
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
        return ZMObject::singleton('Plugins');
    }


    /**
     * Load the plugin status data.
     *
     * <p>The default implementation is to look at settings in the form <em>zenmagick.core.plugins.[id].enabled</em>.</p>
     *
     * @return array The status of all plugins.
     */
    protected function loadStatus() {
        return array();
    }

    /**
     * Get a list of available plugin groups.
     *
     * @return array A list of groups and their associated directories.
     */
    public function getGroups() {
        $types = array();
        $handle = opendir($this->pluginBaseDir_);
        while (false !== ($name = readdir($handle))) { 
            if (ZMLangUtils::startsWith($name, '.')) {
                continue;
            }

            $file = $this->pluginBaseDir_.$name;
            if (is_dir($file)) {
                $types[$name] = $file;
            }
        }
        @closedir($handle);

        asort($types);
        return $types;
    }

    /**
     * Get all plugins.
     *
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return array A map containing separate lists of <code>ZMPlugin</code> instances for each group.
     */
    public function getAllPlugins($enabled=true) {
        $plugins = array();
        foreach ($this->getGroups() as $group => $dir) {
            $plugins[$group] = $this->getPluginsForGroup($group, $enabled);
        }

        return $plugins;
    }

    /**
     * Generate a full list of plugin ids for the given group.
     *
     * @param string group The plugin group.
     * @return array List of plugin ids.
     */
    protected function getPluginIdsForGroup($group) {
        $dir = $this->pluginBaseDir_ . $group . DIRECTORY_SEPARATOR;
        $idList = array();
        if (false !== ($handle = @opendir($dir))) {
            while (false !== ($file = readdir($handle))) { 
                if (ZMLangUtils::startsWith($file, '.')) {
                    continue;
                }

                $idList[] = str_replace('.php', '', $file);
            }
            @closedir($handle);
        }

        return $idList;
    }

    /**
     * Get all plugins for the given group.
     *
     * @param string group The plugin group.
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return array A list of <code>ZMPlugin</code> instances.
     */
    public function getPluginsForGroup($group, $enabled=true) {
        $idList = array();
        // populate list of plugin ids to load
        if ($enabled) {
            // use plugin status to select plugins
            foreach ($this->pluginStatus_ as $id => $status) {
                if ($status['group'] == $group && $status['enabled']) {
                    $idList[] = $id;
                }
            }
        } else {
            // do it the long way...
            $idList = $this->getPluginIdsForGroup($group);
            // make sure we have valid pluginStatus data
            foreach ($idList as $id) {
                if (!array_key_exists($id, $this->pluginStatus_)) {
                    $this->pluginStatus_[$id] = array(
                      'group' => $group,
                      'enabled' => false
                    );
                }
            }
        }

        $plugins = array();
        foreach ($idList as $id) {
            $plugin = $this->getPluginForId($id, $group);
            if (null != $plugin) {
                $plugins[$id] = $plugin;
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
     * Get the plugin for the given id.
     *
     * @param string id The plugin id.
     * @param string group Optional group; default is <code>null</code> to auto detect.
     * @return ZMPlugin A plugin instance or <code>null</code>.
     */
    public function getPluginForId($id, $group=null) {
        if (array_key_exists($id, $this->plugins_)) {
            return $this->plugins_[$id];
        }

        $status = $this->pluginStatus_[$id];
        $group = null != $group ? $group : $status['group'];
        $groupDir = $this->pluginBaseDir_ . $group . DIRECTORY_SEPARATOR;
        $file = $groupDir.$id;
        if (is_dir($file)) {
            // expect plugin file in the directory with the same name and '.php' extension
            $file .= DIRECTORY_SEPARATOR . $id . '.php';
            if (!file_exists($file)) {
                ZMLogging::instance()->log("can't find plugin file for '".$id."'", ZMLogging::DEBUG);
                return null;
            }
        } else if (is_file($file.'.php')) {
            $file .= '.php';
        } else {
            ZMLogging::instance()->log("can't find plugin file for '".$id."'", ZMLogging::DEBUG);
            return null;
        }

        // load
        if (!file_exists($file)) {
            ZMLogging::instance()->log("plugin file does not exist for '".$id."'", ZMLogging::DEBUG);
            return null;
        }

        if (!class_exists($id)) {
            // load plugin class
            require_once($file);
        }

        $plugin = new $id();
        $plugin->setGroup($group);
        $pluginDir = dirname($file) . DIRECTORY_SEPARATOR;
        $plugin->setPluginDirectory($pluginDir == $groupDir ? $groupDir : $pluginDir);

        $this->plugins_[$id] = $plugin;
        return $plugin;
    }

    /**
     * Init all plugins for the given group(s).
     *
     * @param mixed groups Either a single group or a group list.
     * @param boolean enabled If <code>true</code>, return only enabled plugins: default is <code>true</code>.
     * @return array List of initialized plugins.
     */
    public function initPluginsForGroups($groups, $enabled=true) {
        if (!is_array($groups)) {
            $groups = array($groups);
        }

        $ids = array();
        foreach ($groups as $group) {
            foreach ($this->getPluginsForGroup($group, $enabled) as $plugin) {
                $ids[] = $plugin->getId();
            }
        }

        return $this->initPluginsForId($ids, $enabled);
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
            return $plugins[0];
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
        $pluginLoader = ZMLoader::make('Loader', 'plugins');

        $plugins = array();
        foreach ($ids as $id) {
            // get list
            $plugin = $this->getPluginForId($id);
            if ($plugin->isEnabled() || !$enabled) {
                if (ZMPlugin::LP_ALL == $plugin->getLoaderPolicy()) {
                    $pluginLoader->addPath($plugin->getPluginDirectory());
                } else if (ZMPlugin::LP_FOLDER == $plugin->getLoaderPolicy()) {
                    $pluginLoader->addPath($plugin->getPluginDirectory(), false);
                }
                foreach ($plugin->getGlobal() as $file) {
                    $pluginLoader->addGlobal($plugin->getPluginDirectory().$file);
                }

                $plugins[] = $plugin;
            }
        }

        $pluginLoader->loadStatic();

        // plugins prevail over defaults, *and* themes
        ZMLoader::instance()->setParent($pluginLoader);

        // do the actual init only after all plugins have been loaded to allow
        // them to depend on each other
        foreach ($plugins as $plugin) {
            // call init only after everything set up
            $plugin->init();
        }
        ZMEvents::instance()->fireEvent($this, ZMEvents::INIT_PLUGIN_GROUP_DONE, array('ids' => $ids, 'plugins' => $plugins));

        return $plugins;
    }

}

?>
