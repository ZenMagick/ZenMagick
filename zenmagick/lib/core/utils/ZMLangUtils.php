<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Language utils.
 *
 * @author DerManoMann
 * @package org.zenmagick.core.utils
 * @version $Id$
 */
class ZMLangUtils {

    /**
     * Check if a given value or array is empty.
     *
     * @param mixed value The value or array to check.
     * @return boolean <code>true</code> if the value is empty or <code>null</code>, <code>false</code> if not.
     */
    public static function isEmpty($value) {
        return empty($value);
    }

    /**
     * Check if the given string starts with the provided string.
     *
     * @param string s The haystack.
     * @param string start The needle.
     * @return boolean <code>true</code> if <code>$s</code> starts with <code>$start</code>,
     *  <code>false</code> if not.
     */
    public static function startsWith($s, $start) {
        return 0 === strpos($s, $start);
    }


    /**
     * Check if the given string ends with the provided string.
     *
     * @param string s The haystack.
     * @param string end The needle.
     * @return boolean <code>true</code> if <code>$s</code> ends with <code>$start</code>,
     *  <code>false</code> if not.
     */
    public static function endsWith($s, $end) {
        $endLen = strlen($end);
        return $end == substr($s, -$endLen);
    }

    /**
     * Check if the given value exists in the array or comma separated list.
     *
     * @param string value The value to search for.
     * @param mixed array Either an <code>array</code> or a string containing a comma separated list.
     * @return boolean <code>true</code> if the given value exists in the array, <code>false</code> if not.
     */
    public static function inArray($value, $array) {
        if (!is_array($array)) {
            $array = explode(',', $array);
        }
        return in_array($value, $array);
    }

    /**
     * Evaluate a string value as boolean.
     *
     * @param mixed value The value.
     * @return boolean The boolean value.
     */
    public static function asBoolean($value) {
        if (is_integer($value)) {
            return $value;
        }
        return in_array(strtolower($value), array('on', 'true', 'yes', '1'));
    }

    /**
     * Convert values to array where reasonable.
     *
     * @param mixed value The value to convert; either already an array or a URL query form string.
     * @return array The value as array.
     */
    public static function toArray($value) {
        if (null === $value) {
            return array();
        }
        if (is_array($value)) {
            return $value;
        }
        parse_str(urldecode($value), $map);
        // handle booleans
        foreach ($map as $key => $value) {
            if ('false' == $value || 'true' == $value) {
                $map[$key] = self::asBoolean($value);
            }
        }
        return $map;
    }

    /**
     * Create a unique key from all given parameters.
     *
     * @param var arg Arguments.
     * @return string a unique key based on the arguments.
     */
    public static function mkUnique() {
        $args = func_get_args();
        $key = '';
        foreach ($args as $arg) {
            if (is_array($arg)) {
                asort($arg);
                foreach ($arg as $ar) {
                    $key .= '@'.$ar;
                }
            } else {
                $key .= ':'.$arg;
            }
        }
        return md5($key);
    }

}

?>
