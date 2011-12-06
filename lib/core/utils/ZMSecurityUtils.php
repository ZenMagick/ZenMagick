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
    /** Random type digits only. */
    const RANDOM_DIGITS = 'digits';
    /** Random type characters only. */
    const RANDOM_CHARS = 'chars';
    /** Random type mixed (digits and characters). */
    const RANDOM_MIXED = 'mixed';
    /** Random type hexadecimal. */
    const RANDOM_HEX = 'hex';

    private static $seedDone_;


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

    /**
     * Generate a random value.
     *
     * @param int length The length of the random value.
     * @param string type Optional type; predefined values are: <em>mixed</em>, <em>chars</em>, <em>digits</em> or <em>hex</em>; default is <em>mixed</em>.
     *  Any other value will be used as the valid character range.
     * @return string The random string.
     */
    public static function random($length, $type='mixed') {
        static $types	=	array(
        self::RANDOM_DIGITS => '0123456789',
        self::RANDOM_CHARS => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::RANDOM_MIXED => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        self::RANDOM_HEX => '0123456789abcdef',
        );

        if (!self::$seedDone_) {
            mt_srand((double)microtime() * 1000200);
            self::$seedDone_ = true;
        }

        $chars = array_key_exists($type, $types) ? $types[$type] : $type;
        $max=	strlen($chars) - 1;
        $token = '';
        for ($ii=0; $ii < $length; ++$ii) {
            $token .=	$chars[(rand(0, $max))];
        }

        return $token;
    }

}
