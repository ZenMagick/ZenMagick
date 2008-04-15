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
     * Create a id/name pair based select box.
     *
     * <p>Helper function that can create a HTML <code>&lt;select&gt;</code> tag from 
     * any array that contains class instances that provide <code>getId()</code> and
     * <code>getName()</code> getter methods.</p>
     *
     * @package org.zenmagick.html.defaults
     * @param string name The tag name.
     * @param array list A list of options.
     * @param int size Size of the select tag.
     * @param string selectedId Value of option to select.
     * @param string onchange Optional onchange handler.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string Complete HTML <code>&lt;select&gt;</code> tag.
     */
    function zm_idp_select($name, $list, $size=1, $selectedId=null, $onchange=null, $echo=ZM_ECHO_DEFAULT) {
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
     * Simple title generator based on the page name.
     *
     * @package org.zenmagick.html.defaults
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A reasonable page title.
     */
    function zm_title($echo=ZM_ECHO_DEFAULT) {
        $title = ZMRequest::getPageName();
        // special case for static pages
        $title = 'static' != $title ? $title : ZMRequest::getSubPageName();

        // format
        $title = str_replace('_', ' ', $title);
        // capitalise words
        $title = ucwords($title);
        $title = zm_l10n_get($title);

        if ($echo) echo $title;
        return $title;
    }

    /**
     * Format title based on the given page value.
     *
     * @package org.zenmagick.html.defaults
     * @param string page The page name.
     * @return string A reasonable page title.
     * @deprecated use zm_title instead
     */
    function zm_format_title($page=null) {
        $title = str_replace('_', ' ', $page);
        // capitalise words
        $title = ucwords($title);
        $title = zm_l10n_get($title);
        return $title;
    }

    /**
     * Get optional onload handler for the current page.
     *
     * <p>This is based on the <em>ZenMagick</em> theme architecture and not
     * compatible with <code>zen-card</code>.</p>
     *
     * @package org.zenmagick.html.defaults
     * @param string page The page name or <code>null<code> for the current page.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete onload attribute incl. value or an empty string.
     */
    function zm_onload($page=null, $echo=ZM_ECHO_DEFAULT) {
        $page = null == $page ? ZMRequest::getPageName() : $page;

        $onload = '';
        $themeInfo = ZMRuntime::getTheme()->getThemeInfo();
        if ($themeInfo->hasPageEventHandler('onload', $page)) {
            $onload = ' onload="' . $themeInfo->getPageEventHandler('onload', $page) . '"';
        }

        if ($echo) echo $onload;
        return $onload;
    }

    /**
     * Create a list of values separated by the given separator string.
     *
     * @package org.zenmagick.html.defaults
     * @param array list Array of values.
     * @param string sep Separator string; default: ', '.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A list of values.
     */
    function zm_list_values($list, $sep=', ', $echo=ZM_ECHO_DEFAULT) {
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
     * @package org.zenmagick.html.defaults
     * @param string name The common name.
     * @param array values List of values.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string HTML formatted input fields of type <em>hidden</em>.
     */
    function zm_hidden_list($name, $values, $echo=ZM_ECHO_DEFAULT) {
        $slash = ZMSettings::get('isXHTML') ? '/' : '';
        $html = '';
        foreach ($values as $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '"'.$slash.'>';
        }

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Show form field specific error messages.
     *
     * <p>The generated <code>ul</code> tag will have the value <em>[$name]Info</em> as id, and
     * a class of <em>fieldMsg</em>.
     * Each <code>li</code> will have the type as class assigned.</p>
     *
     * @package org.zenmagick.html.defaults
     * @param string name The field name.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string HTML unordered list of messages or <code>null</code>.
     * @deprecated use toolbox instead
     */
    function zm_field_messages($name, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->fieldMessages($name, $echo);
    }

?>
