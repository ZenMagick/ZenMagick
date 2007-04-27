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

    /**
     * Default c'tor.
     */
    function ZMPlugins() {
        parent::__construct();
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
    global $zm_runtime;

        $types = array();
        $handle = opendir($zm_runtime->getPluginsDir());
        while (false !== ($file = readdir($handle))) { 
            if ("." == $file || ".." == $file)
                continue;

            $name = $zm_runtime->getPluginsDir().$file;
//echo $name."<br>";
            if (is_dir($name)) {
                $types[$file] = $name;
            }
        }
        @closedir($handle);

        return $types;
    }

    /**
     * Get *all* plugins.
     *
     * @return array A list of <code>ZMPlugin</code> instances grouped by type.
     */
    function getAllPlugins() {
        $plugins = array();
        foreach ($this->getPluginTypes() as $type => $typeDir) {
            $plugins[$type] = $this->getPluginsForType($type);
        }
        return $plugins;
    }

    /**
     * Get all plugins for the given type.
     *
     * @param string type The plugin type.
     * @return array A list of <code>ZMPlugin</code> instances.
     */
    function getPluginsForType($type) {
    global $zm_runtime;

        $typeDir = $zm_runtime->getPluginsDir() . $type . '/';
//echo $typeDir."<br>";
        $files = array();
        $handle = opendir($typeDir);
        while (false !== ($file = readdir($handle))) { 
            if ("." == $file || ".." == $file)
                continue;

            $name = $typeDir.$file;
//echo $name."<br>";
            if (is_dir($name)) {
                // expect plugin file in the directory with the same name and '.php' extension
                $name = $name . '/' . $file . '.php';
                array_push($files, $name);
            } else if (zm_ends_with($name, ".php")) {
                array_push($files, $name);
            }
        }
        @closedir($handle);

        // make sure we can extend from ZMPlugin
        $this->create("ZMPlugin");
        $plugins = array();
        foreach ($files as $file) {
            $plugin = str_replace('.php', '', basename($file));
//echo $plugin."<br>";
            if (!file_exists($file)) {
                continue;
            }
            require_once($file);
            $obj = new $plugin();
            $obj->setType($type);
            //if (null == $type || $obj->getType() == $type) {
                $plugins[] = $obj;
            //}
        }
//echo $type . ': '.count($plugins);

        return $plugins;
    }

    /**
     * Get plugin for the given id.
     *
     * @param string id The plugin id.
     * @param string type Optional type to make the lookup easier; default is <code>null</code>.
     * @return ZMPlugin A plugin instance or <code>null</code>.
     */
    function getPluginForIdAndType($id, $type=null) {
        if (null != $type) {
            foreach ($this->getPluginsForType($type) as $plugin) {
                if ($id == $plugin->getId()) {
                    return $plugin;
                }
            }
        } else {
            foreach ($this->getAllPlugins() as $types) {
                foreach ($types as $plugin) {
                    if ($id == $plugin->getId()) {
                        return $plugin;
                    }
                }
            }
        }

        return null;
    }

}

?>
