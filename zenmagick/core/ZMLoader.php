<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * ZenMagick code/class loader.
 *
 * <p>Loader might be chained to allow delegation of theme loading. All loader
 * implement a <em>parent first</em> strategy.</p>
 *
 * <p>Classes in ZenMagick have to adhere to the following conventions:</p>
 * <ul>
 *  <li>ZenMagick classes start always with the prefix <em>ZM</em></li>
 *  <li>Filenames have to reflect the containd class; this is <strong>case sensitive</strong>
 *    (Noteable exception is ZMObject, which is located in bootstrpa.php!)
 *  </li>
 *  <li>There is always one class per file</li>
 *  <li>Custom classes use the name of the parent class without the <em>ZM</em> prefix;
 *    For example a custom index controller would extend <code>ZMIndexController</code> and
 *    be named <code>IndexController</code>
 *  </li>
 *  <li>It is considered good practice to group related code in directories similar to the
 *    ZenMagick code
 *  </li>
 *  <li>Classes are created using the class loader <code>create(..)</code> method</li>
 *  <li>Parent classes following the above conventions will be automatically resolved</li>
 * </ul>
 *
 * <p><strong>Note:</strong> This is not scalable as Java code and does not handle more than on
 * level of inheritance.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMLoader {
    var $name_;
    var $parent_;
    var $path_;


    /**
     * Create a new loader.
     *
     * @param string name The loader name.
     */
    function ZMLoader($name) {
        $this->name_ = $name;
        $this->parent_ = null;
        $this->path_ = array();
    }

    /** PHP5 constructor. */
    function __construct($name) {
        $this->ZMLoader($name);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
    }


    /**
     * Get the class path.
     *
     * <p>This will return the full class path incl. all parent loader.</p>
     *
     * @return array Class path array.
     */
    function getClassPath() {
        $classPath = array_merge($this->path_);
        if (null != $this->parent_) {
            $classPath = array_merge($this->parent_->getClassPath(), $classPath);
        }

        return $classPath;
    }

    /**
     * Add a given path to the loaders path.
     *
     * @param string path The path to add.
     * @param bool recursive Flag to indicate if the path should be scanned recursively.
     */
    function addPath($path, $recursive=true) {
        $this->path_ = array_merge($this->path_, $this->_scan($path, $recursive));
    }

    /**
     * Set the parent loader.
     *
     * @param ZMLoader parent The new parent.
     */
    function setParent(&$parent) {
        $this->parent_ =& $parent;
    }

    /**
     * Return the loaders path.
     *
     * <p>The path is a map of (potential) class names and corresponding filenames.</p>
     *
     * @return array The loader path.
     */
    function getPath() {
        return $this->path_;
    }

    /**
     * Returns a map of all the static code in the path. Code is identified by a filename starting with
     * a lower case character.
     */
    function getStatic() {
        $static = array();
        foreach ($this->path_ as $name => $file) {
            if ($name != ucfirst($name)) {
                $static[$name] = $file;
            }
        }

        return $static;
    }

    /**
     * Get the class file for the given class name.
     *
     * @param string name The class name without the <em>ZM</em> prefix.
     * @return string The class filename that or <code>null</code>.
     */
    function getClassFile($name) {
        $filename = null;
        if (null != $this->parent_) {
            $filename = $this->parent_->getClassFile($name);
        }

        return null != $filename ? $filename : (array_key_exists($name, $this->path_) ? $this->path_[$name] : null);
    }

    /**
     * Resolve and load the class code for the given class name.
     *
     * @param string name The class name without the <em>ZM</em> prefix.
     * @return string The final class name either the given or the ZenMagick default
     *  implementation or <code>null</code>.
     */
    function load($name) {
        $classfile = $this->getClassFile($name);
        $zmname = "ZM".$name;
        $zmclassfile = $this->getClassFile($zmname);

        zm_log($this->name_.": loading: class: " . $name .  ", ZM class: " . $zmname, 4);

        if (null != $zmclassfile && !class_exists($zmname)) { require($zmclassfile); }
        if (null != $classfile && !class_exists($name)) { require($classfile); }

        return null != $classfile ? $name : (null != $zmclassfile ? $zmname : null);
    }

    /**
     * Resolve, load and instantiate an instance of the class for the given class name.
     *
     * @param string name The class name without the <em>ZM</em> prefix.
     * @param var arg Optional constructor arguments.
     */
    function create($name) {
        $args = func_get_args();
        array_shift($args);
        return $this->_create($name, $args);
    }

    /**
     * Resolve, load and instantiate an instance of the class for the given class name.
     *
     * @param string name The class name.
     * @param array args Optional list of constructor arguments.
     * @return mixed A class instance or <code>null</code>.
     */
    function createWithArgs($name, $args) {
        return $this->_create($name, $args);
    }

    /**
     * Resolve, load and instantiate an instance of the class for the given class name.
     *
     * @param string name The class name.
     * @param array args Optional list of constructor arguments.
     * @return mixed A class instance or <code>null</code>.
     */
    function _create($name, $args) {
        $clazz = $this->load($name);
        return null != $clazz ? zm_get_instance($clazz, null, $args) : null;
    }

    /**
     * Build a path map for the given path.
     *
     * @param string path The path to scan.
     * @param bool recursive Flag to indicate if the path should be scanned recursively.
     * @return array A path map for the given path.
     */
    function _scan($path, $recursive=true) {
        $files = zm_find_includes($path, $recursive);
        $classMap = array();
        foreach ($files as $file) {
            $classname = str_replace('.php', '', basename($file));
            $classMap[$classname] = $file;
        }

        return $classMap;
    }

}

?>
