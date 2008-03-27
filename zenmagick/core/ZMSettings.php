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
 * Settings (ZenMagick's configuration).
 *
 * @author mano
 * @package org.zenmagick
 * @version $Id$
 */
class ZMSettings {
    private static $settings_ = array();


    /**
     * Get the value for the given setting name.
     *
     * @param string name The setting to check.
     * @param mixed default Optional default value to be returned if setting not found; default is <code>null</code>.
     * @return mixed The setting value or <code>null</code>.
     */
    public static function get($name, $default=null) {
        if (!isset(ZMSettings::$settings_[$name])) {
            ZMObject::log("can't find setting: '".$name."'", ZM_LOG_WARN);
            return $default;
        }

        return ZMSettings::$settings_[$name];
    }

    /**
     * Set configuration value.
     *
     * @param string name The setting to check.
     * @param mixed value (New) value.
     * @return mixed The old setting value or <code>null</code>.
     */
    public static function set($name, $value) {
        $oldValue = isset(ZMSettings::$settings_[$name]) ? ZMSettings::$settings_[$name] : null;
        ZMSettings::$settings_[$name] = $value;

        return $oldValue;
    }

    /**
     * Get a map of all settings.
     *
     * @return array Map of all settings.
     */
    public static function getAll() {
        return ZMSettings::$settings_;
    }

    /**
     * Set a map of all settings.
     *
     * @param array settings Map of settings.
     */
    public static function setAll($settings) {
        ZMSettings::$settings_ = $settings;
    }

    /**
     * Add a map of settings.
     *
     * @param array settings Map of settings.
     */
    public static function addAll($settings) {
        ZMSettings::$settings_ = array_merge(ZMSettings::$settings_, $settings);
    }

}

?>
