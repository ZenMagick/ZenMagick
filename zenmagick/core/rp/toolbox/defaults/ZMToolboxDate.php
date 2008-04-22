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
 * Date methods.
 *
 * @author mano
 * @package org.zenmagick.rp.toolbox.defaults
 * @version $Id$
 */
class ZMToolboxDate extends ZMObject {

    /**
     * Parse RSS date.
     * 
     * @param string date The date.
     * @return array An array with 3 elements in the order [day] [month] [year].
     */
    public function parseRssDate($date) {
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
    public function mkRssDate($date=null) {
        if (null === $date) {
            return date(DATE_RSS);
        }

        if (is_string($date)) {
            $date = strtotime($date);
        }

        return date(DATE_RSS, $date);
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
     * @param boolean reverse If <code>true</code>, the returned data will be reversed.
     * @return array The individual date components in the order dd, mm, cc, yy.
     */
    public function parseDate($date, $format) {
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
     * Convert a UI date into the internal data format.
     *
     * <p>This is typically used by controller/business code to convert user input before 
     * storing it in the database.</p>
     *
     * @param string date The date as received via the UI.
     * @return string The formatted date.
     */
    public function ui2date($date) {
        if (empty($date)) {
            return '';
        }
        // The individual date components in the order dd, mm, cc, yy.
        $da = $this->parseDate($date, UI_DATE_FORMAT);
        return date('Y-m-d 00:00:00', mktime(0, 0, 0, $da[1], $da[0], (int)($da[2].$da[3])));
    }

    /**
     * Format and display a date using the configured short format (<em>DATE_SHORT</em>).
     *
     * @param string date The date.
     * @param boolean echo If <code>true</code>, the date will be echo'ed as well as returned.
     * @return string The formatted date.
     */
    public function shortDate($date, $echo=ZM_ECHO_DEFAULT) { 
        $ds = zen_date_short($date); 
        if($echo) echo $ds;
        return $ds;
    }

}

?>
