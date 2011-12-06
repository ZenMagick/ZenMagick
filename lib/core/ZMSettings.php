<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;

use Symfony\Component\Yaml\Yaml;

/**
 * Settings (ZenMagick's configuration).
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core
 */
class ZMSettings {

    /**
     * Get the value for the given setting name.
     *
     * @param string name The setting to check.
     * @param mixed default Optional default value to be returned if setting not found; default is <code>null</code>.
     * @return mixed The setting value or <code>null</code>.
     */
    public static function get($name, $default=null) {
        return Runtime::getSettings()->get($name, $default);
    }

    /**
     * Set configuration value.
     *
     * <p>A value of <code>null</code> will remove the setting.</p>
     *
     * @param string name The setting to check.
     * @param mixed value (New) value.
     * @return mixed The old setting value or <code>null</code>.
     */
    public static function set($name, $value) {
        return Runtime::getSettings()->set($name, $value);
    }

    /**
     * Get a map of all settings.
     *
     * @return array Map of all settings.
     */
    public static function getAll() {
        return Runtime::getSettings()->getAll();
    }

    /**
     * Add a map of settings.
     *
     * @param array settings Map of settings.
     * @param boolean replace If <code>true</code> existing settings will be replaced; default is <code>true</code>.
     */
    public static function addAll($settings, $replace=true) {
        // old style map with path keys, not nested arrays
        $zettings = Runtime::getSettings();
        foreach ($settings as $name => $value) {
            if ($replace || !$zettings->exists($name)) {
                $zettings->set($name, $value);
            }
        }
    }

    /**
     * Check if a given setting exists.
     *
     * <p>This is useful in cases where <code>null</code> is a valid setting value. In that
     * case, the <code>get</code> method will be ambiguous and <code>exists</code> should
     * be used.</p>.
     *
     * @param string name The setting to check.
     * @return boolean <code>true</code> if a setting with the given name exists.
     */
    public static function exists($name) {
        return Runtime::getSettings()->exists($name);
    }

    /**
     * Append configuration value.
     *
     * @param string name The setting to append to.
     * @param mixed value The value to append.
     * @param string delim Optional delimiter to be used if the value exists and is not empty; default is <em>','</em>.
     * @return mixed The old setting value or <code>null</code>.
     */
    public static function append($name, $value, $delim=',') {
        return Runtime::getSettings()->append($name, $value, $delim);
    }

    /**
     * Load mappings from a YAML style string.
     *
     * @param string yaml The yaml style mappings.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public static function load($yaml, $override=true) {
        self::addAll(Yaml::parse($yaml), $override);
    }

}
