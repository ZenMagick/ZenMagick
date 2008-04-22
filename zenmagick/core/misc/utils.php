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
 *
 * $Id$
 */
?>
<?php

    /**
     * Parse RSS date.
     * 
     * @package org.zenmagick.misc
     * @param string date The date.
     * @return array An array with 3 elements in the order [day] [month] [year].
     * @deprecated use ZMToolbox instead
     */
    function zm_parse_rss_date($date) {
        return ZMToolbox::instance()->date->parseRssDate($date);
    } 
    /**
     * Convert date to RSS date format.
     * 
     * @package org.zenmagick.misc
     * @param mixed date The date string, timestamp (long) or <code>null</code> to use the current date.
     * @return string A date string formatted according to RSS date rules.
     * @deprecated use ZMToolbox instead
    */
    function zm_mk_rss_date($date=null) {
        return ZMToolbox::instance()->date->mkRssDate($date);
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
     * @deprecated use ZMToolbox instead
     */
    function zm_parse_date($date, $format) {
        return ZMToolbox::instance()->date->parseDate($date, $format);
    }
    /**
     * Convert a UI date into the internal data format.
     *
     * <p>This is typically used by controller/business code to convert user input before 
     * storing it in the database.</p>
     *
     * @package org.zenmagick.misc
     * @param string date The date as received via the UI.
     * @return string The formatted date.
     * @deprecated use ZMToolbox instead
     */
    function zm_ui2date($date) {
        return ZMToolbox::instance()->date->ui2date($date);
    }
    /**
     * Convert text based user input into HTML.
     *
     * @package org.zenmagick.misc
     * @param string s The input string.
     * @return string HTML formatted text.
     * @deprecated use ZMToolbox instead
     */
    function zm_text2html($s) {
        return ZMToolbox::instance()->html->text2html($s);
    }
    /**
     * Evaluate a string value as boolean.
     *
     * @package org.zenmagick.misc
     * @param mixed value The value.
     * @return boolean The boolean value.
     * @deprecated use ZMTools instead
     */
    function zm_boolean($value) {
        return ZMTools::asBoolean($value);
    }
    /**
     * Encode XML control characters.
     *
     * @package org.zenmagick.misc
     * @param string s The input string.
     * @return string The encoded string.
     * @deprecated use ZMToolbox instead
     */
    function zm_xml_encode($s) {
        return ZMToolbox::instance()->utils->encodeXML($s);
    }










    /**
     * Checks, if the current page is a checkout page.
     * 
     * @package org.zenmagick.misc
     * @param boolean includeCart If <code>true</code>, the shopping cart is considered a checkout page, too; (defaults to <code>true</code>)
     * @return boolean <code>true</code> if the current page is a checkout page.
     */
    function zm_is_checkout_page($includeCart=true) {
        $page = ZMRequest::getPageName();
        return ($includeCart && 'shopping_cart' == $page) || !(false === strpos($page, 'checkout_'));
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
    function zm_format_currency($amount, $convert=true, $echo=ZM_ECHO_DEFAULT) {
        $currency = ZMCurrencies::instance()->getCurrencyForCode(ZMRequest::getCurrencyCode());
        if (null == $currency) {
          ZMObject::log('no currency found - using default currency', ZM_LOG_WARN);
            $currency = ZMCurrencies::instance()->getCurrencyForCode(ZMSettings::get('defaultCurrency'));
        }
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
        $currency = ZMCurrencies::instance()->getCurrencyForCode(ZMRequest::getCurrencyCode());
        $amount = $currency->parse($money, false);

        return $amount;
    }

?>
