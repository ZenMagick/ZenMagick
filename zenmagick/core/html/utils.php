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
                $params .= "&".$name."=".$value;
            }
        }

        // default to current view
        $view = $view == null ? $zm_request->getPageName() : $view;
        $href = zen_href_link($view, $params, $secure ? 'SSL' : 'NONSSL');

        if (zm_setting('isZMPermalinks')) {
            // adjust to match .htaccess rewrite rules
            $url = parse_url($href);
            $queryString = zm_htmlurldecode($url['query']);
            parse_str($queryString, $query);
            $path = dirname($url['path']).'/';
            $page = $query['main_page'];
            $translate = true;
            switch ($page) {
                case 'index':
                case 'category':
                    if (array_key_exists("cPath", $query)) {
                        $path .= "category/".$query['cPath'];
                    } else if (array_key_exists("manufacturers_id", $query)) {
                        $path .= "manufacturer/".$query['manufacturers_id'];
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
                case 'logout':
                case 'account':
                case 'account_edit':
                case 'account_password':
                case 'account_newsletters':
                case 'account_notifications':
                case 'reviews':
                    $page = str_replace('_', '/', $page);
                    $path .= $page."/";
                    break;
                case 'account_history_info':
                    $path .= "orderhistory/".$query['order_id'];
                    break;
                case 'address_book':
                    $path .= "addressbook/".$query['order_id'];
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
                default:
                    $translate = false;
                    break;
            }
            // always append at end
            if (array_key_exists("action", $query)) {
                $path .= "/".$query['action'];
            }
            if (array_key_exists("page", $query)) {
                $path .= "/".$query['page'];
            }
            if (array_key_exists("filter_id", $query)) {
                $path .= "/".$query['filter_id'];
            }
            if (array_key_exists("sort", $query)) {
                $path .= "/".$query['sort'];
            }

            if ($translate) {
                $href = $url['scheme']."://".$url['host'].$path.$url['fragment'];
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
     * Create a HTML <code>&lt;a&gt;</code> tag with a small product image for the given product.
     *
     * <p>In constrast to the <code>..._href</code> functions, this one will
     * return a full HTML <code>&lt;a&gt;</code> tag.</p>
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
        if (zm_not_null($page->getAltUrl())) {
            $url = parse_url($page->getAltUrl());
            parse_str($url['query'], $query);
            $view = $query['main_page'];
            unset($query['main_page']);
            $params = '';
            foreach ($query as $name => $value) {
                $params .= "&".$name."=".$value;
            }
            $href = $page->isSSL() ? zm_secure_href($view, $params, false) : zm_href($view, $params, false);
        } else if (zm_not_null($page->getAltUrlExternal())) {
            $href = $page->getAltUrlExternal();
        }

        if ($echo) echo $href;
        return $href;
    }


    /**
     * Checks, if the current page is a checkout page.
     * 
     * <p><strong>NOTE:</strong> The shopping cart is <strong>also</strong> considered a checkoput page.</p>
     *
     * @package net.radebatz.zenmagick.html
     * @return <code>true</code> if the current page is either a checkout page or the shopping cart.
     */
    function zm_is_checkout_page() {
    global $zm_request;
        $page = $zm_request->getPageName();
        return 'shopping_cart' == $page || !(false === strpos($page, 'checkout_'));
    }


    /***************** form utilities. ****************/

    /**
     * Create a HTML <code>form</code> tag.
     *
     * <p>The passed parameters will be added to the action URL as well as
     * hidden form fields.
     * The reason for adding to the action is the weiryd way zen-card is looking at
     * <code>$_GET</code> and <code>$_POST</code>.</p>
     * 
     * @package net.radebatz.zenmagick.html
     * @param string page The action page name.
     * @param string params Query string style parameter.
     * @param string id Optional HTML id; defaults to <code>null</code>
     * @param string method Should be either <code>get</code> or <code>post</code>. Defaults to <code>get</code>.
     * @param string onsubmit Optional submit handler for form validation; defaults to <code>null</code>
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form tag plus optional hidden form fields.
     */
    function zm_form($page, $params='', $id=null, $method='post', $onsubmit=null, $echo=true) {
        return _zm_build_form($page, $params, $id, $method, false, $onsubmit, $echo);
    }

    /**
     * Secure version of <code>zm_form</code> to create a HTML <code>form</code> tag.
     *
     * <p>The passed parameters will be added to the action URL as well as
     * hidden form fields.
     * The reason for adding to the action is the weiryd way zen-card is looking at
     * <code>$_GET</code> and <code>$_POST</code>.</p>
     * 
     * @package net.radebatz.zenmagick.html
     * @param string page The action page name.
     * @param string params Query string style parameter.
     * @param string id Optional HTML id; defaults to <code>null</code>
     * @param string method Should be either <code>get</code> or <code>post</code>. Defaults to <code>get</code>.
     * @param string onsubmit Optional submit handler for form validation; defaults to <code>null</code>
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form tag plus optional hidden form fields.
     */
    function zm_secure_form($page, $params='', $id=null, $method='post', $onsubmit=null, $echo=true) {
        return _zm_build_form($page, $params, $id, $method, true, $onsubmit, $echo);
    }

    function _zm_build_form($page, $params='', $id=null, $method='post', $secure=false, $onsubmit=null, $echo=true) {
        $html = '';
        if (zm_starts_with($page, "http")) {
            $action = $page;
        } else {
            $action = $secure ? zm_secure_href($page, $params, false) : zm_href($page, $params, false);
        }
        $html .= '<form action="' . $action . '"';
        if (null != $onsubmit) {
            $html .= ' onsubmit="' . $onsubmit . '"';
        }
        if (null != $id) {
            $html .= ' id="' . $id . '"';
        }
        $html .= ' method="' . $method . '">';
        $html .= '<div>';
        $html .= '<input type="hidden" name="main_page" value="' . $page . '" />';
        parse_str($params, $query);
        foreach ($query as $name => $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }
        $html .= '</div>';

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Convenience function to open a form to add a given product to the shopping cart.
     *
     * <p>The calling page is responsible for adding a submit buttona and a closing <code>&lt;form&gt;</code>
     * tag.</p>
     * 
     * @package net.radebatz.zenmagick.html
     * @param int productId The product (id) to add.
     * @param int quantity Optional quantity; default to 0 which means that the card_quantity field will <strong>not</strong> be added
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form to add a given productId to the shopping cart.
     */
    function zm_add_product_form($productId, $quantity=0, $echo=true) {
    global $zm_request;
        $html = '';
        $params = 'action=add_product&products_id='.$productId;
        $html .= '<form action="' . zm_secure_href(zen_get_info_page($productId), $params, false) . '"';
        $html .= ' method="post" enctype="multipart/form-data">';
        $html .= '<div>';
        $html .= '<input type="hidden" name="action" value="add_product" />';
        $html .= '<input type="hidden" name="products_id" value="'.$productId.'" />';
        if (0 < $quantity) {
            $html .= '<input type="hidden" name="cart_quantity" value="'.$quantity.'" />';
        }
        $html .= '</div>';

        echo $html;
        return $html;
    }


    // do/do not echo code for a selected radio button
    function zm_radio_state($setting, $value=true, $default=false) {
        if ($setting == $value || ($default && zm_empty($value))) {
            echo ' checked="checked"';
        }
    }

    // do/do not echo code for a selected checkbox
    function zm_checkbox_state($setting, $value=true, $default=false) {
        if ($setting == $value || ($default && zm_empty($value))) {
            echo ' checked="checked"';
        }
    }


    // create all required hidden fields for a shopping cart item
    function zm_sc_product_hidden($scItem, $echo=true) {
        $html = '<input type="hidden" name="products_id[]" value="' . $scItem->getId() . '" />';
        if ($scItem->hasAttributes()) {
            foreach ($scItem->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $attributeValue) {
                    $html .= '<input type="hidden" name="id[' . $scItem->getId() . '][' . $attribute->getId() . ']" value="' . $attributeValue->getId() . '" />';
                }
            }
        }

        if ($echo) echo $html;
        return $html;
    }

    // create form id for shipping method
    function zm_shipping_id($method, $echo=true) {
        $provider = $method->getProvider();
        $id = $provider->getId() . '_' . $method->getId();

        if ($echo) echo $id;
        return $id;
    }


    /**
     * Create a full HTML &gt;a&lt; tag.
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
        $target = $page->isNewWin() ? (zm_setting('isJSTarget') ? ' onclick="newWin(this); return false;"' : ' target="_blank"') : '';
        $link = '<a href="' . zm_ezpage_href($page, false) . '"' . $target . '>' . (null == $text ? $page->getTitle() : $text) . ' </a>';

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
        $href = DIR_WS_IMAGES . $src;

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

?>
