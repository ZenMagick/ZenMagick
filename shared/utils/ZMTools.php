<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\apps\store\bundles\ZenCartBundle\mock\ZenCartCheckoutOrder;

/**
 * (System) Tools.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.utils
 */
class ZMTools {
    const RANDOM_DIGITS = 'digits';
    const RANDOM_CHARS = 'chars';
    const RANDOM_MIXED = 'mixed';
    const RANDOM_HEX = 'hex';

    private static $seedDone = false;
    private static $fileOwner = null;
    private static $fileGroup = null;

    // keep track of mock
    private static $mock = false;


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
     * Parse a money amount.
     *
     * @param string amount The amount.
     * @param string currencyCode The currency.
     * @return float The amount.
     */
    public static function parseMoney($money, $currencyCode) {
        $currency = $this->container->get('currencyService')->getCurrencyForCode($currencyCode);
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
            parse_str(str_replace('&amp;', '&', Runtime::getContainer()->get('request')->getQueryString()), $query2);
        }

        if (isset($url1Token) && null === $url2 && isset($url1Token['host']) && Runtime::getContainer()->get('request')->getHostname() != $url1Token['host']) {
            return false;
        }
        if (isset($url1Token) && isset($url2Token) && isset($url1Token['host']) && isset($url2Token['host']) && $url1Token['host'] != $url2Token['host']) {
            return false;
        }

        $idName = Runtime::getSettings()->get('zenmagick.http.request.idName');
        $query1[$idName] = (array_key_exists($idName, $query1) && !empty($query1[$idName])) ? $query1[$idName] : 'index';
        $query2[$idName] = (array_key_exists($idName, $query2) && !empty($query2[$idName])) ? $query2[$idName] : 'index';

        $equal = $query1[$idName] == $query2[$idName];
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
            if (isset($subArgs[$query1[$idName]])) {
                foreach ($subArgs[$query1[$idName]] as $sub) {
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
            self::$fileOwner = fileowner(__FILE__);
            self::$fileGroup = filegroup(__FILE__);
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
     * fmod variant that can handle values < 1.
     */
    public static function fmod_round($x, $y) {
        $x = strval($x);
        $y = strval($y);
        $zc_round = ($x*1000)/($y*1000);
        $zc_round_ceil = (int)($zc_round);
        $multiplier = $zc_round_ceil * $y;
        $results = abs(round($x - $multiplier, 6));
        return $results;
    }

}
