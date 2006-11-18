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

    /**
     * Get the (full file system) <code>zen-cart</code> include directory.
     *
     * @package net.radebatz.zenmagick
     * @return string <code>zen-cart</code> include directory.
     */
    function zm_get_zen_include_dir() { return dirname(dirname(dirname(__FILE__))); }


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
     * Return the request method for the current server request.
     *
     * @package net.radebatz.zenmagick
     * @return string The request method for the current server request.
     */
    function zm_get_request_method() { return $_SERVER['REQUEST_METHOD']; }


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
        if (strlen($s) < strlen($end))
            return false;
        return $end == substr($s, strlen($s) - strlen($end));
    }


    /**
     * Scan (recursively) for <code>.php</code> files.
     *
     * <p>It is worth mentioning that directories will always be processed only after all plain files
     * in a directory.</p>
     *
     * @package net.radebatz.zenmagick
     * @param string dir The name of the root directory to scan.
     * @param bool recursive If <code>true</code>, scan recursively.
     * @return array List of full filenames of <code>.php</code> files.
     */
    function zm_find_includes($dir, $recursive = false) {
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

            $file = $dir."/".$file;
            if (is_dir($file)) {
                array_push($dirs, $file);
            } else if (zm_ends_with($file, ".php")) {
                array_push($includes, $file);
            }
        }
        @closedir($handle);

        // process last
        if ($recursive) {
            foreach ($dirs as $dir) {
                $includes = array_merge($includes, zm_find_includes($dir, $recursive));
            }
        }
        return $includes;
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
     * @return mixed A class instance or <code>null</code>.
     */
    function zm_get_instance($className, $isa=null) {
        if (class_exists($className)) {
            zm_log("creating new " . $className, 4);
            $obj = new $className();
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
                    echo "<li>$" . $name. " :: " . $classes[get_class($instance)] . "</li>";
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
            array_push($imageInfoList, new ZMImageInfo($subdir.$aimg));
        }

        return $imageInfoList;
    }


    /**
     * Simple wrapper around <code>debug_backtrace()</code>.
     *
     * @package net.radebatz.zenmagick
     * @param bool die If true, die after printing the stack.
     */
    function zm_backtrace($die=true) {
        echo "<pre>";
        print_r(debug_backtrace());
        echo "</pre>";
        if ($die) die("trace'n die");
    }


?>
