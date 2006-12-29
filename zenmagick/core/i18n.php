<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

if (!function_exists('zen_date_raw')) {
    /**
     * Convert UI date into a <em>raw date format</em> that zen-cart
     * understands.
     *
     * <p>This generic implementation will work as long as <code>UI_DATE_FORMAT</code>
     * is defined.<br>
     * The function will honour <code>DD</code>, <code>MM</code>, <code>CC</code>, <code>YY</code>
     * and <code>YYYY</code> in the format.</p>
     *
     * <p><strong>NOTE:</strong> The format is *not* case sensitive.</p>
     *
     * @package net.radebatz.zenmagick
     * @param string date A date (usually provided by the user).
     * @param bool reverse If <code>true</code>, the returned data will be reversed.
     * @return string The provided date converted into the format <code>YYYYDDMM</code>
     *  or <code>MMDDYYYY</code>, respectivley.
     */
    function zen_date_raw($date, $reverse=false) {
        $dd = '??';
        $mm = '??';
        $cc = '??';
        $yy = '??';

        $format = strtoupper(UI_DATE_FORMAT);
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

        $raw = $reverse ? $mm.$dd.$cc.$yy : $cc.$yy.$mm.$dd;
        return $raw;
    }
}

?>
