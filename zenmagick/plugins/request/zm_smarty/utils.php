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
 * @version $Id$
 */
?>
<?php


    /**
     * Return the value or default of the map.
     *
     * @package org.zenmagick.plugins.zm_smarty
     * @param array arr The array.
     * @param string key The key.
     * @param mixed default The default value.
     */
    function _zms_ad($arr, $key, $default) {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }


    /**
     * Convert to define if set.
     *
     * <p>This will allow template editors to use define variables in some places.</p>
     *
     * @package org.zenmagick.plugins.zm_smarty
     * @param string value The value.
     * @param string The content of the define or the value.
     */
    function _zms_dv($value) {
        return defined($value) ? constant($value) : $value;
    }

?>
