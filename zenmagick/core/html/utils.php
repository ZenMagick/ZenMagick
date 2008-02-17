<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
     * Creates a HTML <code>&lt;mg&gt;</code> tag for the given <code>ZMImageInfo</code>.
     *
     * @package org.zenmagick.html.defaults
     * @param ZMImageInfo imageInfo The image info.
     * @param string format Can be either of <code>PRODUCT_IMAGE_SMALL</code>, <code>PRODUCT_IMAGE_MEDIUM</code> 
     *  or <code>PRODUCT_IMAGE_LARGE</code>; default is <code>>PRODUCT_IMAGE_SMALL</code>.
     * @param mixed parameter Additional parameter for the <code>&lt;mg&gt;</code> tag; can be either
     *  a query string style list of name/value pairs or a map.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formated HTML <code>&lt;img&gt;</code> tag.
     */
    function zm_image($imageInfo, $format=PRODUCT_IMAGE_SMALL, $parameter=null, $echo=ZM_ECHO_DEFAULT) {
    global $zm_runtime;

        if (null === $imageInfo) {
            return;
        }

        $imageInfo->setParameter($parameter);
        switch ($format) {
        case PRODUCT_IMAGE_LARGE:
            $imgSrc = $imageInfo->getLargeImage();
            break;
        case PRODUCT_IMAGE_MEDIUM:
            $imgSrc = $imageInfo->getMediumImage();
            break;
            break;
        case PRODUCT_IMAGE_SMALL:
            $imgSrc = $imageInfo->getDefaultImage();
            break;
        }
        if (!zm_starts_with($imgSrc, '/')) {
            $imgSrc = $zm_runtime->getContext() . $imgSrc;
        }
        $html = '<img src="'.$imgSrc.'" alt="'.$imageInfo->getAltText().'" ';
        $html .= $imageInfo->getFormattedParameter();
        $html .= ' />';

        if ($echo) echo $html;
        return $html;
    }


    /**
     * Encode a given string to valid HTML.
     *
     * @package org.zenmagick.html
     * @param string s The string to decode.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The encoded HTML.
     */
    function zm_htmlencode($s, $echo=ZM_ECHO_DEFAULT) {
        $s = htmlspecialchars($s, ENT_QUOTES, zm_i18n('HTML_CHARSET'));

        if ($echo) echo $s;
        return $s;
    }


    /**
     * Strip HTML tags from the given text.
     *
     * @package org.zenmagick.html
     * @param string text The text to clean up.
     * @param boolean echo If <code>true</code>, the stripped text will be echo'ed as well as returned.
     * @return string The stripped text.
     */
    function zm_strip_html($text, $echo=ZM_ECHO_DEFAULT) {
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
     * @package org.zenmagick.html
     * @param boolean newWin If <code>true</code>, HTML for opening in a new window will be created.
     * @param boolean echo If <code>true</code>, the formatted text will be echo'ed as well as returned.
     * @return string A preformatted attribute in the form ' name="value"'
     */
    function zm_href_target($newWin=true, $echo=ZM_ECHO_DEFAULT) {
        $text = $newWin ? (zm_setting('isJSTarget') ? ' onclick="newWin(this); return false;"' : ' target="_blank"') : '';

        if ($echo) echo $text;
        return $text;
    }


    /**
     * Encode a URL to valid HTML.
     *
     * @package org.zenmagick.html
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     */
    function zm_htmlurlencode($url) {
        $url = htmlentities($url, ENT_QUOTES, zm_i18n('HTML_CHARSET'));
        $url = str_replace(' ', '%20', $url);
        return $url;
    }


    /**
     * Decode a HTML encoded URL.
     *
     * @package org.zenmagick.html
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
     * @package org.zenmagick.html
     * @param string s The text.
     * @param int max The number of allowed characters.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The (possibly) truncated text.
     */
    function zm_more($s, $max=0, $echo=ZM_ECHO_DEFAULT) {
        return zm_build_more($s, $max, '...', $echo);
    }


    /**
     * Truncate text.
     *
     * @package org.zenmagick.html
     * @param string s The text.
     * @param int max The number of allowed characters.
     * @param string more Optional string that will be appended to indicate that the text was truncated.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The (possibly) truncated text.
     */
    function zm_build_more($s, $max=0, $more=" ...", $echo=ZM_ECHO_DEFAULT) {
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


    /**
     * <code>phpinfo</code> wrapper.
     *
     * @package org.zenmagick.html
     * @param what What to display (see phpinfo manual for more)
     * @return boolean <code>true</code> on success.
     */
    function zm_phpinfo($what, $echo=ZM_ECHO_DEFAULT) {
        ob_start();                                                                                                       
        phpinfo($what);                                                                                                       
        $info = ob_get_contents();                                                                                       
        ob_end_clean();                                                                                                   
        $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);
        $info = str_replace('width="600"', '', $info);

        if ($echo) echo $info;
        return $info;
    }

?>
