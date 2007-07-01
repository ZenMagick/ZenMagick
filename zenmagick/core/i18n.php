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
     * @package net.radebatz.zenmagick.locale
     * @param string date A date (usually part of a form submit by the user).
     * @param bool reverse If <code>true</code>, the returned data will be reversed.
     * @return string The provided date converted into the format <code>YYYYDDMM</code> or <code>MMDDYYYY</code>, respectivley.
     */
    function zen_date_raw($date, $reverse=false) {
        $da = zm_parse_date($date, UI_DATE_FORMAT);
        $raw = $reverse ? $da[1].$da[0].$da[2].$da[3] : $da[2].$da[3].$da[1].$da[0];
        return $raw;
    }

}

    /**
     * Lookup and echo a i18n specific setting.
     *
     * @package net.radebatz.zenmagick.locale
     * @param string name The setting name.
     * @return string A value or null.
     */
    function zm_i18n($name) {
        $i18n = array();
        if (isset($GLOBALS['_zm_i18n_text'])) {
            $i18n = $GLOBALS['_zm_i18n_text'];
        }

        return isset($i18n[$name]) ? $i18n[$name] : null;
    }

    /**
     * Add i18n setting.
     *
     * @package net.radebatz.zenmagick.locale
     * @param mixed name The setting name or an array of settings.
     * @param string value The value (will be ignored if <code>name</code> is of type array).
     */
    function zm_i18n_add($name, $value=null) {
        // ensure we have an array to start with
        if (!isset($GLOBALS['_zm_i18n_text'])) {
            $GLOBALS['_zm_i18n_text'] = array();
        }

        if (is_array($name)) {
            $GLOBALS['_zm_i18n_text'] = array_merge($GLOBALS['_zm_i18n_text'], $name);
        } else {
            $GLOBALS['_zm_i18n_text'][$name] = $value;
        }
    }

    /**
     * Finalise i18n settings.
     *
     * <p>This function will convert all i18n settings into corresponding <code>defines</code> and
     * do all additional stuff that should be done just once.</p>
     *
     * @package net.radebatz.zenmagick.locale
     */
    function zm_i18n_finalise() {
        if (!isset($GLOBALS['_zm_i18n_text'])) {
            return;
        }
        foreach ($GLOBALS['_zm_i18n_text'] as $name => $value) {
            define($name, $value);
        }

        if (defined('LC_TIME_LOCALE')) {
            @setlocale(LC_TIME, LC_TIME_LOCALE);
        }
    }

?>
