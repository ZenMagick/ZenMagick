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
        return ZMToolbox::instance()->form->idpSelect($name, $list, $selectedId, array('size' => $size, 'onchange' => $onchange), $echo);
    }

    /**
     * Simple title generator based on the page name.
     *
     * @package org.zenmagick.html.defaults
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A reasonable page title.
     * @deprecated use toolbox instead
     */
    function zm_title($echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->utils->getTitle(null, $echo);
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
     * @deprecated use toolbox instead
     */
    function zm_onload($page=null, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->onload($page, $echo);
    }

    /**
     * Create  group of hidden form field with a common name (ie. <code>someId[]</code>).
     *
     * @package org.zenmagick.html.defaults
     * @param string name The common name.
     * @param array values List of values.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string HTML formatted input fields of type <em>hidden</em>.
     * @deprecated use toolbox instead
     */
    function zm_hidden_list($name, $values, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->form->hiddenList($name, $values, $echo);
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

    /**
     * Format a date in the short format.
     * @deprecated use toolbox instead
     */
    function zm_date_short($date, $echo=ZM_ECHO_DEFAULT) { return ZMToolbox::instance()->locale->shortDate($date, $echo); }

?>
