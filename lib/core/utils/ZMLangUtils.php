<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
 * PHP utils.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.utils
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
            return (bool)$value;
        }

        $value = strtolower($value);
        return $value == 'true' || $value == '1' || $value == 'on' || $value == 'yes';
        //return in_array(strtolower($value), array('on', 'true', 'yes', '1'));
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

    /**
     * Get bytes from K/M/G sizes
     *
     * <p>
     * echo ini_get('post_max_size'); // 8M
     * echo ZMLangUtils::asBytes('8M'); // 8388608
     * </p>
     *
     * @param string val number with g/k/m suffix.
     * @return int bytes from <code>val</code>.
     */
    public static function asBytes($val) {
        $val = trim($val);
        $unit = strtolower(substr($val,strlen($val/1),1));
        switch($unit) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
        }
        return $val;
    }

    /**
     * Simple stack trace.
     *
     * @param string msg Optional message; default is <code>null</code>.
     */
    public static function dumpStack($msg=null) {
        if ($msg) { echo '<h2>'.$msg.'</h2>'; }
        foreach (debug_backtrace() as $level) {
            $level = array_merge(array('line' => 'line:n/a ', 'function' => 'function:n/a ', 'file' => 'file:n/a '), $level);
            echo $level['line'].':'.$level['function'].':'.$level['file']."<br>\n";
        }
        echo '<br>';
    }

}
