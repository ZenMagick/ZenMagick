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
 *
 * $Id$
 */
?>
<?php

if (!class_exists("ZMObject")) {

    /**
     * ZenMagick base class.
     *
     * <p>This is the base class for all ZenMagick classes and contains some very basic
     * stuff that might be usefull for most/all classes.</p>
     *
     * @author mano
     * @package net.radebatz.zenmagick
     */
    class ZMObject {
        var $loader_;

        /**
         * Default c'tor.
         */
        function ZMObject() {
        global $zm_loader;

            $this->loader_ =& $zm_loader;
        }

        /**
         * Default c'tor.
         */
        function __construct() {
            $this->ZMObject();
        }

        /**
         * Default d'tor.
         */
        function __destruct() {
        }


        /**
         * Shortcut to create new class instances.
         *
         * @param string name The class name.
         * @param var args A variable number of arguments that will be used as arguments for
         * @return mixed An instance of the class denoted by <code>$name</code> or <code>null</code>.
         */
        function create($name) {
            $args = func_get_args();
            array_shift($args);
            return $this->loader_->createWithArgs($name, $args);
        }
    }


    /**
     * Simple <em>ZenMagick</em> logging function.
     *
     * @package net.radebatz.zenmagick
     * @param string msg The message to log.
     * @param int level Optional level (default: 2).
     */
    function zm_log($msg, $level=2) { if (zm_setting('isLogEnabled') && $level <= zm_setting('logLevel')) error_log($msg); }


    /**
     * Configuration lookup.
     *
     * @package net.radebatz.zenmagick
     * @param string name The setting to check.
     * @return mixed The setting value or <code>null</code>.
     */
    function zm_setting($name) {
    global $_ZM_SETTINGS;

        if (!array_key_exists($name, $_ZM_SETTINGS)) {
            zm_log("can't find setting: '".$name."'");
            if (zm_setting('isDieOnError')) die("can't find setting: '".$name."'");
            return null;
        }
        return $_ZM_SETTINGS[$name];
    }


    /**
     * Set configuration value.
     *
     * @package net.radebatz.zenmagick
     * @param string name The setting to check.
     * @param mixed value (New) value.
     * @return mixed The old setting value or <code>null</code>.
     */
    function zm_set_setting($name, $value) {
    global $_ZM_SETTINGS;

        $oldValue = array_key_exists($name, $_ZM_SETTINGS) ? $_ZM_SETTINGS[$name] : null;
        $_ZM_SETTINGS[$name] = $value;

        return $oldValue;
    }


    /**
     * Check if the given string starts with the provided string.
     *
     * @package net.radebatz.zenmagick
     * @param string s The haystack.
     * @param string start The needle.
     * @return bool <code>true</code> if <code>$s</code> starts with <code>$start</code>,
     *  <code>false</code> if not.
     */
    function zm_starts_with($s, $start) {
        return 0 === strpos($s, $start);
    }


    /**
     * Check if the given string ends with the provided string.
     *
     * @package net.radebatz.zenmagick
     * @param string s The haystack.
     * @param string end The needle.
     * @return bool <code>true</code> if <code>$s</code> ends with <code>$start</code>,
     *  <code>false</code> if not.
     */
    function zm_ends_with($s, $end) {
        $endLen = strlen($end);
        return $end == substr($s, -$endLen);
    }


    /**
     * Scan (recursively) for <code>.php</code> files.
     *
     * <p>It is worth mentioning that directories will always be processed only after
     * all plain files in a directory are done.</p>
     *
     * @package net.radebatz.zenmagick
     * @param string dir The name of the root directory to scan.
     * @param bool recursive If <code>true</code>, scan recursively.
     * @return array List of full filenames of <code>.php</code> files.
     */
    $_zm_includes_buffer = array();
    function zm_find_includes($dir, $recursive=false, $root=true) {
    global $_zm_includes_buffer;

        if ($root) $_zm_includes_buffer = array();

        $includes = array();
        if (!file_exists($dir) || !is_dir($dir)) {
            return $includes;
        }

        // save directories for later
        $dirs = array();

        $handle = @opendir($dir);
        while (false !== ($file = readdir($handle))) { 
            if ("." == $file || ".." == $file)
                continue;

            $file = $dir.$file;
            if (is_dir($file)) {
                array_push($dirs, $file);
            } else if (zm_ends_with($file, ".php")) {
                array_push($_zm_includes_buffer, $file);
            }
        }
        @closedir($handle);

        // process last
        if ($recursive) {
            foreach ($dirs as $dir) {
                zm_find_includes($dir."/", $recursive, false);
            }
        }

        return $_zm_includes_buffer;
    }


    /**
     * Normalize class names based on the filename
     *
     * <p>This is pretty much following Java conventions.</p>
     *
     * @package net.radebatz.zenmagick
     * @param string filename The filename.
     * @return string A corresponding class name.
     */
    function zm_mk_classname($filename) {
        // strip potential file extension and dir
        $classname = str_replace('.php', '', basename($filename));
        // '_' == word boundary
        $classname = str_replace('_', ' ', $classname);
        // capitalise words
        $classname = ucwords($classname);
        // cuddle together :)
        $classname = str_replace(' ', '', $classname);
        return $classname;
    }


    /**
     * Get an instance for the given class name.
     *
     * @package net.radebatz.zenmagick
     * @param string className The name of the class to instantiate.
     * @param string isa Optional <em>IS A</em> check.
     * @param array args Optional constructor arguments.
     * @return mixed A class instance or <code>null</code>.
     */
    function zm_get_instance($className, $isa=null, $args=null) {
        if (class_exists($className)) {
            $args = null == $args ? array() : $args;
            zm_log("creating new " . $className, 4);
            $obj = null;
            switch (count($args)) {
            case 0:
                $obj = new $className();
                break;
            case 1:
                $obj = new $className($args[0]);
                break;
            case 2:
                $obj = new $className($args[0], $args[1]);
                break;
            case 3:
                $obj = new $className($args[0], $args[1], $args[2]);
                break;
            case 4:
                $obj = new $className($args[0], $args[1], $args[2], $args[3]);
                break;
            default:
                zm_log("unsupported number of arguments " . $className, 2);
                zm_backtrace();
                break;
            }
            if (null == $isa || is_a($obj, $isa)) {
                return $obj;
            }
        }
        return null;
    }


    /**
     * stripslashes incl. array support.
     *
     * @package net.radebatz.zenmagick
     * @param mixed value A value to strip.
     * @return mixed The stripped value.
     */
    function zm_stripslashes($value) {
        if (!get_magic_quotes_gpc())
            return $value;

       return is_array($value) ?  array_map('zm_stripslashes', $value) : stripslashes($value);
    }


    /**
     * Helper function to dump the ZenMagick environment.
     *
     * @package net.radebatz.zenmagick
     */
    function zm_env() {
    global $zm_loader, $_ZM_SETTINGS;

        echo "<h3><em>ZenMagick</em> class instances</h3>";
        echo "<ul>";

        // get proper class names in PHP4
        $classes = array();
        foreach ($zm_loader->getClassPath() as $clazz => $path) {
            $classes[strtolower($clazz)] = $clazz;
        }

        ksort($GLOBALS);
        foreach ($GLOBALS as $name => $instance) {
            if (zm_starts_with($name, "zm_")) {
                if (is_object($instance)) {
                    // get proper class name...
                    $clazz = strtolower(get_class($instance));
                    echo "<li>$" . $name. " :: " . (array_key_exists($clazz, $classes) ? $classes[$clazz] : get_class($instance)) . "</li>";
                }
            }
        }
        echo "</ul>";

        echo "<h3><em>ZenMagick</em> functions</h3>";
        echo "<ul>";
        $functions = get_defined_functions();
        sort($functions["user"]);
        foreach ($functions["user"] as $function) {
            if (zm_starts_with($function, "zm_")) {
                echo "<li>" . $function . "</li>";
            }
        }
        echo "</ul>";

        echo "<h3><em>ZenMagick</em> settings</h3>";
        echo "<ul>";
        foreach ($_ZM_SETTINGS as $key => $value) {
            if (zm_starts_with($key, 'is')) { $value = $value ? "true" : "false"; }
            echo "<li>" . $key . " = " . $value . "</li>";
        }
        echo "</ul>";

    }


    /**
     * Split image name into components that we need to process it.
     *
     * @package net.radebatz.zenmagick
     * @param string image The image.
     * @return array An array consisting of [optional subdirectory], [file extension], [basename]
     */
    function _zm_split_image_name($image) {
        // optional subdir on all levels
        $subdir = dirname($image);
        $subdir = "." == $subdir ? "" : $subdir."/";

        // the file extension
        $ext = substr($image, strrpos($image, '.'));

        // filename without suffix
        $basename = '';
        if ('' != $image) {
            $basename = ereg_replace($ext, '', $image);
        }

        return array($subdir, $ext, $basename);
    }


    /**
     * Look up additional product images.
     *
     * @package net.radebatz.zenmagick
     * @param string image The image to look up.
     * @return array An array of <code>ZMImageInfo</code> instances.
     */
    function _zm_get_additional_images($image) {
    global $zm_loader;

        $comp = _zm_split_image_name($image);
        $subdir = $comp[0];
        $ext = $comp[1];
        $realImageBase = basename($comp[2]);

        // directory to scan
        $dirname = zm_image_href($sizedir.$subdir, false);

        $imageList = array();
        if ($dir = @dir($dirname)) {
            while ($file = $dir->read()) {
                if (!is_dir($dirname . $file)) {
                    if (zm_ends_with($file, $ext)) {
                        if (1 == preg_match("/" . $realImageBase . "/i", $file)) {
                            if ($file != basename($image)) {
                                if ($realImageBase . ereg_replace($realImageBase, '', $file) == $file) {
                                    array_push($imageList, $file);
                                }
                            }
                        }
                    }
                }
            }
            $dir->close();
            sort($imageList);
        }

        // create ZMImageInfo list...
        $imageInfoList = array();
        foreach ($imageList as $aimg) {
            array_push($imageInfoList, $zm_loader->create("ImageInfo", $subdir.$aimg));
        }

        return $imageInfoList;
    }


    /**
     * Simple wrapper around <code>debug_backtrace()</code>.
     *
     * @package net.radebatz.zenmagick
     * @param string msg If set, die with the provided message.
     */
    function zm_backtrace($msg=null) {
        echo "<pre>";
        print_r(debug_backtrace());
        echo "</pre>";
        if (null !== $msg) die($msg);
    }


    /**
     * Check if a given value or array is empty.
     *
     * @package net.radebatz.zenmagick
     * @param mixed value The value or array to check.
     * @return bool <code>true> if the value is empty or <code>null</code>, <code>false</code> if not.
     */
    function zm_is_empty($value) { 
        // support for arrays
        if (is_array($value)) {
            return 0 < count($value);
        }

        return (empty($value) && 0 == strlen($value)) || null == $value || 0 == strlen(trim($value));
    }


    /**
     * Redirect to the given url.
     *
     * @package net.radebatz.zenmagick
     * @param string url A fully qualified url.
     */
    function zm_redirect($url) { zen_redirect($url); }


    /**
     * Exit execution.
     *
     * <p>Calling this function will end all request handling in an ordered manner.</p>
     *
     * @package net.radebatz.zenmagick
     */
    function zm_exit() { zen_exit(); }


    /**
     * Resolve the given zen-cart class.
     *
     * <p>This functuon ensures that the given class is loaded.</p>
     *
     * @package net.radebatz.zenmagick
     * @param string clazz The class name.
     */
    function zm_resolve_zc_class($clazz) {
        if (!class_exists($clazz)) {
            require_once(DIR_WS_CLASSES . $clazz. '.php');
        }
    }


    /**
     * Encrypt the given password.
     *
     * @package net.radebatz.zenmagick
     * @param string password The password to encrypt.
     * @return string The encrypted password.
     */
    function zm_encrypt_password($password) { return zen_encrypt_password($password); }


    /**
     * Get the currently elapsed page execution time.
     *
     * @package net.radebatz.zenmagick
     * @return long The execution time in milliseconds.
     */
    function zm_get_execution_time() {
        $startTime = explode (' ', PAGE_PARSE_START_TIME);
        $endTime = explode (' ', microtime());
        $executionTime = $endTime[1]+$endTime[0]-$startTime[1]-$startTime[0];
        return round($executionTime, 4);
    }

}

?>
