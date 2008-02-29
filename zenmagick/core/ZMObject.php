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
 * ZenMagick base class.
 *
 * <p>This is the base class for all ZenMagick classes and contains some very basic
 * stuff that might be usefull for most/all classes.</p>
 *
 * @author mano
 * @package org.zenmagick
 */
class ZMObject {
    private static $singletons_ = array();


    /**
     * Create new instance.
     */
    function __construct() {
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
    }


    /**
     * Shortcut to create new class instances.
     *
     * @param string name The class name.
     * @param var args A variable number of arguments that will be used as arguments for
     * @return mixed An instance of the class denoted by <code>$name</code> or <code>null</code>.
     * @deprecated Use <code>ZMLoader::make(..)</code> instead.
     */
    public static function create() {
        $args = func_get_args();
        return ZMLoader::make($args);
    }

    /**
     * Simple <em>ZenMagick</em> logging function.
     *
     * @param string msg The message to log.
     * @param int level Optional level (default: ZM_LOG_INFO).
     */
    public static function log($msg, $level=ZM_LOG_INFO) {
        if (zm_setting('isLogEnabled') && $level <= zm_setting('logLevel')) {
            if (zm_setting('isZMErrorHandler')) {
                trigger_error($msg, E_USER_NOTICE);
            } else {
                error_log($msg);
            }
        }
    }

    /**
     * Simple wrapper around <code>debug_backtrace()</code>.
     *
     * @param string msg If set, die with the provided message.
     */
    public static function backtrace($msg=null) {
        if (null !== $msg) {
            if (is_array($msg)) {
                echo "<pre>";
                print_r($msg);
                echo "</pre>";
            } else {
                echo '<h3>'.$msg.'</h3>';
            }
        }
        echo "<pre>";
        print_r(debug_backtrace());
        echo "</pre>";
        if (null !== $msg) {
            die();
        }
    }

    /**
     * Get a singleton instance of the calling class.
     *
     * @param string name The class name.
     * @return mixed A singleton object.
     */
    protected static function instance($name) {
        if (!array_key_exists($name, ZMObject::$singletons_)) {
            ZMObject::$singletons_[$name] = ZMLoader::make($name);
        }

        return ZMObject::$singletons_[$name];
    }

}

?>
