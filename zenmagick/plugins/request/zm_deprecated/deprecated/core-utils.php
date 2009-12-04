<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
     * Simple <em>ZenMagick</em> logging function.
     *
     * @package org.zenmagick.deprecated
     * @param string msg The message to log.
     * @param int level Optional level (default: ZMLogging::INFO).
     * @deprecated Use <code>ZMObject::log()</code> instead.
     */
    function zm_log($msg, $level=ZMLogging::INFO) { ZMLogging::instance()->log($msg, $leve); }
    /**
     * Simple wrapper around <code>debug_backtrace()</code>.
     *
     * @package org.zenmagick.deprecated
     * @param string msg If set, die with the provided message.
     * @deprecated Use <code>ZMLogging::trace()</code> instead.
     */
    function zm_backtrace($msg=null) {
        ZMLogging::instance()->trace($msg);
    }
    /**
     * Resolve the given zen-cart class.
     *
     * <p>This functuon ensures that the given class is loaded.</p>
     *
     * @package org.zenmagick.deprecated
     * @param string clazz The class name.
     * @deprecated Use <code>ZMTools::resolveZCClass()</code> instead.
     */
    function zm_resolve_zc_class($clazz) { ZMTools::resolveZCClass($clazz); }
    /**
     * Get the currently elapsed page execution time.
     *
     * @package org.zenmagick.deprecated
     * @return long The execution time in milliseconds.
     * @deprecated Use <code>Runtime::getExecutionTime()</code> instead.
     */
    function zm_get_elapsed_time() { return Runtime::getExecutionTime(); }
    /**
     * Create a PHP directive for all global ZenMagick objects.
     *
     * <p>This can be used as argument for <code>eval(..)</code> to make all
     * ZenMagick globals available. Example: <code>eval(zm_globals());</code>.</p>
     *
     * @package org.zenmagick.deprecated
     * @return string A valid PHP global directive including all ZenMagick globals.
     * @deprecated No replacement as globals are generally deprecated
     */
    function zm_globals() {
        $code = 'global ';
        $first = true;
        foreach ($GLOBALS as $name => $instance) {
            if (zm_starts_with($name, "zm_")) {
                if (is_object($instance)) {
                    if (!$first) $code .= ", ";
                    $code .= '$'.$name;
                    $first = false;
                }
            }
        }
        $code .= ";";
        return $code;
    }
    /**
     * Configuration lookup.
     *
     * @package org.zenmagick.deprecated
     * @param string name The setting to check.
     * @param mixed default Optional default value to be returned if setting not found; default is <code>null</code>.
     * @return mixed The setting value or <code>null</code>.
     * @deprecated Use <code>ZMSettings::get()</code> instead.
     */
    function zm_setting($name, $default=null) { return ZMSettings::get($name, $default); }
    /**
     * Set configuration value.
     *
     * @package org.zenmagick.deprecated
     * @param string name The setting to check.
     * @param mixed value (New) value.
     * @return mixed The old setting value or <code>null</code>.
     * @deprecated Use <code>ZMSettings::set()</code> instead.
     */
    function zm_set_setting($name, $value) { return ZMSettings::set($name, $value); }
    /**
     * Get all settings.
     *
     * @package org.zenmagick.deprecated
     * @return array Map of all settings.
     * @deprecated Use <code>ZMSettings::getAll()</code> instead.
     */
    function zm_settings() { return ZMSettings::getAll(); }
    /**
     * Remove a directory (tree).
     *
     * @package org.zenmagick.deprecated
     * @param string dir The directory name.
     * @param boolean recursive Optional flag to enable/disable recursive deletion; (default is <code>true</code>)
     * @deprecated use ZMTools instead.
     */
    function zm_rmdir($dir, $recursive=true) {
        return ZMTools::rmdir($dir, $recursive);
    }
    /**
     * Make dir.
     *
     * @package org.zenmagick.deprecated
     * @param string dir The folder name.
     * @param int perms The file permisssions; (default: null)
     * @param boolean recursive Optional recursive flag; (default: <code>true</code>)
     * @return boolean <code>true</code> on success.
     * @deprecated use ZMTools instead.
     */
    function zm_mkdir($dir, $perms=null, $recursive=true) {
        return ZMTools::mkdir($dir, $perms, $recursive);
    }
    /**
     * Check if a given value or array is empty.
     *
     * @package org.zenmagick.deprecated
     * @param mixed value The value or array to check.
     * @return boolean <code>true</code> if the value is empty or <code>null</code>, <code>false</code> if not.
     * @deprecated use ZMTools instead.
     */
    function zm_is_empty($value) { 
        return ZMTools::isEmpty($value);
    }
    /**
     * Check if the given string starts with the provided string.
     *
     * @package org.zenmagick.deprecated
     * @param string s The haystack.
     * @param string start The needle.
     * @return boolean <code>true</code> if <code>$s</code> starts with <code>$start</code>,
     *  <code>false</code> if not.
     * @deprecated use ZMTools instead.
     */
    function zm_starts_with($s, $start) {
        return ZMTools::startsWith($s, $start);
    }
    /**
     * Check if the given string ends with the provided string.
     *
     * @package org.zenmagick.deprecated
     * @param string s The haystack.
     * @param string end The needle.
     * @return boolean <code>true</code> if <code>$s</code> ends with <code>$start</code>,
     *  <code>false</code> if not.
     * @deprecated use ZMTools instead.
     */
    function zm_ends_with($s, $end) {
        return ZMTools::endsWith($s, $end);
    }
    /**
     * Helper function to dump the ZenMagick environment.
     *
     * @package org.zenmagick.deprecated
     * @deprecated
     */
    function zm_env() {
    global $_ZM_SETTINGS;

        echo "<h3><em>ZenMagick</em> class instances</h3>";
        echo "<ul>";

        // get proper class names in PHP4
        $classes = array();
        foreach (ZMLoader::getClassPath() as $clazz => $path) {
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
     * Check if the given value exists in the array or comma separated list.
     *
     * @package org.zenmagick.deprecated
     * @param string value The value to search for.
     * @param mixed array Either an <code>array</code> or a string containing a comma separated list.
     * @return boolean <code>true</code> if the given value exists in the array, <code>false</code> if not.
     * @deprecated use ZMTools instead.
     */
    function zm_is_in_array($value, $array) {
        return ZMTools::inArray($value, $array);
    }
    /**
     * Exit execution.
     *
     * <p>Calling this function will end all request handling in an ordered manner.</p>
     *
     * @package org.zenmagick.deprecated
     * @deprecated use ZMRuntime instead.
     */
    function zm_exit() {
        Runtime::finish();
    }
    /**
     * Redirect to the given url.
     *
     * <p>This function wil also persist existing messages in the session in order to be
     * able to display them after the redirect.</p>
     *
     * @package org.zenmagick.deprecated
     * @param string url A fully qualified url.
     * @deprecated Use ZMRequest instead
     */
    function zm_redirect($url) {
        ZMRequest::instance()->redirect($url);
    }

?>
