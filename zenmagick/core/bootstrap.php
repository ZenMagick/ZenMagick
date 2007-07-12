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
     * @param int level Optional level (default: ZM_LOG_INFO).
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

        if ($root) { $_zm_includes_buffer = array(); }

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
        $dirname = DIR_FS_CATALOG.DIR_WS_IMAGES.$subdir;
        //$dirname = zm_image_href($sizedir.$subdir, false);

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
     * Simple helper to strip unwanted stuff from a stack trace.
     *
     * @package net.radebatz.zenmagick
     */
    function _zm_clean_backtrace($stack) {
        foreach (array('db_', 'loader_') as $ignore) {
            if (isset($stack[$ignore])) {
                unset($stack[$ignore]);
            }
        }
        foreach ($stack as $key => $value) {
            if (is_array($value)) {
                $stack[$key] = _zm_clean_backtrace($value);
            } else if (is_object($value)) {
                $stack[$key] = get_class($value);
            }
        }

        return $stack;
    }

    /**
     * Simple wrapper around <code>debug_backtrace()</code>.
     *
     * @package net.radebatz.zenmagick
     * @param string msg If set, die with the provided message.
     */
    function zm_backtrace($msg=null) {
        echo "<pre>";
        print_r(_zm_clean_backtrace(debug_backtrace()));
        echo "</pre>";
        if (null !== $msg) {
            if (is_array($msg)) {
                print_r($msg);
            } else {
                echo $msg;
            }
            die();
        }
    }


    /**
     * Check if a given value or array is empty.
     *
     * @package net.radebatz.zenmagick
     * @param mixed value The value or array to check.
     * @return bool <code>true</code> if the value is empty or <code>null</code>, <code>false</code> if not.
     */
    function zm_is_empty($value) { 
        // support for arrays
        if (is_array($value)) {
            return 0 == count($value);
        }

        return (empty($value) && 0 == strlen($value)) || null == $value || 0 == strlen(trim($value));
    }


    /**
     * Redirect to the given url.
     *
     * <p>This function wil also persist existing messages in the session in order to be
     * able to display them after the redirect.</p>
     *
     * @package net.radebatz.zenmagick
     * @param string url A fully qualified url.
     */
    function zm_redirect($url) {
    global $zm_messages;

        if ($zm_messages->hasMessages()) {
            $session = new ZMSession();
            $session->setMessages($zm_messages->getMessages());
        }

        zen_redirect($url);
    }


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
     * Get the currently elapsed page execution time.
     *
     * @package net.radebatz.zenmagick
     * @return long The execution time in milliseconds.
     */
    function zm_get_elapsed_time() {
        $startTime = explode (' ', PAGE_PARSE_START_TIME);
        $endTime = explode (' ', microtime());
        $executionTime = $endTime[1]+$endTime[0]-$startTime[1]-$startTime[0];
        return round($executionTime, 4);
    }


    /**
     * Remove a directory (tree).
     *
     * @package net.radebatz.zenmagick
     * @param string dir The directory name.
     * @param bool recursive Optional flag to enable/disable recursive deletion; (default is <code>true</code>)
     */
    function zm_rmdir($dir, $recursive=true) {
        if (is_dir($dir)) {
            if (substr($dir, -1) != '/') { $dir .= '/'; }
            $handle = opendir($dir);
            while (false !== ($file = readdir($handle))) {
                if ('.' != $file && '..' != $file) {
                    $path = $dir.$file;
                    if (is_dir($path) && $recursive) {
                        zm_rmdir($path, $recursive);
                    } else {
                       unlink($path);
                    }
                }
            }
            closedir($handle);
            rmdir($dir);
        }
    }


    /**
     * Get class hierachy for the given class/object.
     *
     * @package net.radebatz.zenmagick
     * @param mixed object The object or class name.
     * @return array The class hierachy.
     */
    function zm_class_hierachy($object) {
        $hierachy = array($object);
        while($object = get_parent_class($object)) { $hierachy[] = $object; }
        return $hierachy;
    }

    /**
     * Make dir.
     *
     * @package net.radebatz.zenmagick
     * @param string dir The folder name.
     * @param int perms The file permisssions; (default: 755)
     * @param bool recursive Optional recursive flag; (default: <code>true</code>)
     * @return bool <code>true</code> on success.
     */
    function zm_mkdir($dir, $perms=755, $recursive=true) {
        if (null == $dir || zm_is_empty($dir)) {
            return false;
        }
        if (file_exists($dir) && is_dir($dir))
            return true;

        $parent = dirname($dir);
        if (!file_exists($parent) && $recursive) {
            if(!zm_mkdir($parent, $perms, $recursive))
                return false;
        }
        $result = mkdir($dir, octdec($perms));
        return $result;
    }

    /**
     * Create a PHP directive for all global ZenMagick objects.
     *
     * <p>This can be used as argument for <code>eval(..)</code> to make all
     * ZenMagick globals available. Example: <code>eval(zm_globals());</code>.</p>
     *
     * @package net.radebatz.zenmagick
     * @return string A valid PHP global directive including all ZenMagick globals.
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
     * Load locale settings (l10n/i18n)for the given theme.
     *
     * <p>NOTE: This is only going to load mappings. However, since i18n
     * settings need to be set using <code>define(..)</code>, this is done
     * in a separate function, once loading (and theme switching) is over.</p>
     *
     * @package net.radebatz.zenmagick
     * @param ZMTheme theme The theme.
     * @param string languageName The language name.
     */
    function zm_load_theme_locale($theme, $languageName) {
        $path = $theme->getLangDir().$languageName."/";
        $l10n = $path . "l10n.php";
        if (file_exists($l10n)) {
            require($l10n);
        }
        $i18n = $path . "i18n.php";
        if (file_exists($i18n)) {
            require($i18n);
        }
    }

    /**
     * Resolve theme incl. loader update, theme switching and all theme default
     * handling.
     *
     * <p>This is <strong>the</strong> method in the ZenMagick theme handling. It will:</p>
     * <ol>
     *  <li>Configure the theme loader to add theme specific code (controller) to the classpath</li>
     *  <li>Init l10n/i18n</li>
     *  <li>Load the theme specific <code>extra</code> code</li>
     *  <li>Check for theme switching and repeat the process if needed</li>
     * </ol>
     *
     * <p>Passing default theme id rather than the current theme id is equivalent to
     * enabling default theme fallback. Coincidentally, this is also the default behaviour.</p>
     *
     * @package net.radebatz.zenmagick
     * @param string themeId The themeId to start with.
     * @return ZMTheme The final theme.
     */
    function &zm_resolve_theme($themeId=ZM_DEFAULT_THEME) {
    global $zm_runtime, $zm_request, $zm_loader;

        // get root loader
        $rootLoader =& zm_get_root_loader();

        // set up theme
        $theme =& $zm_runtime->getThemeForId($themeId);
        $themeInfo =& $theme->getThemeInfo();

        // configure theme loader
        $themeLoader = new ZMLoader("themeLoader");
        $themeLoader->addPath($theme->getExtraDir());

        // add loader to root loader
        $rootLoader->setParent($themeLoader);

        eval(zm_globals());

        // these can be replaced by themes; will be reinitializes during theme switching
        $themeClasses = array(
            'zm_crumbtrail' => 'Crumbtrail',
            'zm_meta' => 'MetaTags',
        );
        foreach ($themeClasses as $name => $clazz) {
            $currentClazz = strtolower(get_class($$name));
            if ($currentClazz != strtolower($zm_loader->load($clazz))) {
                // update only if changed
                $$name = $zm_loader->create($clazz);
            }
        }

        // init l10n/i18n
        zm_load_theme_locale($theme, $zm_runtime->getLanguageName());

        // use theme loader to load static stuff
        foreach ($themeLoader->getStatic() as $static) {
            require_once($static);
        }

        // check for theme switching
        if ($zm_runtime->getThemeId() != $themeInfo->getThemeId()) {
            return zm_resolve_theme($zm_runtime->getThemeId(), true);
        }

        // finalise i18n
        zm_i18n_finalise();

        return $theme;
    }

    /**
     * Get the root loader.
     *
     * @package net.radebatz.zenmagick
     * @return ZMLoader The root loader.
     */
    function &zm_get_root_loader() {
    global $zm_loader;

        // get root loader
        $rootLoader =& $zm_loader;
        while (null != $rootLoader->parent_) {
            $rootLoader =& $rootLoader->parent_;
        }
        return $rootLoader;
    }

    /**
     * Dispatch the current request.
     *
     * @package net.radebatz.zenmagick
     * @return bool <code>true</code> if the request was dispatched, <code>false</code> if not.
     * @todo Support for internal forwards.
     */
    function zm_dispatch() {
    global $zm_runtime, $zm_request, $zm_loader;

        $controller = $zm_loader->create(zm_mk_classname($zm_request->getPageName().'Controller'));
        if (null == $controller) {
            $controller =& $zm_loader->create("DefaultController");
        }

        if ($controller->validateRequest()) {
            $zm_request->setController($controller);

            eval(zm_globals());

            // execute controller
            $view = $controller->process();
            $controller->exportGlobal("zm_view", $view);

            // generate response
            if (null != $view) {
                $view->generate();
            }

            return true;
        }

        return false;
    }

    /**
     * Check if the given value exists in the array or comma separated list.
     *
     * @package net.radebatz.zenmagick
     * @param string value The value to search for.
     * @param mixed array Either an <code>array</code> or a string containing a comma separated list.
     * @return bool <code>true</code> if the given value exists in the array, <code>false</code> if not.
     */
    function zm_is_in_array($value, $array) {
        if (!is_array($array)) {
            $array = explode(",", $array);
        }
        $array = array_flip($array);
        return isset($array[$value]);
    }


    /**
     * Fire an event.
     *
     * @package net.radebatz.zenmagick
     * @param mixed source The event source.
     * @param string eventId The event id.
     * @param array args Optional additional parameter; default is <code>null</code>.
     */
    function zm_fire_event(&$source, $eventId, $args=null) {
    global $zm_events;

        $zm_events->fireEvent($source, $eventId, $args);
    }


    /**
     * Custom error handler.
     *
     * @package net.radebatz.zenmagick
     * @param int errno The error level.
     * @param string errstr The error message.
     * @param string errfile The source filename.
     * @param int errline The line number.
     * @param array errcontext All variables of scope when error triggered.
     */
    function zm_error_handler($errno, $errstr, $errfile, $errline, $errcontext) { 
        // get current level
        $level = error_reporting(E_ALL);
        error_reporting($level);
        // disabled or not configured?
        if (0 == $level || $errno != ($errno&$level)) {
            return;
        }

        $time = date("d M Y H:i:s"); 
        // Get the error type from the error number 
        $errtypes = array (1    => "Error",
                           2    => "Warning",
                           4    => "Parsing Error",
                           8    => "Notice",
                           16   => "Core Error",
                           32   => "Core Warning",
                           64   => "Compile Error",
                           128  => "Compile Warning",
                           256  => "User Error",
                           512  => "User Warning",
                           1024 => "User Notice",
                           2048 => "Strict",
                           4096 => "Recoverable Error"
        ); 


        if (isset($errtypes[$errno])) {
            $errlevel = $errtypes[$errno]; 
        } else {
            $errlevel = "Unknown";
        }

        $handle = fopen(zm_setting('zmLogFilename'), "a"); 
        fputs($handle, "\"$time\",\"$errfile: $errline\",\"($errlevel) $errstr\"\r\n"); 
        fclose($handle); 
    } 

}

?>
