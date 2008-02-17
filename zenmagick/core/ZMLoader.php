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
 * @package org.zenmagick
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
     * @param boolean recursive Flag to indicate if the path should be scanned recursively.
     */
    function addPath($path, $recursive=true) {
        $this->path_ = array_merge($this->path_, $this->_scan($path, $recursive));
    }

    /**
     * Add a given file to the loaders path.
     *
     * @param string file The file to add.
     */
    function addFile($file) {
        $this->path_[$classname] = $file;
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
     * Returns a list of all the static code in this loaders path. Code is identified by a filename starting with
     * a lower case character.
     *
     * <p>Note: This is an instance specific method. There is no delegation to a parent loader.</p>
     *
     * @return array Static files with local.php being the first (if it exists).
     */
    function getStatic() {
        $static = array();
        // get full list
        foreach ($this->path_ as $name => $file) {
            if ($name == $file) {
                $static[$name] = $file;
            }
        }

        if (array_key_exists('local', $static)) {
            // get local to top 
            $tmp = array();
            array_push($tmp, $static['local']);
            unset($static['local']);
            foreach ($static as $name => $file) {
                array_push($tmp, $file);
            }
           $static = $tmp; 
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

        // additional stuff for single core file, as there is no classpath!
        if (defined('ZM_SINGLE_CORE') && null == $classfile && null == $zmclassfile) {
            if (class_exists($name)) {
                if (zm_starts_with($name, 'ZM')) {
                    return $name;
                } else {
                    // make sure we load a ZenMagick class; otherwise there is 
                    // overlap with zen-cart class names
                    $parent = $name;
                    while (false !== ($parent = get_parent_class($parent))) {
                        if ('ZMObject' == $parent) {
                            return $name;
                        }
                    }
                }
            } 
            // this is not the else case, as we need it as fallback if $name
            // does not get resolved
            if (class_exists($zmname)) {
                return $zmname;
            }
        }

        //zm_log($this->name_.": loading: class: " . $name .  ", ZM class: " . $zmname, ZM_LOG_DEBUG);

        if (null != $zmclassfile && !class_exists($zmname)) { require_once($zmclassfile); }
        if (null != $classfile && !class_exists($name)) { require_once($classfile); }

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
    function &_create($name, $args) {
        $clazz = $this->load($name);
        if (null != $clazz) {
            $obj = null;
            switch (count($args)) {
            case 0:
                $obj = new $clazz();
                break;
            case 1:
                $obj = new $clazz($args[0]);
                break;
            case 2:
                $obj = new $clazz($args[0], $args[1]);
                break;
            case 3:
                $obj = new $clazz($args[0], $args[1], $args[2]);
                break;
            case 4:
                $obj = new $clazz($args[0], $args[1], $args[2], $args[3]);
                break;
            default:
                zm_log("unsupported number of arguments " . $clazz);
                zm_backtrace();
                break;
            }
            return $obj;

        }
        return null;
    }

    /**
     * Build a file map for the given path.
     *
     * @param string path The path to scan.
     * @param boolean recursive Flag to indicate if the path should be scanned recursively.
     * @return array A file map for the given path.
     */
    function _scan($path, $recursive=true) {
        $files = zm_find_includes($path, $recursive);
        $map = array();
        foreach ($files as $file) {
            $name = str_replace('.php', '', basename($file));
            if ($name == strtolower($name)) {
                // static, so make it unique
                $name = $file;
            }
            $map[$name] = $file;
        }

        return $map;
    }

}

?>
