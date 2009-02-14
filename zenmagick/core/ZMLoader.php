<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 *  <li>Filenames have to reflect the contained class; this is <strong>case sensitive</strong>
 *  </li>
 *  <li>There is always one class per file</li>
 *  <li>Custom classes use the name of the parent class without the <em>ZM</em> prefix;
 *    For example a custom index controller would extend <code>ZMIndexController</code> and
 *    be named <code>IndexController</code>
 *  </li>
 *  <li>Classes are created using the class loader's <code>makeClass(..)</code> method</li>
 *  <li>Parent classes following the above conventions will be automatically resolved</li>
 * </ul>
 *
 * <p><strong>Note:</strong> This is not as scalable as Java code and does not handle more than on
 * level of inheritance.</p>
 *
 * <p><strong>Static methods operate all on the root loader.</strong></p>
 *
 * @author DerManoMann
 * @package org.zenmagick
 * @version $Id$
 */
class ZMLoader {
    private static $root_ = null;
    private $parent_;
    private $path_;
    private static $classPrefix = 'ZM';
    private $global_;


    /**
     * Create a new loader.
     */
    public function __construct() {
        $this->parent_ = null;
        $this->path_ = array();
        $this->global_ = array();
    }


    /**
     * Get the root loader.
     *
     * @return ZMLoader The root loader.
     */
    public static function instance() {
        if (null == ZMLoader::$root_) {
            ZMLoader::$root_ = new ZMLoader();
        }
        return ZMLoader::$root_;
    }


    /**
     * Get the class path.
     *
     * @param boolean includeParent If <code>true</code> include the parent loader path: default is <code>true</code>.
     * @return array Class path array.
     */
    public function getClassPath($includeParent=true) {
        $classPath = array_merge($this->path_);
        if ($includeParent && null != $this->parent_) {
            $classPath = array_merge($this->parent_->getClassPath(), $classPath);
        }

        return $classPath;
    }

    /**
     * Get the class file for the given class name.
     *
     * @param string name The class name without the <em>ZM</em> prefix.
     * @return string The class filename that or <code>null</code>.
     */
    protected function getClassFile($name) {
        $filename = null;
        if (null != $this->parent_) {
            $filename = $this->parent_->getClassFile($name);
        }

        return null != $filename ? $filename : (isset($this->path_[$name]) ? $this->path_[$name] : null);
    }

    /**
     * Add a given path to the loaders path.
     *
     * @param string path The path to add.
     * @param boolean recursive Flag to indicate if the path should be scanned recursively.
     */
    public function addPath($path, $recursive=true) {
        $this->path_ = array_merge($this->path_, $this->scan($path, $recursive));
    }

    /**
     * Manually register a class.
     *
     * @param string clazz The class name.
     * @param string filename The filename.
     */
    public function registerClass($clazz, $filename) {
        $this->path_[$clazz] = $filename;
    }

    /**
     * Set the parent loader.
     */
    public function setParent($parent) {
        $last = $this;
        while (null != $last->parent_) {
            $last = $last->parent_;
        }
        $last->parent_ = $parent;
    }

    /**
     * Add a file to be loaded in global context.
     *
     * @param string filename The file to load.
     */
    public function addGlobal($filename) {
        $this->global_[] = $filename;
    }

    /**
     * Get files to be loaded in global context.
     *
     * @param array List of filenames.
     */
    public function getGlobal() {
        $list = $this->global_;
        if (null != $this->parent_) {
            $list = array_merge($list, $this->parent_->getGlobal());
        }
        return $list;
    }

    /**
     * Load all available static code.
     *
     * <p><strong>Note:</strong> Using this is intended to load functions, defines, etc. As this
     * is loaded inside a method, variables inside static files will not be real globals.</p>
     */
    public function loadStatic() {
        foreach ($this->getStatic() as $static) {
            require_once $static;
        }
    }

    /**
     * Returns a list of all the static code in this loaders path. Code is identified by a filename starting with
     * a lower case character.
     *
     * <p>Note: This is an instance specific method. There is no delegation to a parent loader.</p>
     *
     * @return array Static files with local.php being the first (if it exists).
     */
    public function getStatic() {
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
            $tmp[] = $static['local'];
            unset($static['local']);
            foreach ($static as $name => $file) {
                $tmp[] = $file;
            }
            $static = $tmp; 
        }

        return $static;
    }

    /**
     * Shortcut version of <code>ZMLoader::instance()->resolveClass($name)</code>.
     *
     * @param string name The class name (without the <em>ZM</em> prefix).
     * @return string The resolved class name; this is either the given name, the ZenMagick default
     *  implementation or <code>null</code>.
     */
    public static function resolve($name) {
        return ZMLoader::instance()->resolveClass($name);
    }

    /**
     * Resolve and load the class code for the given class name.
     *
     * @param string name The class name (without the <em>ZM</em> prefix).
     * @return string The resolved class name; this is either the given name, the ZenMagick default
     *  implementation or <code>null</code>.
     */
    public function resolveClass($name) {
        // plain name
        $classfile = $this->getClassFile($name);
        // prefixed class
        $zmname = ZMLoader::$classPrefix.$name;

        if (!class_exists($name) && !interface_exists($name) && null != $classfile) {
            if (null != ($zmclassfile = $this->getClassFile($zmname))) { 
                // ensure the prefixed class is loaded for inheritence
                require_once $zmclassfile;
            }
            require_once $classfile;
        }

        if (class_exists($name) || interface_exists($name)) {
            if (0 === strpos($name, ZMLoader::$classPrefix) || null != $classfile) {
                // resolved && starts with prefix or resolved via classpath (which suggests a ZenMagick class) - as good as it gets
                return $name;
            } else {
                // make sure we load a ZenMagick class
                // there might be name clashes with non ZenMagick classes
                $parent = $name;
                while (false !== ($parent = get_parent_class($parent))) {
                    if (0 === strpos($parent, ZMLoader::$classPrefix)) {
                        return $name;
                    }
                }
            }
        } 

        // fallback to prefixed class
        if (null != ($zmclassfile = $this->getClassFile($zmname)) && !class_exists($zmname)) { 
            require_once $zmclassfile;
        }

        if (class_exists($zmname) || interface_exists($zmname)) {
            return $zmname;
        }

        return null;
    }

    /**
     * Shortcut for creating new class instances.
     *
     * <p>Please note that is it also possible to pass just a single parameter (array) that contains the class name
     * (first element) and optionally constructor arguments (second, third,..).</p>
     *
     * @param string name The class name (without the <em>ZM</em> prefix).
     * @param var arg Optional constructor arguments.
     * @return mixed A new instance of the given class.
     */
    public static function make($args) {
        if (!is_array($args)) {
            $args = func_get_args();
        }
        return ZMLoader::instance()->makeClass($args);
    }

    /**
     * Resolve, load and instantiate a new instance of the given class.
     *
     * @param string name The class name (without the <em>ZM</em> prefix).
     * @param var arg Optional constructor arguments.
     * @return mixed A new instance of the given class.
     */
    protected function makeClass($name) {
        if (is_array($name)) {
            $tmp = $name;
            $name = array_shift($tmp);
            $args = $tmp;
        } else {
            $args = func_get_args();
            array_shift($args);
        }
        $clazz = ZMLoader::resolve($name);
        if (null != $clazz) {
            if (!class_exists($clazz)) {
                throw ZMLoader::make('ZMException', 'class not found ' . $clazz);
            }
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
            case 5:
                $obj = new $clazz($args[0], $args[1], $args[2], $args[3], $args[4]);
                break;
            default:
                throw ZMLoader::make('ZMException', 'unsupported number of constructor arguments ' . $clazz);
            }
            return $obj;

        }
        return null;
    }


    /**
     * Scan (recursively) for <code>.php</code> files.
     *
     * <p>It is worth mentioning that directories will always be processed only after
     * all plain files in a directory are done.</p>
     *
     * @param string dir The name of the root directory to scan.
     * @param string ext Optional file suffix/extension; default is <em>.php</em>.
     * @param boolean recursive If <code>true</code>, scan recursively.
     * @return array List of full filenames of <code>.php</code> files.
     */
    public static function findIncludes($dir, $ext='.php', $recursive=false) {
        $includes = array();
        if (!is_dir($dir) || false !== strpos($dir, '.svn')) {
            return $includes;
        }

        // save directories for later
        $dirs = array();

        $handle = @opendir($dir);
        while (false !== ($file = readdir($handle))) { 
            if ("." == $file || ".." == $file) {
                continue;
            }
            $file = $dir.$file;
            if (is_dir($file)) {
                $dirs[] = $file;
            } else if ($ext == substr($file, -strlen($ext))) {
                $includes[] = $file;
            }
        }
        @closedir($handle);

        // process folders last
        if ($recursive) {
            foreach ($dirs as $dir) {
                $includes = array_merge($includes, ZMLoader::findIncludes($dir."/", $ext, $recursive));
            }
        }

        return $includes;
    }


    /**
     * Scan the given path for PHP files.
     *
     * @param string path The path to scan.
     * @param boolean recursive Flag to indicate if the path should be scanned recursively.
     * @return array A file map for the given path.
     */
    protected function scan($path, $recursive=true) {
        $files = ZMLoader::findIncludes($path, '.php', $recursive);
        $map = array();
        foreach ($files as $file) {
            $name = str_replace('.php', '', basename($file));
            // support for Name.class.php style
            $name = str_replace('.class', '', $name);
            if ($name == strtolower($name)) {
                // static, so make it unique
                $name = $file;
            }
            $map[$name] = $file;
        }

        return $map;
    }

    /**
     * Normalize class names based on the filename
     *
     * <p>This is pretty much following Java conventions.</p>
     *
     * @param string filename The filename.
     * @return string A corresponding class name.
     */
    public static function makeClassname($filename) {
        // strip potential file extension and dir
        $classname = str_replace('.php', '', basename($filename));
        // '_' == word boundary
        $classname = str_replace(array('_', '-'), ' ', $classname);
        // capitalise words
        $classname = ucwords($classname);
        // cuddle together :)
        $classname = str_replace(' ', '', $classname);
        return $classname;
    }

    /**
     * Get class hierachy for the given class/object.
     *
     * @param mixed object The object or class name.
     * @return array The class hierachy.
     */
    public static function getClassHierachy($object) {
        $hierachy = array($object);
        while($object = get_parent_class($object)) { 
            $hierachy[] = $object;
        }
        return $hierachy;
    }

}

?>
