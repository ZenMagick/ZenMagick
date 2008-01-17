<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
if (!defined('DATE_RSS')) { define('DATE_RSS', "D, d M Y H:i:s T"); }


    /**
     * Parse RSS date.
     * 
     * @package org.zenmagick.misc
     * @param string date The date.
     * @return array An array with 3 elements in the order [day] [month] [year].
    */
    function zm_parse_rss_date($date) {
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
    function zm_mk_rss_date($date=null) {
        if (null === $date) {
            return date(DATE_RSS);
        }

        if (is_string($date)) {
            $date = strtotime($date);
        }

        return date(DATE_RSS, $date);
    } 


    /**
     * Checks, if the current page is a checkout page.
     * 
     * @package org.zenmagick.misc
     * @param boolean includeCart If <code>true</code>, the shopping cart is considered a checkout page, too; (defaults to <code>true</code>)
     * @return boolean <code>true</code> if the current page is a checkout page.
     */
    function zm_is_checkout_page($includeCart=true) {
    global $zm_request;

        $page = $zm_request->getPageName();
        return ($includeCart && 'shopping_cart' == $page) || !(false === strpos($page, 'checkout_'));
    }


    /**
     * Parse a date according to a given format.
     *
     * <p>This function will honour <code>DD</code>, <code>MM</code>, <code>CC</code>, <code>YY</code>
     * and <code>YYYY</code> in the format.</p>
     *
     * <p><strong>NOTE:</strong> The format is *not* case sensitive.</p>
     *
     * @package org.zenmagick.misc
     * @param string date A date (usually provided by the user).
     * @param string format The date format
     * @param boolean reverse If <code>true</code>, the returned data will be reversed.
     * @return array The individual date components in the order dd, mm, cc, yy.
     */
    function zm_parse_date($date, $format) {
        $dd = '??';
        $mm = '??';
        $cc = '??';
        $yy = '??';

        $format = strtoupper($format);

        // parse
        $dpos = strpos($format, 'DD');
        if (false !== $dpos) {
            $dd = substr($date, $dpos, 2);
        }
        $mpos = strpos($format, 'MM');
        if (false !== $mpos) {
            $mm = substr($date, $mpos, 2);
        }
        $cpos = strpos($format, 'CC');
        if (false !== $cpos) {
            $cc = substr($date, $cpos, 2);
        }
        $cypos = strpos($format, 'YYYY');
        if (false !== $cypos) {
            $cc = substr($date, $cypos, 2);
            $yy = substr($date, $cypos+2, 2);
        } else {
            $ypos = strpos($format, 'YY');
            if (false !== $ypos) {
                $yy = substr($date, $ypos, 2);
            }
        }

        return array($dd, $mm, $cc, $yy);
    }


    /**
     * Encode XML control characters.
     *
     * @package org.zenmagick.misc
     * @param string s The input string.
     * @return string The encoded string.
     */
    function zm_xml_encode($s) {
        $encoding = array();
        $encoding['<'] = "&lt;";
        $encoding['>'] = "&gt;";
        $encoding['&'] = "&amp;";

        foreach ($encoding as $char => $entity) {
            $s = str_replace($char, $entity, $s);
        }

        return $s;
    }


    /**
     * Convert text based user input into HTML.
     *
     * @package org.zenmagick.misc
     * @param string s The input string.
     * @return string HTML formatted text.
     */
    function zm_text2html($s) {
        $html = str_replace("\n", '<br>', $s);
        $html = str_replace("\r\n", '<br>', $html);
        $html = str_replace("\r", '', $html);
        return $html;
    }


    /**
     * Helper for conditional get support.
     *
     * @package org.zenmagick.misc
     * @param string timestamp The last change date of whatever resource this is about.
     * @param boolean <code>true<code> if <strong>no</strong> body should be returned, 
     *  <code>false</code> if the resource changed.
     */
    function zm_eval_if_modified_since($timestamp) {
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
            false;
        $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
            stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : 
            false;
        if (!$if_modified_since && !$if_none_match) {
            return false;
        }
        // At least one of the headers is there - check them
        if ($if_none_match && $if_none_match != $etag) {
            return false; // etag is there but doesn't match
        }
        if ($if_modified_since && $if_modified_since != $last_modified) {
            return false; // if-modified-since is there but doesn't match
        }
        // Nothing has changed since their last request - serve a 304 and exit
        header('HTTP/1.0 304 Not Modified');
        return true;
    }


    /**
     * Evaluate a string value as boolean.
     *
     * @package org.zenmagick.misc
     * @param mixed value The value.
     * @return boolean The boolean value.
     */
    function zm_boolean($value) {
        if (is_integer($value)) {
            return $value;
        }
        return zm_is_in_array(strtolower($value), "on,true,yes,1");
    }


    /**
     * Extract the base product id from a given string.
     *
     * @package org.zenmagick.misc
     * @param string productId The full product id incl. attribute suffix.
     * @return int The product id.
     */
    function zm_base_product_id($productId) {
        $arr = explode(':', $productId);
        return (int) $arr[0];
    }


    /**
     * Reverse of <code>zm_base_product_id</code>.
     *
     * <p>Creates a unique id for the given product variation.</p>
     *
     * <p>Attributes are sorted using <code>krsort(..)</code> so to be compatible
     * for different attribute orders.</p>
     *
     * @package org.zenmagick.misc
     * @param string productId The full product id incl. attribute suffix.
     * @param array attrbutes Additional product attributes.
     * @return string The product id.
     * @todo currently uses <code>zen_get_uprid(..)</code>...
     */
    function zm_product_variation_id($productId, $attributes=array()) {
return zen_get_uprid($productId, $attributes);
        $fullProductId = $productId;

        if (is_array($attributes) && 0 < count($attributes) && !strstr($productId, ':')) {
            krsort($attributes);
            $s = $productId;
            foreach ($attributes as $id => $value) {
	              if (is_array($value)) {
                    foreach ($value as $vid => $vval) {
                        $s .= '{' . $id . '}' . trim($vid);
                    }
                } else {
                    $s .= '{' . $id . '}' . trim($value);
                }
            }
            $fullProductId .= ':' . md5($s);
        }

        return $fullProductId;
    }


    /**
     * Format the given amount according to the current currency.
     *
     * @package org.zenmagick.misc
     * @param float amount The amount.
     * @param boolean convert If <code>true</code>, consider <code>$amount</code> to be in default currency and
     *  convert before formatting.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The formatted amount.
     */
    function zm_format_currency($amount, $convert=true, $echo=true) {
    global $zm_request, $zm_currencies;

        $currency = $zm_currencies->getCurrencyForCode($zm_request->getCurrencyCode());
        $money = $currency->format($amount, $convert);

        if ($echo) echo $money;
        return $money;
    }


    /**
     * Parse a money amount.
     *
     * @package org.zenmagick.misc
     * @param string amount The amount probably formatted according to the sessions currency setting.
     * @return float The amount.
     */
    function zm_parse_money($money) {
    global $zm_request, $zm_currencies;

        $currency = $zm_currencies->getCurrencyForCode($zm_request->getCurrencyCode());
        $amount = $currency->parse($money, false);

        return $amount;
    }

?>
