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

use zenmagick\base\Runtime;

/**
 * Networking utils.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.utils
 */
class ZMNetUtils {

    /**
     * Encode a given URL to valid HTML.
     *
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     */
    public static function encode($url) {
        $url = htmlentities($url, ENT_QUOTES, Runtime::getSettings()->get('zenmagick.http.html.charset'));
        $url = str_replace(' ', '%20', $url);
        return $url;
    }

    /**
     * Decode a HTML encoded URL.
     *
     * @param string url The url to decode.
     * @return string The decoded URL.
     */
    public static function decode($url) {
        $s = html_entity_decode($url, ENT_QUOTES, Runtime::getSettings()->get('zenmagick.http.html.charset'));
        $s = str_replace('%20', ' ', $s);
        return $s;
    }

    /**
     * Get the top level domain from a given url.
     *
     * @param string url The url
     * @return string The top level domain.
     * @see http://stackoverflow.com/questions/399250/going-where-php-parse-url-doesnt-parsing-only-the-domain
     */
    public static function getDomain($url) {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        return $domain;
    }
}
