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
     * Create a URL for a href.
     *
     * <p>If the <code>view</code> argument is <code>null</code>, the current view will be
     * used. The provided parameter will be merged into the current query string.</p>
     *
     * @package net.radebatz.zenmagick.html
     * @param string view The view name (ie. the page name as referred to by the parameter <code>main_page</code>)
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    function zm_href($view=null, $params='', $echo=true) { return _zm_build_href($view, $params, false, $echo); }

    /**
     * Secure version of {@link net.radebatz.zenmagick.html#zm_href zm_href}.
     *
     * @package net.radebatz.zenmagick.html
     * @param string view The view name (ie. the page name as referred to by the parameter <code>main_page</code>)
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full, secure URL.
     */
    function zm_secure_href($view=null, $params='', $echo=true) { return _zm_build_href($view, $params, true, $echo); }

    function _zm_build_href($view=null, $params='', $secure=false, $echo=true) {
    global $zm_request;
        if (null === $view || null === $params) {
            $query = array();
            if (null === $view || null === $params) {
                parse_str($zm_request->getQueryString(), $query);
                unset($query['main_page']);
            }
            if (null != $params) {
                parse_str($params, $arr);
                $query = array_merge($query, $arr);
            }
            $params = '';
            foreach ($query as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $params .= "&".$name."[]=".$subValue;
                    }
                } else {
                    $params .= "&".$name."=".$value;
                }
            }
        }

        // default to current view
        $view = $view == null ? $zm_request->getPageName() : $view;
        $href = zen_href_link($view, $params, $secure ? 'SSL' : 'NONSSL');

        if (zm_setting('isZMPrettyLinks')) {
            // adjust to match .htaccess rewrite rules
            $url = parse_url($href);
            $queryString = zm_htmlurldecode($url['query']);
            parse_str($queryString, $query);
            $path = dirname($url['path']);
            if (!zm_ends_with($path, '/')) {
                $path .= '/';
            }
            $path = dirname($url['path']).'/';
            if (zm_starts_with($path, '\\')) {
                $path = substr($path, 1);
            }
            $page = $query['main_page'];
            $translate = true;
            $removeNames = array('main_page', 'cPath', 'manufacturers_id', 'cat', 'products_id', 'order_id', 'reviews_id', 'id');
            switch ($page) {
                case 'index':
                case 'category':
                    if (array_key_exists("cPath", $query)) {
                        $path .= "category/".$query['cPath'];
                    } else if (array_key_exists("manufacturers_id", $query)) {
                        $path .= "manufacturer/".$query['manufacturers_id'];
                    } else {
                        $path .= "home";
                    }
                    break;
                case 'static':
                    $path .= "static/".$query['cat'];
                    break;
                case 'product_info':
                    if (array_key_exists("cPath", $query)) {
                        $path .= "product/".$query['products_id'];
                        $path .= "/".$query['cPath'];
                    } else if (array_key_exists("manufacturers_id", $query)) {
                        $path .= "manufacturer/".$query['products_id'];
                        $path .= "/".$query['manufacturers_id'];
                    } else {
                        $path .= "product/".$query['products_id'];
                    }
                    break;
                case 'login':
                case 'logoff':
                case 'account':
                case 'account_edit':
                case 'account_password':
                case 'account_newsletters':
                case 'account_notifications':
                case 'reviews':
                    $page = str_replace('_', '/', $page);
                    $path .= $page."/";
                    break;
                case 'account_history':
                    $path .= "account/history/";
                    break;
                case 'account_history_info':
                    $path .= "account/history/order/".$query['order_id'];
                    break;
                case 'address_book':
                    $path .= "addressbook/";
                    break;
                case 'address_book_process':
                    $path .= "addressbook/process/";
                    break;
                case 'product_reviews':
                    $path .= "reviews/".$query['products_id'];
                    break;
                case 'product_reviews_info':
                    $path .= "reviews/".$query['products_id']."/".$query['reviews_id'];
                    break;
                case 'product_reviews_write':
                    $path .= "reviews/new/".$query['products_id'];
                    break;
                case 'shopping_cart':
                    $path .= "cart/";
                    break;
                case 'account_notifications':
                    $path .= "account/notifications/";
                    if (array_key_exists('products_id', $query)) {
                        $path .= $query['products_id'];
                    }
                    break;
                case 'tell_a_friend':
                    $path .= "tellafriend/".$query['products_id'];
                    break;
                case 'page':
                    $path .= "page/".$query['id'];
                    if (array_key_exists('chapter', $query)) {
                        $path .= "/".$query['chapter'];
                        array_push($removeNames, 'chapter');
                    }
                    break;
                case 'site_map':
                    $path .= "sitemap/";
                    break;
                case 'specials':
                    $path .= "specials";
                    break;
                case 'privacy':
                    $path .= "privacy";
                    break;
                case 'contact_us':
                    $path .= "contactus/";
                    break;
                case 'products_new':
                    $path .= "newproducts/";
                    break;
                case 'password_forgotten':
                    $path .= "account/password/forgotten/";
                    break;
                case 'create_account':
                    $path .= "account/create/";
                    break;
                case 'create_account':
                    $path .= "account/create/";
                    break;
                case 'advanced_search':
                    $path .= "search/";
                    break;
                case 'advanced_search_result':
                    $path .= "search/results";
                    break;
                case 'featured_products':
                    $path .= "featured/";
                    break;
                case 'checkout_process':
                    $path .= "checkout/process/";
                    break;
                case 'checkout_success':
                    $path .= "checkout/success/";
                    break;
                case 'checkout_shipping':
                    $path .= "checkout/shipping/";
                    break;
                case 'checkout_shipping_address':
                    $path .= "checkout/shipping/address/";
                    break;
                case 'checkout_payment':
                    $path .= "checkout/payment/";
                    break;
                case 'checkout_payment_address':
                    $path .= "checkout/payment/address/";
                    break;
                case 'checkout_confirmation':
                    $path .= "checkout/confirm/";
                    break;
                case 'gv_send':
                    $path .= "account/giftcard/send/";
                    break;
                case 'gv_faq':
                    $path .= "account/giftcard/faq/";
                    break;
                case 'time_out':
                    $path .= "timeout/";
                    break;
                case 'redirect':
                    $path .= "redirect/".$query['action']."/".$query['goto'];
                    array_push($removeNames, 'goto');
                    array_push($removeNames, 'action');
                    break;
                default:
                    if (zm_starts_with($page, 'popup_')) {
                        $path .= "popup/".substr($page, 6);
                    } else {
                        $translate = false;
                        if (!zm_is_empty($page)) {
                            zm_log("no pretty link mapping for: ".$page);
                        }
                    }
                    break;
            }
            foreach ($removeNames as $rkey) {
                if (array_key_exists($rkey, $query)) {
                    unset($query[$rkey]);
                }
            }
            // remaining query
            $params = '';
            foreach ($query as $key => $value) {
                $params .= '&'.$key.'='.$value;
            }
            $params = (0 < strlen($params) ? ('?'.substr($params, 1)) : '');

            if ($translate) {
                $href = $url['scheme']."://".$url['host'].$path.(zm_is_empty($url['fragment']) ? '' : '#'.$url['fragment']).$params;
            }
        }

        if ($echo) echo $href;
        return $href;
    }


    /**
     * Convenience function.
     *
     * <p>Please note that in <em>ZenMagick</em> all product URLs use the same
     * view name. The actual view name gets resolved only when the href is used.</p>
     *
     * @package net.radebatz.zenmagick.html
     * @param int productId The product id
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     * @return string A complete product URL.
     */
    function zm_product_href($productId, $echo=true) { 
        return _zm_build_href(FILENAME_PRODUCT_INFO, '&products_id='.$productId, false, $echo);
    }

    /**
     * Convenience function.
     *
     * @package net.radebatz.zenmagick.html
     * @param string catName The static page name.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete URL for the given static page.
     */
    function zm_static_href($catName, $echo=true) { 
        return _zm_build_href('static', '&cat='.$catName, false, $echo);
    }

    /**
     * Back link.
     *
     * <p>In constrast to the <code>..._href</code> functions, this one will
     * return a full HTML <code>&lt;a&gt;</code> tag.</p>
     *
     * @package net.radebatz.zenmagick.html
     * @param string text The link text (can be plain text or HTML).
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formated HTML <code>&lt;a&gt;</code> tag.
     */
    function zm_back_link($text, $echo=true) {
        echo $foo;
        $link = zen_back_link() . $text . '</a>';

        if ($echo) echo $link;
        return $link;
    }

    /**
     * Build href for ez-page.
     *
     * @package net.radebatz.zenmagick.html
     * @param ZMEZPage page A <code>ZMEZPage</code> instance.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete URL for the given ez-page.
     */
    function zm_ezpage_href($page, $echo=true) {
        $params = '&id='.$page->getId();
        if (0 != $page->getTocChapter()) {
            $params .= '&chapter='.$page->getTocChapter();
        }
        $href = $page->isSSL() ? zm_secure_href(FILENAME_EZPAGES, $params, false) : zm_href(FILENAME_EZPAGES, $params, false);
        if (!zm_is_empty($page->getAltUrl())) {
            $url = parse_url($page->getAltUrl());
            parse_str($url['query'], $query);
            $view = $query['main_page'];
            unset($query['main_page']);
            $params = '';
            foreach ($query as $name => $value) {
                $params .= "&".$name."=".$value;
            }
            $href = $page->isSSL() ? zm_secure_href($view, $params, false) : zm_href($view, $params, false);
        } else if (!zm_is_empty($page->getAltUrlExternal())) {
            $href = $page->getAltUrlExternal();
        }

        if ($echo) echo $href;
        return $href;
    }


    /**
     * Create a full HTML &lt;a&gt; tag.
     *
     * @package net.radebatz.zenmagick.html
     * @param integer id The EZ page id.
     * @param string text Optional link text.
     * @param bool echo If <code>true</code>, the link will be echo'ed as well as returned.
     * @return string A full HTML link.
     */
    function zm_ezpage_link($id, $text=null, $echo=true) {
    global $zm_pages;
        $page = $zm_pages->getPageForId($id);
        $link = '<a href="' . zm_ezpage_href($page, false) . '"' . zm_href_target($page->isNewWin(), false) . '>' . (null == $text ? $page->getTitle() : $text) . ' </a>';

        if ($echo) echo $link;
        return $link;
    }


    /**
     * Create an image href with <code>DIR_WS_IMAGES</code> as base.
     *
     * @package net.radebatz.zenmagick.html
     * @param string src The relative source name.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    function zm_image_href($src, $echo=true) {
        $href = DIR_WS_CATALOG.DIR_WS_IMAGES . $src;

        if ($echo) echo $href;
        return $href;
    }


    /**
     * Create an redirect href for the given action and id.
     *
     * @package net.radebatz.zenmagick.html
     * @param string action The redirect action.
     * @param string id The redirect id.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    function zm_redirect_href($action, $id, $echo=true) {
        return _zm_build_href(FILENAME_REDIRECT, "action=".$action."&goto=".$id, false, $echo);
    }


    /**
     * Convert a given relative href/URL into a absolute one based on teh current context.
     *
     * @package net.radebatz.zenmagick.html
     * @param string href The URL to convert..
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The absolute href.
     */
    function zm_absolute_href($href, $echo=true) {
        $href = dirname(zm_href(null, null, false)) . "/" . $href;

        if ($echo) echo $href;
        return $href;
    }


    /**
     * Media href.
     *
     * @package net.radebatz.zenmagick.html
     * @param string filename The media filename.
     * @param bool echo If <code>true</code>, the formatted text will be echo'ed as well as returned.
     * @return A URL.
     */
    function zm_media_href($filename, $echo=true) {
        $href = DIR_WS_MEDIA.$filename;

        if ($echo) echo $href;
        return $href;
    }

    /**
     * Convenience function.
     *
     * <p><strong>NOTE:</strong> Ampersand are not encoded in this function.</p>
     *
     * @package net.radebatz.zenmagick.html
     * @param string controller The controller name without the leading <em>ajax_</em>.
     * @param string method The name of the method to call.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete Ajax URL.
     */
    function zm_ajax_href($controller, $method, $params='', $echo=true) { 
        $url = str_replace('&amp;', '&', _zm_build_href('ajax_'.$controller, $params.'&method='.$method, false, false));

        if ($echo) echo $url;
        return $url;
    }

?>
