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
 * Misc. tools.
 *
 * @author DerManoMann
 * @package org.zenmagick.core.utils
 * @version $Id: ZMTools.php 2231 2009-05-21 04:57:23Z DerManoMann $
 */
class ZMTools {

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
        return self::inArray(strtolower($value), "on,true,yes,1");
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
        parse_str($value, $map);
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

    /**
     * Apply user/group settings to file(s) that should allow ftp users to modify/delete them.
     *
     * <p>The file group attribute is only going to be changed if the <code>$perms</code> parameter is not empty.</p>
     * 
     * <p>This method may be disabled by setting <em>zenmagick.core.fs.permissions.isFix</em> to <code>false</code>.</p>
     *
     * @param mixed files Either a single filename or list of files.
     * @param boolean recursive Optional flag to recursively process all files/folders in a given directory; default is <code>false</code>.
     * @param array perms Optional file permissions; defaults are taken from the settings <em>fs.permissions.defaults.folder</em> for folder,
     *  <em>fs.permissions.defaults.file</em> for files.
     */
    public static function setFilePerms($files, $recursive=false, $perms=array()) {
        if (!ZMSettings::get('zenmagick.core.fs.permissions.isFix')) {
            return;
        }
        if (null == self::$fileOwner || null == self::$fileGroup) {
            clearstatcache();
            self::$fileOwner = fileowner(__FILE__);
            self::$fileGroup = filegroup(__FILE__);
            if (0 == self::$fileOwner && 0 == self::$fileGroup) {
                return;
            }
        }
        
        if (!is_array($files)) {
            $files = array($files);
        }

        $filePerms = array_merge(array('file' => ZMSettings::get('zenmagick.core.fs.permissions.defaults.file', '0644'), 
                                    'folder' => ZMSettings::get('zenmagick.core.fs.permissions.defaults.folder', '0755')), $perms);

        foreach ($files as $file) {
            if (0 < count($perms)) {
                @chgrp($file, self::$fileGroup);
            }
            @chown($file, self::$fileOwner);
            $mod = $filePerms[(is_dir($file) ? 'folder' : 'file')];
            @chmod($file, $mod);

            if (is_dir($file) && $recursive) {
                $dir = $file;
                if (!self::endsWith($dir, DI)) {
                    $dir .= '/';
                }
                $subfiles = array();
                $handle = @opendir($dir);
                while (false !== ($file = readdir($handle))) { 
                    if ("." == $file || ".." == $file) {
                        continue;
                    }
                    $subfiles[] = $dir.$file;
                }
                @closedir($handle);
                self::setFilePerms($subfiles, $recursive, $perms);
            }
        }
    }

}

?>
