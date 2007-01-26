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


    /**
     * Create a HTML <code>&lt;a&gt;</code> tag with a small product image for the given product.
     *
     * <p>In constrast to the <code>..._href</code> functions, this one will
     * return a full HTML <code>&lt;img&gt;</code> tag.</p>
     *
     * @package net.radebatz.zenmagick.html
     * @param ZMProduct product A product.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formated HTML <code>&lt;a&gt;</code> tag.
     */
    function zm_product_image($product, $echo=true) {
        $img = zen_image(DIR_WS_IMAGES . $product->getDefaultImage(), $product->getName(), 
            '', '', 'class="product"');

        if ($echo) echo $img;
        return $img;
    }


    /**
     * Encode a given string to valid HTML.
     *
     * @package net.radebatz.zenmagick.html
     * @param string s The string to decode.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The encoded HTML.
     */
    function zm_htmlencode($s, $echo=true) {
        $s = htmlentities($s);

        if ($echo) echo $s;
        return $s;
    }


    /**
     * Strip HTML tags from the given text.
     *
     * @package net.radebatz.zenmagick.html
     * @param string text The text to clean up.
     * @param bool echo If <code>true</code>, the stripped text will be echo'ed as well as returned.
     * @return string The stripped text.
     */
    function zm_strip_html($text, $echo=true) {
        $clean = zen_clean_html($text);

        if ($echo) echo $clean;
        return $clean;
    }


    /**
     * Create a HTML <code>target</code> or <code>onclick</code> attribute for a link.
     *
     * <p>Please note that ther is already a snigle whitespace in front of the attribute name.</p>
     *
     * <p>Behaviour is controlled with the <em>ZenMagick</em> setting <code>isJSTarget</code>.</p>
     *
     * @package net.radebatz.zenmagick.html
     * @param bool newWin If <code>true</code>, HTML for opening in a new window will be created.
     * @param bool echo If <code>true</code>, the formatted text will be echo'ed as well as returned.
     * @return string A preformatted attribute in the form ' name="value"'
     */
    function zm_href_target($newWin=true, $echo=true) {
        $text = $newWin ? (zm_setting('isJSTarget') ? ' onclick="newWin(this); return false;"' : ' target="_blank"') : '';

        if ($echo) echo $text;
        return $text;
    }


    /**
     * Encode a URL to valid HTML.
     *
     * @package net.radebatz.zenmagick.html
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     */
    function zm_htmlurlencode($url) {
        $url = htmlentities($url);
        $url = str_replace(' ', '%20', $url);
        return $url;
    }


    /**
     * Decode a HTML encoded URL.
     *
     * @package net.radebatz.zenmagick.html
     * @param string url The url to decode.
     * @return string The decoded URL.
     */
    function zm_htmlurldecode($s) {
        $s = html_entity_decode($s);
        $s = str_replace('%20', ' ', $s);
        return $s;
    }


    /**
     * Truncate text with trailing '...'
     *
     * <p>Convenience function for <code>zm_build_more</code>.</p>
     *
     * @package net.radebatz.zenmagick.html
     * @param string s The text.
     * @param int max The number of allowed characters.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The (possibly) truncated text.
     */
    function zm_more($s, $max=0, $echo=true) {
        return zm_build_more($s, $max, '...', $echo);
    }


    /**
     * Truncate text.
     *
     * @package net.radebatz.zenmagick.html
     * @param string s The text.
     * @param int max The number of allowed characters.
     * @param string more Optional string that will be appended to indicate that the text was truncated.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The (possibly) truncated text.
     */
    function zm_build_more($s, $max=0, $more=" ...", $echo=true) {
        $text = $s;
        if (0 != $max && strlen($text) > $max) {
            $pos = strpos($text, ' ', $max-10);
            if (!($pos === false)) {
                $text = substr($text, 0, $pos+1);
            }
            $text .= $more;
        }

        if ($echo) echo $text;
        return $text;
    }

?>
