<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Plugins.
 *
 * <p>Plugins are similar to zen-cart modules. Basically, <code>ZMPlugin</code> can be used as
 * base class for a zen-cart module (zen-cart will use the properties and methods marked
 * <em>deprecated</em>...).</p>
 *
 * <p>The plugin type is controlled by the base directory within the plugins directory.
 * Please note that even though it is valid to create payment, shipping and order_total
 * directories/plugins, zen-cart will not (yet) recognize them as such.</p>
 *
 * <p>For now, plugins are a simple way to add configuration options to zen-cart without
 * the need to write custom installer/uninstaller.</p.
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
 *  It is the plugins responsibility to load all other files it depons upon.</dd>
 * <dl>
 *
 * <p>Plugins are grouped according to the way they are used/required. Valid groups (subdirectories)
 * are:</p>
 * <dl>
 *  <dt>request</dt><dd>created for each request</dd>
 * </dl>
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMPlugins extends ZMService {
    var $pluginStatus_;
    var $plugins_;
    var $pluginsDir_;


    /**
     * Default c'tor.
     */
    function ZMPlugins() {
    global $zm_runtime;

        parent::__construct();

        $this->pluginStatus_ = unserialize(ZENMAGICK_PLUGIN_STATUS);
        if (!is_array($this->pluginStatus_)) {
            $this->pluginStatus_ = array();
        }
        $this->plugins_ = array();
        $this->pluginsDir_ = $zm_runtime->getPluginsDir();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMPlugins();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get a list of available plugin types.
     *
     * @return array A list of types and their associated directories.
     */
    function getPluginTypes() {
        $types = array();
        $handle = opendir($this->pluginsDir_);
        while (false !== ($file = readdir($handle))) { 
            if (zm_starts_with($file, '.')) {
                continue;
            }

            $name = $this->pluginsDir_.$file;
            if (is_dir($name)) {
                $types[$file] = $name;
            }
        }
        @closedir($handle);

        return $types;
    }

    /**
     * Get all plugins.
     *
     * @param bool useCache If <code>true</code>, use the cached plugin status info.
     * @return array A list of <code>ZMPlugin</code> instances grouped by type.
     */
    function getAllPlugins($useCache=true) {
        $plugins = array();
        foreach ($this->getPluginTypes() as $type => $typeDir) {
            $plugins[$type] = $this->getPluginsForType($type, $useCache);
        }
        return $plugins;
    }

    /**
     * Load list of plugins for the given type.
     *
     * @param string type The plugin type.
     * @return array List of plugin ids.
     */
    function _getPluginIdsForType($type) {
        $typeDir = $this->pluginsDir_ . $type . '/';
        $idList = array();
        $handle = @opendir($typeDir);
        if (false !== $handle) {
            while (false !== ($file = readdir($handle))) { 
                if (zm_starts_with($file, '.')) {
                    continue;
                }

                $idList[] = str_replace('.php', '', $file);
            }
            @closedir($handle);
        }

        return $idList;
    }

    /**
     * Get all plugins for the given type.
     *
     * @param string type The plugin type.
     * @param bool useCache If <code>true</code>, use the cached plugin status info.
     * @return array A list of <code>ZMPlugin</code> instances.
     */
    function &getPluginsForType($type, $useCache=true) {
        $idList = array();
        if ($useCache) {
            // use plugin status to select plugins
            foreach ($this->pluginStatus_ as $id => $status) {
                if ($status['type'] == $type && $status['enabled']) {
                    $idList[] = $id;
                }
            }
        } else {
            // do it the long way...
            $idList = $this->_getPluginIdsForType($type);
        }

        $plugins = array();
        foreach ($idList as $id) {
            $plugin =& $this->getPluginForId($id, $type);
            if (null != $plugin) {
                $plugins[$id] =& $plugin;
            }
        }

        return $plugins;
    }

    /**
     * Get plugin for the given id.
     *
     * @param string id The plugin id.
     * @param string type Optional type.
     * @return ZMPlugin A plugin instance or <code>null</code>.
     */
    function &getPluginForId($id, $type=null) {
        if (array_key_exists($id, $this->plugins_)) {
            return $this->plugins_[$id];
        }

        $status = $this->pluginStatus_[$id];
        $type = null != $type ? $type : $status['type'];
        $typeDir = $this->pluginsDir_ . $type . '/';
        $file = $typeDir.$id;
        if (is_dir($file)) {
            // expect plugin file in the directory with the same name and '.php' extension
            $file .= '/' . $id . '.php';
            if (!file_exists($file)) {
                return null;
            }
        } else if (is_file($file.'.php')) {
            $file .= '.php';
        } else {
            return null;
        }

        // load
        if (!file_exists($file)) {
            return null;
        }

        if (!class_exists($id)) {
            require_once($file);
        }

        $plugin =& new $id();
        $plugin->setType($type);
        $pluginDir = dirname($file) . '/';
        if ($pluginDir != $typeDir) {
            $plugin->setPluginDir($pluginDir);
        }

        $this->plugins_[$id] =& $plugin;
        return $plugin;
    }

    /**
     * Call <code>filterContents(string)</code> on all available plugin handler.
     *
     * @param string contents The page contents.
     * @return string The really final contents :0
     */
    function filterResponse($contents) {
    global $zm_request;

        $controller = $zm_request->getController();
        foreach ($controller->getGlobals() as $name => $instance) {
            global $$name;
            $$name = $instance;
        }

        foreach ($this->getPluginsForType('request') as $id => $plugin) {
            // PHP4 hack; use $$id rather than $plugin
            if ($$id->isEnabled()) {
                $pluginHandler = $$id->getPluginHandler();
                if (null !== $pluginHandler && is_subclass_of($pluginHandler, 'ZMPluginHandler')) {
                    $pluginHandler->setPlugin($$id);
                    $contents = $pluginHandler->filterResponse($contents);
                }
            }
        }
        return $contents;
    }

}

?>
