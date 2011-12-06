<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\http\request\rewriter\UrlRewriter;

/**
 * SEO rewriter for pretty link (SEO) support.
 *
 * @package org.zenmagick.plugins.prettyLinks
 * @author mano
 */
class ZMPrettyLinksUrlRewriter implements UrlRewriter {

    /**
     * {@inheritDoc}
     */
    public function decode($request) {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewrite($request, $args) {
        $requestId = $args['requestId'];
        $params = $args['params'];
        $secure = $args['secure'];
        $addSessionId = isset($args['addSessionId']) ? $args['addSessionId'] : true;
        $isStatic = isset($args['isStatic']) ? $args['isStatic'] : false;
        $useContext = isset($args['useContext']) ? $args['useContext'] : true;

        if (null != ZMSettings::get('plugins.prettyLinks.seoEnabled') && !ZMLangUtils::inArray($requestId, ZMSettings::get('plugins.prettyLinks.seoEnabled'))) {
            // not doing anything
            return null;
        }

        // get default url
        $toolbox = $request->getToolbox();
        $href = ZMStoreDefaultUrlRewriter::furl($requestId, $params, $secure ? 'SSL' : 'NONSSL', $addSessionId, false, $isStatic, $useContext, $request);

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
        $requestId = $query[Runtime::getSettings()->get('zenmagick.http.request.idName')];
        $translate = true;
        $removeNames = array(Runtime::getSettings()->get('zenmagick.http.request.idName'), 'cPath', 'manufacturers_id', 'cat', 'products_id', 'order_id', 'reviews_id', 'id');
        switch ($requestId) {
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
                $requestId = str_replace('_', '/', $requestId);
                $path .= $requestId."/";
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
                if (ZMLangUtils::startsWith($requestId, 'popup_')) {
                    $path .= "popup/".substr($requestId, 6);
                } else {
                    if (isset($_zm_pretty_link_map) && isset($_zm_pretty_link_map[$requestId])) {
                        $mapping = $_zm_pretty_link_map[$requestId];
                        if (null == $mapping['convert']) {
                            $path .= $requestId;
                        } else {
                            if (function_exists($mapping['convert'])) {
                                $path .= call_user_func($mapping['convert'], $requestId, $query);
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
                        if (!empty($requestId)) {
                          Runtime::getLogging()->info("no pretty link mapping for: ".$requestId);
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

}
