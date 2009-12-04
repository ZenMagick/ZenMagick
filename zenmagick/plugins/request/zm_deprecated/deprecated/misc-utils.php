<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
     * @package org.zenmagick.deprecated
     * @param string date The date.
     * @return array An array with 3 elements in the order [day] [month] [year].
     * @deprecated use ZMTools instead
     */
    function zm_parse_rss_date($date) {
        return ZMTools::parseRssDate($date);
    } 
    /**
     * Convert date to RSS date format.
     * 
     * @package org.zenmagick.deprecated
     * @param mixed date The date string, timestamp (long) or <code>null</code> to use the current date.
     * @return string A date string formatted according to RSS date rules.
     * @deprecated use ZMTools instead
    */
    function zm_mk_rss_date($date=null) {
        return ZMTools::mkRssDate($date);
    } 
    /**
     * Parse a date according to a given format.
     *
     * <p>This function will honour <code>DD</code>, <code>MM</code>, <code>CC</code>, <code>YY</code>
     * and <code>YYYY</code> in the format.</p>
     *
     * <p><strong>NOTE:</strong> The format is *not* case sensitive.</p>
     *
     * @package org.zenmagick.deprecated
     * @param string date A date (usually provided by the user).
     * @param string format The date format
     * @param boolean reverse If <code>true</code>, the returned data will be reversed.
     * @return array The individual date components in the order dd, mm, cc, yy.
     * @deprecated use ZMTools instead
     */
    function zm_parse_date($date, $format) {
        $c = ZMTools::parseDateString($date, $format);
        return array($c['DD'], $c['MM'], $c['CC'], $c['YY']);
    }
    /**
     * Convert a UI date into the internal data format.
     *
     * <p>This is typically used by controller/business code to convert user input before 
     * storing it in the database.</p>
     *
     * @package org.zenmagick.deprecated
     * @param string date The date as received via the UI.
     * @return string The formatted date.
     * @deprecated use ZMTools instead
     */
    function zm_ui2date($date) {
        if (empty($date)) {
            return '';
        }
        // The individual date components in the order dd, mm, cc, yy.
        $da = self::parseDateString($date, UI_DATE_FORMAT);
        return date(ZM_DATETIME_FORMAT, mktime(0, 0, 0, $da['mm'], $da['dd'], (int)($da['cc'].$da['yy'])));
    }
    /**
     * Convert text based user input into HTML.
     *
     * @package org.zenmagick.deprecated
     * @param string s The input string.
     * @return string HTML formatted text.
     * @deprecated use ZMToolbox instead
     */
    function zm_text2html($s) {
        return ZMRequest::instance()->getToolbox()->html->text2html($s);
    }
    /**
     * Evaluate a string value as boolean.
     *
     * @package org.zenmagick.deprecated
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
     * @package org.zenmagick.deprecated
     * @param string s The input string.
     * @return string The encoded string.
     * @deprecated use ZMToolbox instead
     */
    function zm_xml_encode($s) {
        return ZMRequest::instance()->getToolbox()->utils->encodeXML($s);
    }
    /**
     * Format the given amount according to the current currency.
     *
     * @package org.zenmagick.deprecated
     * @param float amount The amount.
     * @param boolean convert If <code>true</code>, consider <code>$amount</code> to be in default currency and
     *  convert before formatting.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The formatted amount.
     * @deprecated use ZMToolbox instead
     */
    function zm_format_currency($amount, $convert=true, $echo=ZM_ECHO_DEFAULT) {
        return ZMRequest::instance()->getToolbox()->utils->formatMoney($amount, $convert, $echo);
    }
    /**
     * Parse a money amount.
     *
     * @package org.zenmagick.deprecated
     * @param string amount The amount probably formatted according to the sessions currency setting.
     * @return float The amount.
     * @deprecated use ZMTools instead
     */
    function zm_parse_money($money) {
        return ZMTools::parseMoney($money);
    }
    /**
     * Helper for conditional get support.
     *
     * @package org.zenmagick.deprecated
     * @param string timestamp The last change date of whatever resource this is about.
     * @param boolean <code>true<code> if <strong>no</strong> body should be returned, 
     *  <code>false</code> if the resource changed.
     * @deprecated use ZMTools instead
     */
    function zm_eval_if_modified_since($timestamp) {
        return !ZMTools::ifModifiedSince($timestamp);
    }
    /**
     * Checks, if the current page is a checkout page.
     * 
     * @package org.zenmagick.deprecated
     * @param boolean includeCart If <code>true</code>, the shopping cart is considered a checkout page, too; (defaults to <code>true</code>)
     * @return boolean <code>true</code> if the current page is a checkout page.
     * @deprecated use ZMRequest instead
     */
    function zm_is_checkout_page($includeCart=true) {
        return ZMRequest::instance()->isCheckout($includeCart);
    }

?>
