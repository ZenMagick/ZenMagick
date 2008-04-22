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
     * Creates a HTML <code>&lt;img&gt;</code> tag for the given <code>ZMImageInfo</code>.
     *
     * @package org.zenmagick.html.defaults
     * @param ZMImageInfo imageInfo The image info.
     * @param string format Can be either of <code>PRODUCT_IMAGE_SMALL</code>, <code>PRODUCT_IMAGE_MEDIUM</code> 
     *  or <code>PRODUCT_IMAGE_LARGE</code>; default is <code>>PRODUCT_IMAGE_SMALL</code>.
     * @param mixed parameter Additional parameter for the <code>&lt;mg&gt;</code> tag; can be either
     *  a query string style list of name/value pairs or a map.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formated HTML <code>&lt;img&gt;</code> tag.
     * @deprecated use the new toolbox instead!
     */
    function zm_image($imageInfo, $format=PRODUCT_IMAGE_SMALL, $parameter='', $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->image($imageInfo, $format, $parameter, $echo);
    }


    /**
     * Encode a given string to valid HTML.
     *
     * @package org.zenmagick.html
     * @param string s The string to decode.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The encoded HTML.
     * @deprecated use the new toolbox instead!
     */
    function zm_htmlencode($s, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->encode($s, $echo);
    }


    /**
     * Strip HTML tags from the given text.
     *
     * @package org.zenmagick.html
     * @param string text The text to clean up.
     * @param boolean echo If <code>true</code>, the stripped text will be echo'ed as well as returned.
     * @return string The stripped text.
     * @deprecated use the new toolbox instead!
     */
    function zm_strip_html($text, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->strip($text, $echo);
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
     * @deprecated use the new toolbox instead!
     */
    function zm_href_target($newWin=true, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->hrefTarget($newWin, $echo);
    }


    /**
     * Encode a URL to valid HTML.
     *
     * @package org.zenmagick.html
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     * @deprecated use the new toolbox instead!
     */
    function zm_htmlurlencode($url) {
        return ZMToolbox::instance()->net->encode($url);
    }


    /**
     * Decode a HTML encoded URL.
     *
     * @package org.zenmagick.html
     * @param string url The url to decode.
     * @return string The decoded URL.
     * @deprecated use the new toolbox instead!
     */
    function zm_htmlurldecode($s) {
        return ZMToolbox::instance()->net->decode($url);
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
     * @deprecated use the new toolbox instead!
     */
    function zm_more($s, $max=0, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->more($s, $max, '...', $echo);
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
     * @deprecated use the new toolbox instead!
     */
    function zm_build_more($s, $max=0, $more=" ...", $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->more($s, $max, $more, $echo);
    }


    /**
     * <code>phpinfo</code> wrapper.
     *
     * @package org.zenmagick.html
     * @param what What to display (see phpinfo manual for more)
     * @return boolean <code>true</code> on success.
     * @deprecated use the new toolbox instead!
     */
    function zm_phpinfo($what, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->macro->phpinfo($what, $echo);
    }

?>
