<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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

/**
 * XML utils.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.utils
 */
class ZMXmlUtils {

    /**
     * Encode XML control characters.
     *
     * @param string s The input string.
     * @return string The encoded string.
     */
    public static function encodeXML($s) {
        $encoding = array(
            '<' => '&lt;',
            '>' => '&gt;',
            '&' => '&amp;'
        );

        foreach ($encoding as $char => $entity) {
            $s = str_replace($char, $entity, $s);
        }

        return $s;
    }

    /**
     * Encode XML attribute characters.
     *
     * @param string s The input string.
     * @return string The encoded string.
     */
    public static function encodeAttribute($s) {
        $encoding = array(
            '"' => '&#34;',
            "'" => '&#39;'
        );

        foreach ($encoding as $char => $entity) {
            $s = str_replace($char, $entity, $s);
        }

        return $s;
    }

}
