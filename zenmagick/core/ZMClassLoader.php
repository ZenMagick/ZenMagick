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
 * Class loader that will also resolve inherited 'ZM' classes
 * <p>If a class does <strong>not</strong> start with 'ZM', the corresponding
 * file with 'ZM' prefix will be loaded as well, assuming inheritance.
 * This is not really scalable and does not handle more than on level of inheritance (yet).</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMClassLoader {
    var $classPath_;


    // create new instance
    function ZMClassLoader() {
    global $zm_runtime;
        $this->classPath_ = array();
        $this->classPath_ = array_merge($this->classPath_, $this->_findClasses($zm_runtime->getThemePath()."/".ZM_THEME_EXTRA));
        $this->classPath_ = array_merge($this->classPath_, $this->_findClasses($zm_runtime->getControllerPath()));
        $this->classPath_ = array_merge($this->classPath_, $this->_findClasses($zm_runtime->getZMRootPath()."core/"));
        $this->classPath_ = array_merge($this->classPath_, $this->_findClasses($zm_runtime->getThemePath(), false));
/*
echo "<pre>";
print_r($this->classPath_);
echo "</pre>";
*/
    }

    // create new instance
    function __construct() {
        $this->ZMClassLoader();
    }

    function __destruct() {
    }


    // get the current class path
    function getClassPath() {
        return array_merge($this->classPath_);
    }


    // locate, load and instantiate a given class;
    function newInstance($name) {
        $classfile = null;
        $zmname = "ZM".$name;
        $zmclassfile = null;
        if (array_key_exists($name, $this->classPath_)) {
            $classfile = $this->classPath_[$name];
        }

        if (null == $classfile || !zm_starts_with($name, "ZM")) {
            if (array_key_exists($zmname, $this->classPath_)) {
                $zmclassfile = $this->classPath_[$zmname];
            }
        }

        if (null != $zmclassfile) {
            require_once($zmclassfile);
        }
        if (null != $classfile) {
            require_once($classfile);
        }

        zm_log("newInstance: name: " . $name .  ", zmname: " . $zmname, 4);

        if (null == $classfile && null == $zmclassfile)
            return null;

        return zm_get_instance(null != $classfile ? $name : $zmname);
    }


    // return class => filename map for given directory
    function _findClasses($path, $recursive=true) {
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
