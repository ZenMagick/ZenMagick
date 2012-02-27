<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2010 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Networking utils.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.utils
 */
class ZMNetUtils {

    /**
     * helper to try to sort out headers for people who aren't running apache,
     * or people who are running PHP as FastCGI.
     *
     * @return array of request headers as associative array.
     */
    public static function getAllHeaders() {
        $retarr = array();
        $raw = array();

        if (function_exists('getallheaders')) {
            $raw = getallheaders();
        } else {
            $raw = array();
            foreach (array_merge($_ENV, $_SERVER) as $key => $value) {
                //we need this header
                if (false !== strpos(strtolower($key), 'content-type')) {
                    continue;
                }
                if ('HTTP_' == strtoupper(substr($key, 0, 5)) || false !== strpos(strtolower($key), 'content-type')) {
                    $key = preg_replace('/^HTTP_/i', '', $key);
                    $key = str_replace(' ', '-', strtolower(str_replace(array('-', '_'), ' ', $key)));
                    $raw[$key] = $value;
                }
            }
        }

        $headers = array();
        foreach ($raw as $key => $value) {
            // normalize keys
            $key = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $key))));
            $headers[$key] = $value;
        }

        ksort($headers);

        return $headers;
    }

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

    /**
     * Set the response content type.
     *
     * @param string type The content type.
     * @param string charset Optional charset; default is utf-8; <code>null</code> will omit the charset part.
     */
    public static function setContentType($type, $charset="utf-8") {
        $text = "Content-Type: " . $type;
        if (null != $charset) {
            $text .= "; charset=" . $charset;
        }
        header($text);
    }

}
