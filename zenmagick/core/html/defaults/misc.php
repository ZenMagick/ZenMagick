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
     * Encode a URL to valid HTML.
     *
     * @package net.radebatz.zenmagick.html.defaults
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
     * @package net.radebatz.zenmagick.html.defaults
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
     * @package net.radebatz.zenmagick.html.defaults
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
     * @package net.radebatz.zenmagick.html.defaults
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

    /**
     * Create a id/name pair based select box.
     *
     * <p>Helper function that can create a HTML <code>&lt;select&gt;</code> tag from 
     * any array that contains class instances that provide <code>getid()</code> and
     * <code>getName()</code> getter methods.</p>
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param string name The tag name.
     * @param array list A list of options.
     * @param int size Size of the select tag.
     * @param string selectedId Value of option to select.
     * @param string onchange Optional onchange handler.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string Complete HTML <code>&lt;select&gt;</code> tag.
     */
    function zm_idp_select($name, $list, $size=1, $selectedId=null, $onchange=null, $echo=true) {
        $html = '';
        $html .= '<select id="' . $name . '" name="' . $name . '" size="' . $size . '"';
        $html .= (null != $onchange ? ' onchange="' . $onchange . '"' : '');
        $html .= '>';
        foreach ($list as $item) {
            $selected = $item->getId() == $selectedId;
            $html .= '<option value="' . $item->getId() . '"';
            $html .= ($selected ? ' selected="selected"' : '');
            $html .= '>' . $item->getName() . '</option>';
        }
        $html .= '</select>';

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Simple title genrator based on the page name.
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A reasonable page title.
     */
    function zm_title($echo=true) {
    global $zm_request;

        $title = $zm_request->getPageName();
        $title = 'static' != $title ? $title : $zm_request->getSubPageName();
        $title = str_replace('_', ' ', $title);
        // capitalise words
        $title = ucwords($title);
        $title = zm_l10n_get($title);

        if ($echo) echo $title;
        return $title;
    }

    /**
     * Get optional onload handler for the current page.
     *
     * <p>This is based on the <em>ZenMagick</em> theme architecture and not
     * compatible with <code>zen-card</code>.</p>
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete onload attribute incl. value or an empty string.
     */
    function zm_onload($page=null, $echo=true) {
    global $zm_request, $zm_themeInfo;
        $page = null == $page ? $zm_request->getPageName() : $page;

        $onload = '';
        if ($zm_themeInfo->hasPageEventHandler('onload', $page)) {
            $onload = ' onload="' . $zm_themeInfo->getPageEventHandler('onload', $page) . '"';
        }

        if ($echo) echo $onload;
        return $onload;
    }

    /**
     * Escape single and double quotes.
     *
     * <p>Useful when creating dynamic JavaScript based on database content that might
     * include single quotes; e.f. ZenMagick<strong>'</strong>s bla bla...</p>
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param string text The text to escape.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The escaped text.
     */
    function zm_quote($text, $echo=true) {
        if (get_magic_quotes_gpc()) {
            $text = addslashes($text);
        }

        if ($echo) echo $text;
        return $text;
    }

    /**
     * Create a list of values separated by the given separator string.
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param array list Array of values.
     * @param string sep Separator string; default: ', '.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A list of values.
     */
    function zm_list_values($list, $sep=', ', $echo=true) {
        $first = true;
        $html = '';
        foreach ($list as $value) {
            if (!$first) $html .= $sep;
            $first = false;
            $html .= $value;
        }

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Create  group of hidden form field with a common name (ie. <code>someId[]</code>).
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param string name The common name.
     * @param array values List of values.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string HTML formatted input fields of type <em>hidden</em>.
     */
    function zm_hidden_list($name, $values, $echo=true) {
        $html = '';
        foreach ($values as $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }

        if ($echo) echo $html;
        return $html;
    }

?>
