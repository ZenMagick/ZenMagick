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

if (!defined('DATE_RSS')) { define('DATE_RSS', "D, d M Y H:i:s T"); }

/**
 * (System) Tools.
 *
 * @author mano
 * @package org.zenmagick.misc
 * @version $Id$
 */
class ZMTools {

    /**
     * Convert a UI date into the internal data format.
     *
     * <p>This is typically used by controller/business code to convert user input before 
     * storing it in the database.</p>
     *
     * @param string date The date as received via the UI.
     * @return string The formatted date.
     * @deprecated use translateDateString instead
     */
    public static function ui2date($date) {
        if (empty($date)) {
            return '';
        }
        // The individual date components in the order dd, mm, cc, yy.
        $da = self::parseDateString($date, UI_DATE_FORMAT);
        return date(ZM_DATETIME_FORMAT, mktime(0, 0, 0, $da['mm'], $da['dd'], (int)($da['cc'].$da['yy'])));
    }
    /**
     * Parse a date according to a given format.
     *
     * <p>This function will honour <code>DD</code>, <code>MM</code>, <code>CC</code>, <code>YY</code>
     * and <code>YYYY</code> in the format.</p>
     *
     * <p><strong>NOTE:</strong> The format is *not* case sensitive.</p>
     *
     * @param string date A date (usually provided by the user).
     * @param string format The date format
     * @return array The individual date components in the order dd, mm, cc, yy.
     * @deprecated use parseDateString instead
     */
    public static function parseDate($date, $format) {
        $c = self::parseDateString($date, $format);
        return array($c['DD'], $c['MM'], $c['CC'], $c['YY']);
    }


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
     * @param int perms The file permisssions; (default: 755)
     * @param boolean recursive Optional recursive flag; (default: <code>true</code>)
     * @return boolean <code>true</code> on success.
     */
    public static function mkdir($dir, $perms=755, $recursive=true) {
        if (null == $dir || empty($dir)) {
            return false;
        }
        if (file_exists($dir) && is_dir($dir))
            return true;

        $parent = dirname($dir);
        if (!file_exists($parent) && $recursive) {
            if (!self::mkdir($parent, $perms, $recursive))
                return false;
        }
        $result = mkdir($dir, octdec($perms));
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
            $array = explode(",", $array);
        }
        $array = array_flip($array);
        return isset($array[$value]);
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
            $value = ereg_replace(' +', ' ', $value);
            $value = preg_replace("/[<>]/", '_', $value);
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
        ereg("[a-zA-Z]+, ([0-3]?[0-9]) ([a-zA-Z]+) ([0-9]{2,4}) .*", $date, $regs);
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
     * @param string url1 The first URL to compare.
     * @param string url2 Optional second URL; default is <code>null</code> to compare to the current URL.
     * @return boolean <code>true</code> if URLs are considered equal (based on various URL parameters).
     */
    public static function compareStoreUrl($url1, $url2=null) {
        $url1Token = parse_url($url1);
        parse_str($url1Token['query'], $query1);

        if (null != $url2) {
            $url2Token = parse_url($url2);
            parse_str($url2Token['query'], $query2);
        } else {
            parse_str(ZMRequest::getQueryString(), $query2);
        }

        $equal = $query1['main_page'] == $query2['main_page'];
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
            if (isset($subArgs[$query1['main_page']])) {
                foreach ($subArgs[$query1['main_page']] as $sub) {
                    $equal = $query1[$sub] === $query2[$sub];
                    if (!$equal) {
                        return false;
                    }
                }
            }
        }

        return $equal;
    }

}

?>
