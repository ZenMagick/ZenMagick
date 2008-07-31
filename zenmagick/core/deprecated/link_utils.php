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
     * Create a URL for a href.
     *
     * <p>If the <code>view</code> argument is <code>null</code>, the current view will be
     * used. The provided parameter will be merged into the current query string.</p>
     *
     * @package org.zenmagick.deprecated
     * @param string view The view name (ie. the page name as referred to by the parameter <code>main_page</code>)
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     * @deprecated use the new toolbox instead!
     */
    function zm_href($view=null, $params='', $echo=ZM_ECHO_DEFAULT) { 
        return ZMToolbox::instance()->net->url($view, $params, false, $echo);
    }


    /**
     * Secure version of {@link org.zenmagick.html#zm_href zm_href}.
     *
     * @package org.zenmagick.deprecated
     * @param string view The view name (ie. the page name as referred to by the parameter <code>main_page</code>)
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full, secure URL.
     * @deprecated use the new toolbox instead!
     */
    function zm_secure_href($view=null, $params='', $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->net->url($view, $params, true, $echo);
    }

    /**
     * Convenience function.
     *
     * <p>Please note that in <em>ZenMagick</em> all product URLs use the same
     * view name. The actual view name gets resolved only when the href is used.</p>
     *
     * @package org.zenmagick.deprecated
     * @param int productId The product id.
     * @param int categoryId Optional category id.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     * @return string A complete product URL.
     * @deprecated use the new toolbox instead!
     */
    function zm_product_href($productId, $categoryId=null, $echo=ZM_ECHO_DEFAULT) { 
        return ZMToolbox::instance()->net->product($productId, $categoryId, $echo);
    }

    /**
     * Convenience function.
     *
     * @package org.zenmagick.deprecated
     * @param string catName The static page name.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete URL for the given static page.
     * @deprecated use the new toolbox instead!
     */
    function zm_static_href($catName, $echo=ZM_ECHO_DEFAULT) { 
        return ZMToolbox::instance()->net->staticPage($catName, $echo);
    }

    /**
     * Back link.
     *
     * <p>In constrast to the <code>..._href</code> functions, this one will
     * return a full HTML <code>&lt;a&gt;</code> tag.</p>
     *
     * @package org.zenmagick.deprecated
     * @param string text The link text (can be plain text or HTML).
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formated HTML <code>&lt;a&gt;</code> tag.
     * @deprecated use the new toolbox instead!
     */
    function zm_back_link($text, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->backLink($text, array(), $echo);
    }

    /**
     * Build href for ez-page.
     *
     * @package org.zenmagick.deprecated
     * @param ZMEZPage page A <code>ZMEZPage</code> instance.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete URL for the given ez-page.
     * @deprecated use the new toolbox instead!
     */
    function zm_ezpage_href($page, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->net->ezpage($page, $echo);
    }


    /**
     * Create a full HTML &lt;a&gt; tag.
     *
     * @package org.zenmagick.deprecated
     * @param integer id The EZ page id.
     * @param string text Optional link text.
     * @param boolean echo If <code>true</code>, the link will be echo'ed as well as returned.
     * @return string A full HTML link.
     * @deprecated use the new toolbox instead!
     */
    function zm_ezpage_link($id, $text=null, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->html->ezpageLink($id, $text, array(), $echo);
    }


    /**
     * Create an absolute image path for the given image.
     *
     * @package org.zenmagick.deprecated
     * @param string src The relative image name (relative to zen-cart's image folder).
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The image URI.
     * @deprecated use the new toolbox instead!
     */
    function zm_image_uri($src, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->net->image($src, $echo);
    }


    /**
     * Create an redirect href for the given action and id.
     *
     * <p>All messages created up to this point during request handling will be saved and
     * restored with the next request handling cycle.</p>
     *
     * @package org.zenmagick.deprecated
     * @param string action The redirect action.
     * @param string id The redirect id.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     * @deprecated use the new toolbox instead!
     */
    function zm_redirect_href($action, $id, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->net->url($action, $id, false, $echo);
    }


    /**
     * Convert a given relative href/URL into a absolute one based on the current context.
     *
     * @package org.zenmagick.deprecated
     * @param string href The URL to convert..
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The absolute href.
     * @deprecated use the new toolbox instead!
     */
    function zm_absolute_href($href, $echo=ZM_ECHO_DEFAULT) {
        return ZMToolbox::instance()->net->absolute($href, $echo);
    }

    /**
     * Convenience function.
     *
     * <p><strong>NOTE:</strong> Ampersand are not encoded in this function.</p>
     *
     * @package org.zenmagick.deprecated
     * @param string controller The controller name without the leading <em>ajax_</em>.
     * @param string method The name of the method to call.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete Ajax URL.
     * @deprecated use the new toolbox instead!
     */
    function zm_ajax_href($controller, $method, $params='', $echo=ZM_ECHO_DEFAULT) { 
        return ZMToolbox::instance()->net->ajax($controller, $method, $params, $echo);
    }

    /**
     * Convenience function.
     *
     * @package org.zenmagick.deprecated
     * @param string channel The channel.
     * @param string key Optional key, for example, 'new' for the product channel.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete URL.
     * @deprecated use the new toolbox instead!
     */
    function zm_rss_feed_href($channel, $key=null, $echo=ZM_ECHO_DEFAULT) { 
        return ZMToolbox::instance()->net->rssFeed($channel, $key, $echo);
    }

?>
