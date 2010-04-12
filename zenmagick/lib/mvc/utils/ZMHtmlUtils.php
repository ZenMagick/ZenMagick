<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * HTML utilities.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.utils
 * @version $Id$
 */
class ZMHtmlUtils {

    /**
     * Encode a given string to valid HTML.
     *
     * @param string s The string to encode.
     * @return string The encoded HTML.
     */
    public static function encode($s) {
        $s = html_entity_decode($s, ENT_QUOTES, ZMSettings::get('zenmagick.mvc.html.charset')); 
        $s = htmlentities($s, ENT_QUOTES, ZMSettings::get('zenmagick.mvc.html.charset'));

        return $s;
    }

    /**
     * Convert text based user input into HTML.
     *
     * @param string s The input string.
     * @return string HTML formatted text.
     */
    public static function text2html($s) {
        $html = str_replace("\r\n", '<br>', $html);
        $html = str_replace("\n", '<br>', $s);
        $html = str_replace("\r", '', $html);
        return $html;
    }

    /**
     * Truncate text.
     *
     * @param string s The text.
     * @param int max The number of allowed characters; default is <em>0</em> for all.
     * @param string more Optional string that will be appended to indicate that the text was truncated; default is <em>...</em>.
     * @return string The (possibly) truncated text.
     */
    public static function more($s, $max=0, $more=" ...") {
        $text = $s;
        if (0 != $max && strlen($text) > $max) {
            $pos = strpos($text, ' ', $max-10);
            if (!($pos === false)) {
                $text = substr($text, 0, $pos+1);
            }
            $text .= $more;
        }

        return $text;
    }

}
