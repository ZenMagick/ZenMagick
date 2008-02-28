<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMPlugins extends ZMService {
    private static $pluginStatus_ = array();
    private static $plugins_ = array();


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        ZMPlugins::$pluginStatus_ = unserialize(ZENMAGICK_PLUGIN_STATUS);
        if (!is_array(ZMPlugins::$pluginStatus_)) {
            ZMPlugins::$pluginStatus_ = array();
        }
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
        return parent::instance('Plugins');
    }


    /**
     * Get a list of available plugin types.
     *
     * @return array A list of types and their associated directories.
     */
    public function getPluginTypes() {
        $types = array();
        $handle = opendir(ZMRuntime::getPluginsDir());
        while (false !== ($file = readdir($handle))) { 
            if (zm_starts_with($file, '.')) {
                continue;
            }

            $name = ZMRuntime::getPluginsDir().$file;
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
     * @param string scope The plugin scope; default is <code>ZM_SCOPE_ALL</code>.
     * @param boolean configured If <code>true</code>, return only configured provider: default is <code>true</code>.
     * @return array A list of <code>ZMPlugin</code> instances grouped by type.
     */
    function getAllPlugins($scope=ZM_SCOPE_ALL, $configured=true) {
        $plugins = array();
        foreach (ZMPlugins::getPluginTypes() as $type => $typeDir) {
            $plugins[$type] = ZMPlugins::getPluginsForType($type, $scope, $configured);
        }
        return $plugins;
    }

    /**
     * Load list of plugins for the given type.
     *
     * @param string type The plugin type.
     * @return array List of plugin ids.
     */
    protected static function _getPluginIdsForType($type) {
        $typeDir = ZMRuntime::$getPluginsDir() . $type . '/';
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
     * @param string scope The plugin scope; default is <code>ZM_SCOPE_ALL</code>.
     * @param boolean configured If <code>true</code>, return only configured provider: default is <code>true</code>.
     * @return array A list of <code>ZMPlugin</code> instances.
     */
    public function getPluginsForType($type, $scope=ZM_SCOPE_ALL, $configured=true) {
        $idList = array();
        if ($configured) {
            // use plugin status to select plugins
            foreach (ZMPlugins::$pluginStatus_ as $id => $status) {
                if ($status['type'] == $type && $status['enabled']) {
                    if (ZM_SCOPE_ALL == $status['scope'] || $status['scope'] == $scope) {
                        $idList[] = $id;
                    }
                }
            }
        } else {
            // do it the long way...
            $idList = ZMPlugins::_getPluginIdsForType($type);
        }

        $plugins = array();
        foreach ($idList as $id) {
            $plugin = ZMPlugins::getPluginForId($id, $type);
            if (null != $plugin) {
                $plugins[$id] = $plugin;
            }
        }

        if (!$configured) {
            // sort
            usort($plugins, array(ZMPlugins, "_cmp_plugins"));
        }

        return $plugins;
    }

    /**
     * Compare plugins relative to their sort order and name.
     *
     * @param ZMPlugin a First plugin.
     * @param ZMPlugin b Second plugin.
     * @return integer Value less than, equal to, or greater than zero if the first argument is
     *  considered to be respectively less than, equal to, or greater than the second.
     */
    static function _cmp_plugins($a, $b) {
        $ao = $a->getSortOrder();
        $bo = $b->getSortOrder();
        if ($ao == $bo) {
            return 0;
        }
        return ($ao < $bo) ? -1 : 1;
    }

    /**
     * Get plugin for the given id.
     *
     * @param string id The plugin id.
     * @param string type Optional type.
     * @return ZMPlugin A plugin instance or <code>null</code>.
     */
    public function getPluginForId($id, $type=null) {
        if (array_key_exists($id, ZMPlugins::$plugins_)) {
            return ZMPlugins::$plugins_[$id];
        }

        $status = ZMPlugins::$pluginStatus_[$id];
        $type = null != $type ? $type : $status['type'];
        $typeDir = ZMRuntime::getPluginsDir() . $type . '/';
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

        $plugin = new $id();
        $plugin->setType($type);
        $pluginDir = dirname($file) . '/';
        if ($pluginDir != $typeDir) {
            $plugin->setPluginDir($pluginDir);
        }

        ZMPlugins::$plugins_[$id] = $plugin;
        return $plugin;
    }

    /**
     * Call <code>filterContents(string)</code> on all available plugin handler.
     *
     * @param string contents The page contents.
     * @return string The really final contents :0
     */
    public function filterResponse($contents) {
        $controller = ZMRequest::instance()->getController();
        foreach ($controller->getGlobals() as $name => $instance) {
            global $$name;
            $$name = $instance;
        }

        foreach (ZMPlugins::getPluginsForType('request', ZM_SCOPE_STORE) as $plugin) {
            if ($plugin->isEnabled()) {
                $pluginHandler = $plugin->getPluginHandler();
                //TODO: PHP5: interface ZMPluginHandler?
                if (null !== $pluginHandler && is_subclass_of($pluginHandler, 'ZMPluginHandler')) {
                    $pluginHandler->setPlugin($plugin);
                    $contents = $pluginHandler->filterResponse($contents);
                }
            }
        }

        return $contents;
    }

    /**
     * Init all plugins of the given type and scope.
     *
     * @package org.zenmagick
     * @param string type The type.
     * @param string scope The current scope.
     */
    public function initPlugins($type, $scope) {
        // prepare environment
        eval(zm_globals());

        // each type has it's own loader
        $pluginLoader = ZMLoader::make("Loader");

        // get list
        $pluginList = ZMPlugins::getPluginsForType($type, $scope);

        // instantiate, add to loader (if required) and make global
        foreach ($pluginList as $plugin) {
            if ($plugin->isEnabled()) {
                if ('ALL' == $plugin->getLoaderSupport()) {
                    $pluginLoader->addPath($plugin->getPluginDir());
                } else if ('FOLDER' == $plugin->getLoaderSupport()) {
                    $pluginLoader->addPath($plugin->getPluginDir(), false);
                }
                $pluginId = $plugin->getId();
                // make plugin a global using the class name
                global $$pluginId;
                $$pluginId = $plugin;
            }
        }

        // use plugin loader to load static stuff
        if (ZM_SCOPE_ADMIN == $scope || !defined('ZM_SINGLE_CORE')) {
            $pluginLoader->loadStatic();
        }

        // plugins prevail over defaults, *and* themes
        ZMLoader::instance()->setParent($pluginLoader);

        // call init only after everything set up
        foreach ($pluginList as $plugin) {
            if ($plugin->isEnabled()) {
                $plugin->init();
            }
        }
    }


}

?>
