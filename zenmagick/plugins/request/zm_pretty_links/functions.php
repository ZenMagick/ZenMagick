<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 */
?>
<?php


    /**
     * Add a custom mapping for pretty link generation.
     *
     * <p>The converter function will be called with two parameters; the current page name
     * and as second parameter a complete map of query parameters.</p>
     *
     * @package org.zenmagick.plugins.zm_pretty_links
     * @param string view The view name (ie. the page name as referred to by the parameter <code>ZM_PAGE_KEY</code>)
     * @param mixed convert Function converting the view name to a pretty link; default is <code>null</code>
     *  which will be interpreted as using the view name.
     * @param array params List of query parameters to append as part of the pretty link.
     */
    function zm_pretty_links_set_mapping($view, $convert=null, $params=array(), $exclude=array()) { 
    global $_zm_pretty_link_map;

        if (!isset($_zm_pretty_link_map)) {
            $_zm_pretty_link_map = array();
        }

        $_zm_pretty_link_map[$view] = array('convert' => $convert, 'params' => $params, 'exclude' => $exclude);
    }
    
    /**
     * ZenMagick SEO API function.
     *
     * @package org.zenmagick.plugins.zm_pretty_links
     */
    function zm_build_seo_href($view=null, $params='', $isSecure=false, $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
    global $_zm_pretty_link_map;

        $toolbox = ZMRequest::instance()->getToolbox();
        $href = $toolbox->net->furl($view, $params, $secure ? 'SSL' : 'NONSSL', $addSessionId, false, $isStatic, $useContext);

        if (null != ZMSettings::get('seoEnabledPagesList') && !ZMLangUtils::inArray($view, ZMSettings::get('seoEnabledPagesList'))) {
            return $href;
        }

        $url = parse_url($href);
        $queryString = $toolbox->net->decode($url['query']);
        parse_str($queryString, $query);
        $path = dirname($url['path']);
        if (!ZMLangUtils::endsWith($path, '/')) {
            $path .= '/';
        }
        if (ZMLangUtils::startsWith($path, '\\')) {
            $path = substr($path, 1);
        }
        $page = $query[ZM_PAGE_KEY];
        $translate = true;
        $removeNames = array(ZM_PAGE_KEY, 'cPath', 'manufacturers_id', 'cat', 'products_id', 'order_id', 'reviews_id', 'id');
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
            case 'gv_redeem':
                $path .= "account/giftcard/redeem/";
                if (array_key_exists('couponCode', $query)) {
                    $path .= $query['couponCode'];
                    array_push($removeNames, 'couponCode');
                }
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
            case 'rss':
                $path .= $query['channel'];
                array_push($removeNames, 'channel');
                if (isset($query['key'])) {
                    $path .= "/".$query['key'];
                    array_push($removeNames, 'key');
                }
                $path .= "/rss.xml";
                break;
            case 'redirect':
                $path .= "redirect/".$query['action']."/".$query['goto'];
                array_push($removeNames, 'goto');
                array_push($removeNames, 'action');
                break;
            default:
                if (ZMLangUtils::startsWith($page, 'popup_')) {
                    $path .= "popup/".substr($page, 6);
                } else {
                    if (isset($_zm_pretty_link_map) && isset($_zm_pretty_link_map[$page])) {
                        $mapping = $_zm_pretty_link_map[$page];
                        if (null == $mapping['convert']) {
                            $path .= $page;
                        } else {
                            if (function_exists($mapping['convert'])) {
                                $path .= call_user_func($mapping['convert'], $page, $query);
                            }
                        }

                        foreach ($mapping['params'] as $mp) {
                            if (isset($query[$mp])) {
                                $path .= '/'.$query[$mp];
                                array_push($removeNames, $mp);
                            }
                        }
                        $removeNames = array_merge($removeNames, $mapping['exclude']);
                    } else {
                        $translate = false;
                        if (!empty($page)) {
                            ZMLogging::instance()->log("no pretty link mapping for: ".$page);
                        }
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
        foreach ($query as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $params .= '&amp;'.$name.'[]='.$subValue;
                }
            } else {
                $params .= '&amp;'.$name.'='.$value;
            }
        }
        $params = (0 < strlen($params) ? ('?'.substr($params, 5)) : '');

        if ($translate) {
            $href = $url['scheme']."://".$url['host'].$path.(isset($url['fragment']) ? '#'.$url['fragment'] : '').$params;
        }

        return $href;
    }

?>
