<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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

if (!defined('DATE_RSS')) { define('DATE_RSS', "D, d M Y H:i:s T"); }

/**
 * (System) Tools.
 *
 * @author DerManoMann
 * @package org.zenmagick.misc
 * @version $Id: ZMTools.php 2231 2009-05-21 04:57:23Z DerManoMann $
 */
class ZMTools {
    const RANDOM_DIGITS = 'digits';
    const RANDOM_CHARS = 'chars';
    const RANDOM_MIXED = 'mixed';
    const RANDOM_HEX = 'hex';

    private static $seedDone = false;
    private static $fileOwner = null;
    private static $fileGroup = null;


    /**
     * Remove a directory (tree).
     *
     * @param string dir The directory name.
     * @param boolean recursive Optional flag to enable/disable recursive deletion; (default is <code>true</code>)
     * @return boolean <code>true</code> on success.
     */
    public static function rmdir($dir, $recursive=true) {
        if (is_dir($dir)) {
            if (substr($dir, -1) != '/') { $dir .= '/'; }
            $handle = opendir($dir);
            while (false !== ($file = readdir($handle))) {
                if ('.' != $file && '..' != $file) {
                    $path = $dir.$file;
                    if (is_dir($path) && $recursive) {
                        self::rmdir($path, $recursive);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            rmdir($dir);
        }
        return true;
    }


    /**
     * Make directory.
     *
     * @param string dir The folder name.
     * @param int perms The file permisssions (octal); default: <code>null</code> to use the value of setting
     *  <em>fs.permissions.defaults.folder</em>.
     * @param boolean recursive Optional recursive flag; (default: <code>true</code>)
     * @return boolean <code>true</code> on success.
     */
    public static function mkdir($dir, $perms=null, $recursive=true) {
    	clearstatcache();
        if (null == $dir || empty($dir)) {
            return false;
        }
        if (file_exists($dir) && is_dir($dir)) {
            return true;
        }

        $parent = dirname($dir);
        if (!file_exists($parent) && $recursive) {
            if (!self::mkdir($parent, $perms, $recursive))
                return false;
        }
        
        if (null === $perms) {
        	$perms = ZMSettings::get('fs.permissions.defaults.folder');
        }

        $result = @mkdir($dir, $perms);
        // somehow this always ends up 0755, even with 0777
        self::setFilePerms($dir, $recursive, array('folder' => $perms));

        if (!$result) {
            ZMLogging::instance()->log("insufficient permission to create directory: '".$dir.'"', ZMLogging::WARN);
        }
        
        return $result;
    }

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
     * Convert a numeric range definition into an array of single values.
     *
     * <p>A range might be a single value, a range; for example <em>3-8</em> or a list of both.</p>
     * <p>Valid examples of ranges are:</p>
     * <ul>
     *  <li>3</li>
     *  <li>3,4,8</li>
     *  <li>3,4-6,8</li>
     *  <li>1,3-5,9,13,100-302</li>
     * </ul>
     *
     * @param string range The range value.
     * @return array List of numeric (int) values.
     */
    public static function parseRange($range) {
        $arr = array();
        foreach (explode(',', $range) as $token) {
            if (!empty($token)) {
                $elems = explode('-', $token);
                $size = count($elems);
                if (1 == $size && !empty($elems[0])) {
                    $elem = (int)$elems[0];
                    $arr[$elem] = $elem;
                } else if (2 == $size && !empty($elems[0]) && !empty($elems[1])) {
                    for ($ii=(int)$elems[0]; $ii<=(int)$elems[1]; ++$ii) {
                        $arr[$ii] = $ii;
                    }
                }
            }
        }
        return $arr;
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
     * Parse a money amount.
     *
     * @param string amount The amount probably formatted according to the sessions currency setting.
     * @return float The amount.
     */
    public static function parseMoney($money) {
        $currency = ZMCurrencies::instance()->getCurrencyForCode(ZMRequest::getCurrencyCode());
        $amount = $currency->parse($money, false);
        return $amount;
    }

    /**
     * Helper for conditional get support.
     *
     * @package org.zenmagick.misc
     * @param string timestamp The last change date of whatever resource this is about.
     * @param boolean <code>true<code> <strong>if</strong> a body should be returned, 
     *  <code>false</code> if the resource changed.
     */
    public static function ifModifiedSince($timestamp) {
        // A PHP implementation of conditional get, see 
        // http://fishbowl.pastiche.org/archives/001132.html
        $last_modified = substr(date('r', $timestamp), 0, -5).'GMT';
        $etag = '"'.md5($last_modified).'"';
        // Send the headers
        header("Last-Modified: $last_modified");
        header("ETag: $etag");
        // See if the client has provided the required headers
        $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
            stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :
            true;
        $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
            stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : 
            true;
        if (!$if_modified_since && !$if_none_match) {
            return true;
        }
        // At least one of the headers is there - check them
        if ($if_none_match && $if_none_match != $etag) {
            return true; // etag is there but doesn't match
        }
        if ($if_modified_since && $if_modified_since != $last_modified) {
            return true; // if-modified-since is there but doesn't match
        }
        // Nothing has changed since their last request - serve a 304 and exit
        header('HTTP/1.0 304 Not Modified');
        return false;
    }

    /**
     * Sanitize the given value.
     *
     * @param mixed value A string or array.
     * @return mixed A sanitized version.
     */
    public static function sanitize($value) {
        if (is_string($value)) {
            $value = preg_replace('/ +/', ' ', $value);
            $value = preg_replace('/[<>]/', '_', $value);
            if (get_magic_quotes_gpc()) {
                $value = stripslashes($value);
            }
            return trim($value);
        } elseif (is_array($value)) {
            reset($value);
            while (list($key, $val) = each($value)) {
                $value[$key] = self::sanitize($val);
            }
            return $value;
        }

        return $value;
    }

    /**
     * Convert a (UI) date from one format to another.
     *
     * @param string s The date as received via the UI.
     * @param string from The current format of the date string.
     * @param string to The target format.
     * @return string The formatted date string or <code>''</code> (if <code>$s</code> is empty).
     */
    public static function translateDateString($s, $from, $to) {
        if (empty($s)) {
            return null;
        }
        $st = $to;
        foreach (self::parseDateString($s, $from) as $token => $value) {
            $st = str_replace($token, $value, $st);
        }
        return $st;
    }

    /**
     * Parse a date according to the given format.
     *
     * <p>This function supports the following format token:</p>
     * <ul>
     *  <li><code>hh</code> - hours</li>
     *  <li><code>ii</code> - minutes</li>
     *  <li><code>ss</code> - seconds</li>
     *  <li><code>dd</code> - day</li>
     *  <li><code>mm</code> - month</li>
     *  <li><code>cc</code> - century</li>
     *  <li><code>yy</code> - year</li>
     *  <li><code>yyyy</code> - full year (if found both <em>cc</em> and <em>yy</em> will be populated accordingly</li>
     * </ul>
     *
     * @param string s A date; usually either provided by the user or a database date.
     * @param string format The date format
     * @param array defaults Optional defaults for components; default is <code>null</code> for none.
     * @return array The individual date components as map using the token as keys.
     */
    public static function parseDateString($s, $format, $defaults=null) {
        $components = array(
              'hh' => '00', 'ii' => '00', 'ss' => '00',
              'dd' => '01', 'mm' => '01', 'cc' => '00', 'yy' => '00'
        );
        if (null !== $defaults) {
            $components = array_merge($components, $defaults);
        }

        foreach ($components as $token => $value) {
            $tpos = strpos($format, $token);
            if (false !== $tpos) {
                $components[$token] = substr($s, $tpos, 2);
            }
        }

        // special case for YYYY
        $cypos = strpos($format, 'yyyy');
        if (false !== $cypos) {
            $components['cc'] = substr($s, $cypos, 2);
            $components['yy'] = substr($s, $cypos+2, 2);
        }

        $components['yyyy'] = $components['cc'].$components['yy']; 

        // ensure all components are digits only
        foreach ($components as $key => $component) {
            if (!ctype_digit($component)) {
                $format = '%0'.strlen($component).'s';
                $components[$key] = sprintf($format, '0');
            }
        }

        // make yyy first to avoid wrong replacements later on
        return array_reverse($components);
    }

    /**
     * Parse RSS date.
     * 
     * @param string date The date.
     * @return array An array with 3 elements in the order [day] [month] [year].
     */
    public static function parseRssDate($date) {
        preg_match("/[a-zA-Z]+, ([0-3]?[0-9]) ([a-zA-Z]+) ([0-9]{2,4}) .*/", $date, $regs);
        return $regs[1].'/'.$regs[2].'/'.$regs[3];
    } 


    /**
     * Convert date to RSS date format.
     * 
     * @package org.zenmagick.misc
     * @param mixed date The date string, timestamp (long) or <code>null</code> to use the current date.
     * @return string A date string formatted according to RSS date rules.
     */
    public static function mkRssDate($date=null) {
        if (null === $date) {
            return date(DATE_RSS);
        }

        if (is_string($date)) {
            $date = strtotime($date);
        }

        return date(DATE_RSS, $date);
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
     * Compare URLs.
     *
     * <p>This is defined only for URLs within the store.</p>
     *
     * <p><strong>NOTE: This function may not work with SEO solutions.</strong></p>
     *
     * @param string url1 The first URL to compare.
     * @param string url2 Optional second URL; default is <code>null</code> to compare to the current URL.
     * @return boolean <code>true</code> if URLs are considered equal (based on various URL parameters).
     */
    public static function compareStoreUrl($url1, $url2=null) {
        // just in case
        $url1 = str_replace('&amp;', '&', $url1);
        if (null !== $url2) {
            $url2 = str_replace('&amp;', '&', $url2);
        }

        if ($url1 == $url2) {
            return true;
        }

        if (false !== strpos($url1, '//') || false !== strpos($url1, '?')) {
            $url1Token = parse_url($url1);
            parse_str($url1Token['query'], $query1);
        } else {
            parse_str($url1, $query1);
        }

        if (null !== $url2) {
            if (false !== strpos($url2, '//') || false !== strpos($url2, '?')) {
                $url2Token = parse_url($url2);
                parse_str($url2Token['query'], $query2);
            } else {
                parse_str($url2, $query2);
            }
        } else {
            parse_str(str_replace('&amp;', '&', ZMRequest::getQueryString()), $query2);
        }

        if (isset($url1Token) && null === $url2 && isset($url1Token['host']) && ZMRequest::getHostname() != $url1Token['host']) {
            return false;
        }
        if (isset($url1Token) && isset($url2Token) && isset($url1Token['host']) && isset($url2Token['host']) && $url1Token['host'] != $url2Token['host']) {
            return false;
        }

        $query1[ZM_PAGE_KEY] = (array_key_exists(ZM_PAGE_KEY, $query1) && !empty($query1[ZM_PAGE_KEY])) ? $query1[ZM_PAGE_KEY] : 'index';
        $query2[ZM_PAGE_KEY] = (array_key_exists(ZM_PAGE_KEY, $query2) && !empty($query2[ZM_PAGE_KEY])) ? $query2[ZM_PAGE_KEY] : 'index';

        $equal = $query1[ZM_PAGE_KEY] == $query2[ZM_PAGE_KEY];
        // additional test for sub parameter
        if ($equal) {
            $subArgs = array(
                'static' => array('cat'),
                'page' => array('id'),
                'index' => array('cPath', 'manufacturers_id'),
                'category' => array('cPath', 'manufacturers_id'),
                'products_info' => array('products_id'),
                'account_history_info' => array('order_id'),
                'product_reviews' => array('products_id'),
                'product_reviews_info' => array('products_id', 'reviews_id')
            );
            if (isset($subArgs[$query1[ZM_PAGE_KEY]])) {
                foreach ($subArgs[$query1[ZM_PAGE_KEY]] as $sub) {
                    if (array_key_exists($sub, $query1) || array_key_exists($sub, $query2)) {
                        $equal = array_key_exists($sub, $query1) && array_key_exists($sub, $query2) && $query1[$sub] === $query2[$sub];
                        if (!$equal) {
                            return false;
                        }
                    }
                }
            }
        }

        return $equal;
    }

    /**
     * Apply user/group settings to file(s) that should allow ftp users to modify/delete them.
     *
     * <p>The file group attribute is only going to be changed if the <code>$perms</code> parameter is not empty.</p>
     * 
     * <p>This method may be disabled by setting <em>fs.permissions.fix</em> to <code>false</code>.</p>
     *
     * @param mixed files Either a single filename or list of files.
     * @param boolean recursive Optional flag to recursively process all files/folders in a given directory; default is <code>false</code>.
     * @param array perms Optional file permissions; defaults are taken from the settings <em>fs.permissions.defaults.folder</em> for folder,
     *  <em>fs.permissions.defaults.file</em> for files.
     */
    public static function setFilePerms($files, $recursive=false, $perms=array()) {
        if (!ZMSettings::get('fs.permissions.fix')) {
            return;
        }
        if (null == self::$fileOwner || null == self::$fileGroup) {
            clearstatcache();
            $referenceFile = ZMRuntime::getZMRootPath().'init.php';
            self::$fileOwner = fileowner($referenceFile);
            self::$fileGroup = filegroup($referenceFile);
            if (0 == self::$fileOwner && 0 == self::$fileGroup) {
                return;
            }
        }
        
        if (!is_array($files)) {
            $files = array($files);
        }

        $filePerms = array_merge(array('file' => ZMSettings::get('fs.permissions.defaults.file'), 'folder' => ZMSettings::get('fs.permissions.defaults.folder')), $perms);

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

    /**
     * Normalize filename.
     *
     * @param string filename The filename.
     * @return string The normalized filename.
     */
    public static function normalizeFilename($filename) {
        if (strpos($filename, '\\')) {
            $filename = preg_replace('/\\\\+/', '\\', $filename);
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
        }

        return $filename;
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
     * Generate a random value.
     *
     * @param int length The length of the random value.
     * @param string type Optional type; predefined values are: <em>mixed</em>, <em>chars</em>, <em>digits</em> or <em>hex</em>; default is <em>mixed</em>.
     *  Any other value will be used as the valid character range.
     * @return string The random string.
     */
    public static function random($length, $type='mixed') { 
        static $types	=	array(
            self::RANDOM_DIGITS => '0123456789', 
            self::RANDOM_CHARS => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            self::RANDOM_MIXED => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            self::RANDOM_HEX => '0123456789abcdef',
        );

        if (!self::$seedDone) {
            mt_srand((double)microtime() * 1000200);
            self::$seedDone = true;
        }

        $chars = array_key_exists($type, $types) ? $types[$type] : $type;
        $max=	strlen($chars) - 1;
        $token = '';
        for ($ii=0; $ii < $length; ++$ii) {
            $token .=	$chars[(rand(0, $max))];
        }

        return $token;
    }

    /**
     * Resolve the given zen-cart class.
     *
     * <p>This function ensures that the given class is loaded.</p>
     *
     * @param string clazz The class name.
     */
    public static function resolveZCClass($clazz) {
        if (!class_exists($clazz)) {
            require_once DIR_FS_CATALOG . DIR_WS_CLASSES . $clazz. '.php';
        }
    }

    /**
     * Move files and folders.
     *
     * @param string src The source (file or folder).
     * @param string target The target (file or folder).
     * @return boolean <code>true</code> on success.
     */
    public static function move($src, $target) {
        if (is_dir($src)) {
            if (is_file($target)) {
                return false;
            }
            if ('/' != substr($src, -1)) {
                $src .= '/';
            }
            if ('/' != substr($target, -1)) {
                $target .= '/';
            }

            ZMTools::mkdir($target);
            $handle = opendir($src);
            if ($handle = opendir($src)) {
                while (false !== ($file = readdir($handle))) {
                    if ("." == $file || ".." == $file) {
                        continue;
                    }
                    $fullfile = $src.$file;
                    if (is_dir($fullfile)) {
                        if (!ZMTools::move($fullfile.'/', $target.$file.'/')) {
                            return false;
                        }
                    } else {
                        if (!copy($fullfile, $target.$file)) {
                            return false;
                        }
                    }
                }
                closedir($handle);
                return ZMTools::rmdir($src, true);
            } else {
                return false;
            }
        } else {
            if (is_dir($target)) {
                if ('/' != substr($target, -1)) {
                    $target .= '/';
                }
                ZMTools::mkdir($target);
                if (!copy($src, $target.basename($src))) {
                    return false;
                }
            } else {
                ZMTools::mkdir(dirname($target));
                if (!copy($src, $target)) {
                    return false;
                }
            }
            return unlink($src);
        }
    }

    /**
     * Unzip a file into the given directory.
     *
     * @param string filename The zip filename.
     * @param string target The target directory.
     * @return boolean <code>true</code> on success.
     */
    public static function unzip($filename, $target) {
        if (!function_exists('zip_open')) {
            return false;
        }
        if ('/' != substr($target, -1)) {
            $target .= '/';
        }

        if ($zhandle = zip_open($filename)) {
            while ($zentry = zip_read($zhandle)) {
                if (zip_entry_open($zhandle, $zentry, 'r')) {
                    $entryFilename = $target.zip_entry_name($zentry);
                    // ensure folder exists, otherwise things get dropped silently
                    ZMTools::mkDir(dirname($entryFilename));
                    $buffer = zip_entry_read($zentry, zip_entry_filesize($zentry));
                    zip_entry_close($zentry);
                    $fp = fopen($entryFilename, 'wb');
                    fwrite($fp, "$buffer");
                    fclose($fp);
                    self::setFilePerms($entryFilename);
                } else {
                    return false;
                }
            }
            zip_close($zhandle);
            return true;
        }
    }

    /**
     * fmod variant that can handle values < 1.
     */
    public function fmod_round($x, $y) {
        $x = strval($x);
        $y = strval($y);
        $zc_round = ($x*1000)/($y*1000);
        $zc_round_ceil = (int)($zc_round);
        $multiplier = $zc_round_ceil * $y;
        $results = abs(round($x - $multiplier, 6));
        return $results;
    }

}

?>
