<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

/**
 * Security utils..
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.utils
 */
class ZMSecurityUtils {

    /**
     * Sanitize a given value.
     *
     * @param mixed value A string or array.
     * @return mixed A sanitized version.
     */
    public static function sanitize($value) {
        if (is_string($value)) {
            //$value = preg_replace('/ +/', ' ', $value);
            $value = preg_replace('/[<>]/', '_', $value);
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                $value = stripslashes($value);
            }
            return trim($value);
        } elseif (is_array($value)) {
            while (list($key, $val) = each($value)) {
                $value[$key] = self::sanitize($val);
            }
            return $value;
        }

        return $value;
    }

}
