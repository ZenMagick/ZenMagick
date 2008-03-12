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
 *
 * $Id$
 */
?>
<?php


    /**
     * Simple <em>ZenMagick</em> logging function.
     *
     * @package org.zenmagick
     * @param string msg The message to log.
     * @param int level Optional level (default: ZM_LOG_INFO).
     * @deprecated Use <code>ZMObject::log()</code> instead.
     */
    function zm_log($msg, $level=ZM_LOG_INFO) { ZMObject::log($msg, $leve); }
    /**
     * Simple wrapper around <code>debug_backtrace()</code>.
     *
     * @package org.zenmagick
     * @param string msg If set, die with the provided message.
     * @deprecated Use <code>ZMObject::backtrace()</code> instead.
     */
    function zm_backtrace($msg=null) {
        ZMObject::backtrace($msg);
    }
    /**
     * Check if a given value or array is empty.
     *
     * @package org.zenmagick
     * @param mixed value The value or array to check.
     * @return boolean <code>true</code> if the value is empty or <code>null</code>, <code>false</code> if not.
     * @deprecated Use <code>empty()</code> instead.
     */
    function zm_is_empty($value) { 
        return empty($value);
    }
    /**
     * Resolve the given zen-cart class.
     *
     * <p>This functuon ensures that the given class is loaded.</p>
     *
     * @package org.zenmagick
     * @param string clazz The class name.
     * @deprecated Use <code>ZMLoader::resolveZCClass()</code> instead.
     */
    function zm_resolve_zc_class($clazz) { ZMLoader::resolveZCClass($clazz); }
    /**
     * Get the currently elapsed page execution time.
     *
     * @package org.zenmagick
     * @return long The execution time in milliseconds.
     * @deprecated Use <code>ZMRuntime::getExecutionTime()</code> instead.
     */
    function zm_get_elapsed_time() { return ZMRuntime::getExecutionTime(); }
    /**
     * Create a PHP directive for all global ZenMagick objects.
     *
     * <p>This can be used as argument for <code>eval(..)</code> to make all
     * ZenMagick globals available. Example: <code>eval(zm_globals());</code>.</p>
     *
     * @package org.zenmagick
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
     * @package org.zenmagick
     * @param string name The setting to check.
     * @return mixed The setting value or <code>null</code>.
     */
    function zm_setting($name) {
    global $_ZM_SETTINGS;

        if (!array_key_exists($name, $_ZM_SETTINGS)) {
            ZMObject::log("can't find setting: '".$name."'", ZM_LOG_WARN);
            return null;
        }
        return $_ZM_SETTINGS[$name];
    }


    /**
     * Set configuration value.
     *
     * @package org.zenmagick
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
     * Get all settings.
     *
     * @package org.zenmagick
     * @return array Map of all settings.
     */
    function zm_settings() {
    global $_ZM_SETTINGS;

        return $_ZM_SETTINGS;
    }


    /**
     * Check if the given string starts with the provided string.
     *
     * @package org.zenmagick
     * @param string s The haystack.
     * @param string start The needle.
     * @return boolean <code>true</code> if <code>$s</code> starts with <code>$start</code>,
     *  <code>false</code> if not.
     */
    function zm_starts_with($s, $start) {
        return 0 === strpos($s, $start);
    }


    /**
     * Check if the given string ends with the provided string.
     *
     * @package org.zenmagick
     * @param string s The haystack.
     * @param string end The needle.
     * @return boolean <code>true</code> if <code>$s</code> ends with <code>$start</code>,
     *  <code>false</code> if not.
     */
    function zm_ends_with($s, $end) {
        $endLen = strlen($end);
        return $end == substr($s, -$endLen);
    }


    /**
     * Helper function to dump the ZenMagick environment.
     *
     * @package org.zenmagick
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
     * Split image name into components that we need to process it.
     *
     * @package org.zenmagick
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
     * @package org.zenmagick
     * @param string image The image to look up.
     * @return array An array of <code>ZMImageInfo</code> instances.
     */
    function _zm_get_additional_images($image) {
        $comp = _zm_split_image_name($image);
        $subdir = $comp[0];
        $ext = $comp[1];
        $realImageBase = basename($comp[2]);

        // directory to scan
        $dirname = DIR_FS_CATALOG.DIR_WS_IMAGES.$subdir;

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
            array_push($imageInfoList, ZMLoader::make("ImageInfo", $subdir.$aimg));
        }

        return $imageInfoList;
    }




    /**
     * Redirect to the given url.
     *
     * <p>This function wil also persist existing messages in the session in order to be
     * able to display them after the redirect.</p>
     *
     * @package org.zenmagick
     * @param string url A fully qualified url.
     */
    function zm_redirect($url) {
        if (ZMMessages::instance()->hasMessages()) {
            $session = ZMRequest::getSession();
            $session->setMessages(ZMMessages::instance()->getMessages());
        }

        $url = str_replace('&amp;', '&', $url);

        header('Location: ' . $url);
        zm_exit();
    }


    /**
     * Exit execution.
     *
     * <p>Calling this function will end all request handling in an ordered manner.</p>
     *
     * @package org.zenmagick
     */
    function zm_exit() {
        zen_session_close();
        exit();
    }


    /**
     * Remove a directory (tree).
     *
     * @package org.zenmagick
     * @param string dir The directory name.
     * @param boolean recursive Optional flag to enable/disable recursive deletion; (default is <code>true</code>)
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
     * Make dir.
     *
     * @package org.zenmagick
     * @param string dir The folder name.
     * @param int perms The file permisssions; (default: 755)
     * @param boolean recursive Optional recursive flag; (default: <code>true</code>)
     * @return boolean <code>true</code> on success.
     */
    function zm_mkdir($dir, $perms=755, $recursive=true) {
        if (null == $dir || empty($dir)) {
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
     * @package org.zenmagick
     * @param string themeId The themeId to start with.
     * @return ZMTheme The final theme.
     */
    function zm_resolve_theme($themeId=ZM_DEFAULT_THEME) {
        // set up theme
        $theme = ZMThemes::instance()->getThemeForId($themeId);
        $themeInfo = $theme->getThemeInfo();

        // configure theme loader
        $themeLoader = ZMLoader::make("Loader", "themeLoader");
        $themeLoader->addPath($theme->getExtraDir());

        // add loader to root loader
        ZMLoader::instance()->setParent($themeLoader);

        if (zm_setting('isLegacyAPI')) { eval(zm_globals()); }

        // init l10n/i18n
        $session = ZMRequest::getSession();
        $language = $session->getLanguage();
        $theme->loadLocale($language);

        // use theme loader to load static stuff
        foreach ($themeLoader->getStatic() as $static) {
            require_once($static);
        }

        // check for theme switching
        if (ZMRuntime::getThemeId() != $themeInfo->getThemeId()) {
            return zm_resolve_theme(ZMRuntime::getThemeId(), true);
        }

        // finalise i18n
        zm_i18n_finalise();

        ZMEvents::instance()->fireEvent(null, ZM_EVENT_THEME_RESOLVED, array('theme' =>& $theme));

        return $theme;
    }

    /**
     * Dispatch the current request.
     *
     * @package org.zenmagick
     * @return boolean Always <code>true</code>.
     */
    function zm_dispatch() {
        $controller = ZMLoader::make(ZMLoader::makeClassname(ZMRequest::getPageName().'Controller'));
        if (null == $controller) {
            $controller = ZMLoader::make("DefaultController");
        }

        ZMRequest::setController($controller);

        if (zm_setting('isLegacyAPI')) { eval(zm_globals()); }

        // execute controller
        $view = $controller->process();

        // generate response
        if (null != $view) {
            $controller->exportGlobal("zm_view", $view);
            ZMEvents::instance()->fireEvent(null, ZM_EVENT_VIEW_START, array('view' =>& $view));
            $view->generate();
            ZMEvents::instance()->fireEvent(null, ZM_EVENT_VIEW_DONE);
        }

        return true;
    }

    /**
     * Check if the given value exists in the array or comma separated list.
     *
     * @package org.zenmagick
     * @param string value The value to search for.
     * @param mixed array Either an <code>array</code> or a string containing a comma separated list.
     * @return boolean <code>true</code> if the given value exists in the array, <code>false</code> if not.
     */
    function zm_is_in_array($value, $array) {
        if (!is_array($array)) {
            $array = explode(",", $array);
        }
        $array = array_flip($array);
        return isset($array[$value]);
    }


    /**
     * Custom error handler.
     *
     * @package org.zenmagick
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

        if (null != ($handle = fopen(zm_setting('zmLogFilename'), "a"))) {
            fputs($handle, "\"$time\",\"$errfile: $errline\",\"($errlevel) $errstr\"\r\n"); 
            fclose($handle); 
        }
    } 

?>
