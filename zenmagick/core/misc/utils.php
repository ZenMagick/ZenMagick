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
if (!defined('DATE_RSS')) { define(DATE_RSS, "D, d M Y H:i:s T"); }


    /**
     * Parse RSS date.
     * 
     * @package net.radebatz.zenmagick.misc
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
     * @package net.radebatz.zenmagick.misc
     * @param string date The date or <code>null</code>.
     * @return string A date string formatted according to RSS date rules.
    */
    function zm_mk_rss_date($date=null) {
        if (null === $date) {
            return date(DATE_RSS);
        }

        $date = strtotime($date);
        return date(DATE_RSS, $date);
    } 


    /**
     * Checks, if the current page is a checkout page.
     * 
     * @package net.radebatz.zenmagick.misc
     * @param bool includeCart If <code>true</code>, the shopping cart is considered a checkout page, too; (defaults to <code>true</code>)
     * @return bool <code>true</code> if the current page is a checkout page.
     */
    function zm_is_checkout_page($includeCart=true) {
    global $zm_request;

        $page = $zm_request->getPageName();
        return ($includeCard && 'shopping_cart' == $page) || !(false === strpos($page, 'checkout_'));
    }


    /**
     * Parse a date according to a given format.
     *
     * <p>This function will honour <code>DD</code>, <code>MM</code>, <code>CC</code>, <code>YY</code>
     * and <code>YYYY</code> in the format.</p>
     *
     * <p><strong>NOTE:</strong> The format is *not* case sensitive.</p>
     *
     * @package net.radebatz.zenmagick.misc
     * @param string date A date (usually provided by the user).
     * @param string format The date format
     * @param bool reverse If <code>true</code>, the returned data will be reversed.
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
     * @package net.radebatz.zenmagick.misc
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
     * @param string s The input string.
     * @return string HTML formatted text.
     */
    function zm_text2html($s) {
        $html = str_replace("\n", '<br>', $s);
        $html = str_replace("\r\n", '<br>', $html);
        $html = str_replace("\r", '', $html);
        return $html;
    }

?>
