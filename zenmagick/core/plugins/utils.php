<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
     * Create a plugin admin page URL.
     *
     * @package net.radebatz.zenmagick.plugins
     * @param string function The view function name.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    function zm_plugin_admin_url($function, $params='', $echo=true) {
        $url = zen_href_link('zmPluginPage', 'fkt='.$function.'&'.$params, 'SSL');

        if ($echo) echo $url;
        return $url;
    }

    /**
     * Create a plugin view/admin page URL.
     *
     * <p>This function can be used in places where code is executed in both storefront <strong>and</strong>
     * admin context.</p>
     *
     * <p>In contrast to <code>zm_plugin_admin_url</code>, this function will accept either a view name,
     * a function name or both as <em>target</em>.</p>
     *
     * <p>Format for <em>target</em> is as follows:</p>
     * <dl>
     *   <dt>View only</dt><dd>Same as for <code>zm_href</code>.</dd>
     *   <dt>Admin only</dt><dd>Same as for <code>zm_plugin_admin_url</code> except that the function name is preceeded by <em>;</em>.</dd>
     *   <dt>View and function</dt><dd>Viewname and function separated by <em>;</em>; example: <code>wiki;zm_wiki_admin</code>.</dd>
     * </dl>
     *
     * @package net.radebatz.zenmagick.plugins
     * @param string target The target.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    function zm_plugin_url($target, $params='', $echo=true) {
    global $zm_request;

        $target = explode(';', $target);
        if ($zm_request->isAdmin()) {
            return zm_plugin_admin_url($target[1], $params, $echo);
        } else {
            return zm_href($target[0], $params, $echo);
        }
    }

?>
