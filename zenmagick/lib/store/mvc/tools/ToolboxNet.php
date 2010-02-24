<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Networking/URL related functions.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.tools
 * @version $Id$
 */
class ToolboxNet extends ZMToolboxNet {

    /**
     * ZenMagick implementation of zen-cart's zen_href_link function.
     */
    public function furl($page=null, $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
    //TODO:
    global $session_started, $http_domain, $https_domain;

        $isAdmin = false;
        if (ZMSettings::get('isAdmin')) {
            // admin links!
            $isAdmin = true;
            //TODO: init!
            if (empty($page)) {
                if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
                while (false !== strpos($PHP_SELF, '//')) $PHP_SELF = str_replace('//', '/', $PHP_SELF);
                $page = $PHP_SELF;
            } else {
                $page = DIR_WS_ADMIN . $page;
            }
            $useContext = false;
            $isStatic = true;
        } else if (empty($page)) {
            throw new ZMException('missing page parameter');
        }

        // handle SEO
        $rewriters = $this->getSeoRewriter();
        if (!$isAdmin && $seo && 0 < count($rewriters)) {
            $rewrittenUrl = null;
            $args = array(
              'requestId' => $page, 
              'params' => $params, 
              'secure' => 'SSL'==$transport, 
              'addSessionId' => $addSessionId, 
              'isStatic' => $isStatic, 
              'useContext' => $useContext
            );
            foreach ($rewriters as $rewriter) {
                if (null != ($rewrittenUrl = $rewriter->rewrite($this->getRequest(), $args))) {
                    return $rewrittenUrl;
                 }
            }
        }

        // default to non ssl
        $server = HTTP_SERVER;
        if ($transport == 'SSL' && ZMSettings::get('isEnableSSL')) {
            $server = HTTPS_SERVER;
        }

        $path = '';
        if ($useContext) {
            $path = HTTPS_SERVER == $server ? DIR_WS_HTTPS_CATALOG : DIR_WS_CATALOG;
        }

        // trim '?' and '&' from params
        while ('?' == ($char = substr($params, 0, 1)) || '&' == $char) $params = substr($params, 1);
        while ('?' == ($char = substr($params, -1)) || '&' == $char) $params = substr($params, 0, -1);

        $query = '?';
        if ($isStatic) {
            $path .= $page;
        } else {
            $path .= 'index.php';
            $query .= ZM_PAGE_KEY . '=' . $page;
        }

        if (!empty($params)) {
            $query .= '&'.strtr(trim($params), array('"' => '&quot;'));
        }

        // trim trailing '?' and '&' from path
        while ('?' == ($char = substr($path, -1)) || '&' == $char) $path = substr($path, 0, -1);

        // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
        $sid = null;
        //TODO:$session = $this->getRequest()->getSession();
        if ($addSessionId && ($session_started/* || $session->isStarted()*/) && !ZMSettings::get('isForceCookieUse')) {
            if (defined('SID') && !ZMLangUtils::isEmpty(SID)) {
                // defined, so use it
                $sid = SID;
            } elseif (($transport == 'NONSSL' && HTTPS_SERVER == $server) || ($transport == 'SSL' && HTTP_SERVER == $server)) {
                // switch from http to https or vice versa
                if ($http_domain != $https_domain) {
                    $sid = zen_session_name() . '=' . zen_session_id();
                }
            }
        }

        if (null !== $sid) {
            $query .= '&' . strtr(trim($sid), array('"' => '&quot;'));
        }

        while (false !== strpos($path, '//')) $path = str_replace('//', '/', $path);
        $query = (1 < strlen($query)) ? $query : '';

        return $this->encode($server.$path.$query);
    }


    /**
     * Create a ZenMagick URL.
     *
     * <p>Mother of all other URL related methods in this class.</p>
     *
     * <p>If the <code>page</code> parameter is <code>null</code>, the current page/view will be
     * used. The provided parameter will be merged into the current query string.</p>
     *
     * @param string page The page name (ie. the page name as referred to by the parameter <code>ZM_PAGE_KEY</code>)
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean secure Flag indicating whether to create a secure or non secure URL; default is <code>false</code>.
     * @return string A full URL.
     */
    public function url($page=null, $params='', $secure=false) {
        // custom view and params handling
        if (null === $page || null === $params) {
            $query = $this->getRequest()->getParameterMap();
            unset($query[ZM_PAGE_KEY]);
            unset($query[zen_session_name()]);
            if (null != $params) {
                parse_str($params, $arr);
                $query = array_merge($query, $arr);
            }
            $params = '';
            foreach ($query as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $params .= $name.'[]='.$subValue.'&';
                    }
                } else {
                    $params .= $name.'='.$value.'&';
                }
            }
        }

        // default to current view
        $page = $page === null ? $this->getRequest()->getRequestId() : $page;
        $href = null;
        // no SEO in admin
        // XXX: have separate setting to disable rather than admin (might have to fake that to force regular URLS
        if (function_exists('zm_build_seo_href') && !ZMSettings::get('isAdmin')) {
            // use custom SEO builder function - three args only
            $href = zm_build_seo_href($this->getRequest(), $page, $params, $secure);
        } else {
            // use default implementation - three args only
            $href = $this->furl($page, $params, $secure ? 'SSL' : 'NONSSL');
        }

        return $href;
    }


    /**
     * Convenience function to build a product URL.
     *
     * <p>Please note that in <em>ZenMagick</em> all product URLs use the same
     * view name. The actual view name gets resolved only when the href is used.</p>
     *
     * @param int productId The product id.
     * @param int categoryId Optional category id.
     * @return string A complete product URL.
     */
    public function product($productId, $categoryId=null) { 
        $cPath = '';
        if (null != $categoryId) {
            $category = ZMCategories::instance()->getCategoryForId($categoryId);
            if (null != $category) {
                $cPath = '&'.$category->getPath();
            }
        }
        return $this->url(FILENAME_PRODUCT_INFO, '&products_id='.$productId.$cPath);
    }

    /**
     * Create a static page URL for the given static page name.
     *
     * @param string name The static page name.
     * @return string A complete URL for the given static page.
     */
    public function staticPage($name) { 
        return $this->url('static', '&cat='.$name);
    }

    /**
     * Build an ez-page URL.
     *
     * @param ZMEZPage page A <code>ZMEZPage</code> instance.
     * @return string A complete URL for the given ez-page.
     */
    public function ezPage($page) {
        if (null === $page) {
            $href = zm_l10n_get('ezpage not found');
            return $href;
        }

        $params = '&id='.$page->getId();
        if (0 != $page->getTocChapter()) {
            $params .= '&chapter='.$page->getTocChapter();
        }

        $href = $this->url(FILENAME_EZPAGES, $params, $page->isSSL());
        if (!ZMLangUtils::isEmpty($page->getAltUrl())) {
            $url = parse_url($page->getAltUrl());
            parse_str($url['query'], $query);
            $view = $query[ZM_PAGE_KEY];
            unset($query[ZM_PAGE_KEY]);
            $params = '';
            foreach ($query as $name => $value) {
                $params .= "&".$name."=".$value;
            }
            $href = $this->url($view, $params, $page->isSSL());
        } else if (!ZMLangUtils::isEmpty($page->getAltUrlExternal())) {
            $href = $page->getAltUrlExternal();
        }

        return $href;
    }

    /**
     * Create an absolute image path/URL for the given image.
     *
     * @param string src The relative image name (relative to zen-cart's image folder).
     * @return string The image URI.
     */
    public function image($src) {
        $href = DIR_WS_CATALOG.DIR_WS_IMAGES . $src;

        return $href;
    }

    /**
     * Create an redirecting URL for the given action and id that is trackable.
     *
     * <p>All messages created up to this point during request handling will be saved and
     * restored with the next request handling cycle.</p>
     *
     * @param string action The redirect action.
     * @param string id The redirect id.
     * @return string A full URL.
     */
    public function trackLink($action, $id) {
        return $this->url(FILENAME_REDIRECT, "action=".$action."&goto=".$id);
    }

    /**
     * Convert a given relative href/URL into an absolute one based on the current context.
     *
     * @param string href The URL to convert.
     * @return string The absolute href.
     */
    public function absoluteURL($href) {
        $host = ($this->getRequest()->isSecure() ? HTTPS_SERVER : HTTP_SERVER);
        $context = ($this->getRequest()->isSecure() ? DIR_WS_HTTPS_CATALOG : DIR_WS_CATALOG);

        if (!ZMLangUtils::startsWith($href, '/')) {
            // make fully qualified
            $href = $context . $href;
        }

        // make full URL
        return $host . $href;
    }

    /**
     * Create an Ajax URL for the given controller and method.
     *
     * <p><strong>NOTE:</strong> Ampersand are not encoded in this function.</p>
     *
     * @param string controller The controller name without the leading <em>ajax_</em>.
     * @param string method The name of the method to call.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @return string A complete Ajax URL.
     */
    public function ajax($controller, $method, $params='') { 
        if (ZMSettings::get('isAdmin')) {
            $params .= '&controller=ajax_'.$controller;
            $controller = 'zmAjaxHandler.php';
        } else {
            $controller = 'ajax_'.$controller;
        }

        $url = str_replace('&amp;', '&', $this->url($controller, $params.'&method='.$method, $this->getRequest()->isSecure()));

        return $url;
    }

    /**
     * Build a RSS feed URL.
     *
     * @param string channel The channel.
     * @param string key Optional key; for example, 'new' for the product channel.
     * @return string A complete URL.
     */
    public function rssFeed($channel, $key=null) { 
        $params = 'channel='.$channel;
        if (null !== $key) {
            $params .= "&key=".$key;
        }
        $url = $this->url('rss', $params);

        return $url;
    }

    /**
     * Build a result list URL pointing to the previous page.
     *
     * @param ZMResultList resultList The current result list.
     * @param boolean secure If <code>true</code>, the URI will be secure; default is <code>null</code> to use the current
     *  request state.
     * @return string A URL pointing to the previous page or <code>null</code>.
     */
    public function resultListBack($resultList, $secure=null) {
        if (!$resultList->hasPreviousPage()) {
            return null;
        }

        $secure = null !== $secure ? $secure : $this->getRequest()->isSecure();
        $url = $this->url(null, "&page=".$resultList->getPreviousPageNumber(), $secure);

        return $url;
    }

    /**
     * Build a URL pointing to the next page.
     *
     * @param ZMResultList resultList The current result list.
     * @param boolean secure If <code>true</code>, the URI will be secure; default is <code>null</code> to use the current
     *  request state.
     * @return string A URL pointing to the next page or <code>null</code>.
     */
    public function resultListNext($resultList, $secure=null) {
        if (!$resultList->hasNextPage()) {
            return null;
        }

        $secure = null !== $secure ? $secure : $this->getRequest()->isSecure();
        $url = $this->url(null, "&page=".$resultList->getNextPageNumber(), $secure);

        return $url;
    }

}
