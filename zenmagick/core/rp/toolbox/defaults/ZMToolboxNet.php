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
 */
?>
<?php


/**
 * Networking/URL related functions.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.toolbox.defaults
 * @version $Id$
 */
class ZMToolboxNet extends ZMObject {

    /**
     * ZenMagick implementation of zen-cart's zen_href_link function.
     */
    public function _zm_zen_href_link($page=null, $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
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
            ZMObject::backtrace('missing page parameter');
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
            $query .= 'main_page=' . $page;
        }

        if (!empty($params)) {
            $query .= '&'.strtr(trim($params), array('"' => '&quot;'));
        }

        // trim trailing '?' and '&' from path
        while ('?' == ($char = substr($path, -1)) || '&' == $char) $path = substr($path, 0, -1);

        // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
        $sid = null;
        //TODO:$session = ZMRequest::getSession();
        if ($addSessionId && ($session_started/* || $session->isValid()*/) && !ZMSettings::get('isForceCookieUse')) {
            if (defined('SID') && !ZMTools::isEmpty(SID)) {
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
     * @param string page The page name (ie. the page name as referred to by the parameter <code>main_page</code>)
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean secure Flag indicating whether to create a secure or non secure URL; default is <code>false</code>.
     * @param boolean echo If <code>true</code>, the URL will be echo'ed as well as returned.
     * @return string A full URL.
     */
    public function url($page=null, $params='', $secure=false, $echo=ZM_ECHO_DEFAULT) {
        // custom view and params handling
        if (null === $page || null === $params) {
            $query = array();
            if (null === $page || null === $params) {
                $query = ZMRequest::getParameterMap();
                unset($query['main_page']);
                unset($query[zen_session_name()]);
            }
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
        $page = $page === null ? ZMRequest::getPageName() : $page;
        $href = null;
        if (function_exists('zm_build_seo_href')) {
            // use custom SEO builder function
            $href = zm_build_seo_href($page, $params, $secure);
        } else {
            // use default implementation
            $href = $this->_zm_zen_href_link($page, $params, $secure ? 'SSL' : 'NONSSL');
        }

        if ($echo) echo $href;
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
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     * @return string A complete product URL.
     */
    public function product($productId, $categoryId=null, $echo=ZM_ECHO_DEFAULT) { 
        $cPath = '';
        if (null != $categoryId) {
            $category = ZMCategories::instance()->getCategoryForId($categoryId);
            if (null != $category) {
                $cPath = '&'.$category->getPath();
            }
        }
        return $this->url(FILENAME_PRODUCT_INFO, '&products_id='.$productId.$cPath, false, $echo);
    }

    /**
     * Create a static page URL for the given static page name.
     *
     * @param string name The static page name.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete URL for the given static page.
     */
    public function staticPage($name, $echo=ZM_ECHO_DEFAULT) { 
        return $this->url('static', '&cat='.$name, false, $echo);
    }

    /**
     * Build an ez-page URL.
     *
     * @param ZMEZPage page A <code>ZMEZPage</code> instance.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete URL for the given ez-page.
     */
    public function ezpage($page, $echo=ZM_ECHO_DEFAULT) {
        if (null === $page) {
            $href = zm_l10n_get('ezpage not found');
            if ($echo) echo $href;
            return $href;
        }

        $params = '&id='.$page->getId();
        if (0 != $page->getTocChapter()) {
            $params .= '&chapter='.$page->getTocChapter();
        }

        $href = $this->url(FILENAME_EZPAGES, $params, $page->isSSL(), false);
        if (!ZMTools::isEmpty($page->getAltUrl())) {
            $url = parse_url($page->getAltUrl());
            parse_str($url['query'], $query);
            $view = $query['main_page'];
            unset($query['main_page']);
            $params = '';
            foreach ($query as $name => $value) {
                $params .= "&".$name."=".$value;
            }
            $href = $this->url($view, $params, $page->isSSL(), false);
        } else if (!ZMTools::isEmpty($page->getAltUrlExternal())) {
            $href = $page->getAltUrlExternal();
        }

        if ($echo) echo $href;
        return $href;
    }

    /**
     * Create an absolute image path/URL for the given image.
     *
     * @param string src The relative image name (relative to zen-cart's image folder).
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The image URI.
     */
    public function image($src, $echo=ZM_ECHO_DEFAULT) {
        $href = DIR_WS_CATALOG.DIR_WS_IMAGES . $src;

        if ($echo) echo $href;
        return $href;
    }

    /**
     * Create an redirect URL for the given action and id.
     *
     * <p>All messages created up to this point during request handling will be saved and
     * restored with the next request handling cycle.</p>
     *
     * @param string action The redirect action.
     * @param string id The redirect id.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    public function redirect($action, $id, $echo=ZM_ECHO_DEFAULT) {
        return $this->url(FILENAME_REDIRECT, "action=".$action."&goto=".$id, false, false, $echo);
    }

    /**
     * Convert a given relative href/URL into an absolute one based on the current context.
     *
     * @param string href The URL to convert.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The absolute href.
     */
    public function absolute($href, $echo=ZM_ECHO_DEFAULT) {
        $host = (ZMRequest::isSecure() ? HTTPS_SERVER : HTTP_SERVER);
        $context = (ZMRequest::isSecure() ? DIR_WS_HTTPS_CATALOG : DIR_WS_CATALOG);

        if (!ZMTools::startsWith($href, '/')) {
            // make fully qualified
            $href = $context . $href;
        }

        // make full URL
        $href = $host . $href;

        if ($echo) echo $href;
        return $href;
    }

    /**
     * Create an Ajax URL for the given controller and method.
     *
     * <p><strong>NOTE:</strong> Ampersand are not encoded in this function.</p>
     *
     * @param string controller The controller name without the leading <em>ajax_</em>.
     * @param string method The name of the method to call.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete Ajax URL.
     */
    public function ajax($controller, $method, $params='', $echo=ZM_ECHO_DEFAULT) { 
        if (ZMSettings::get('isAdmin')) {
            $params .= '&controller=ajax_'.$controller;
            $controller = 'zmAjaxHandler.php';
        } else {
            $controller = 'ajax_'.$controller;
        }

        $url = str_replace('&amp;', '&', $this->url($controller, $params.'&method='.$method, ZMRequest::isSecure(), false));

        if ($echo) echo $url;
        return $url;
    }

    /**
     * Build a RSS feed URL.
     *
     * @param string channel The channel.
     * @param string key Optional key; for example, 'new' for the product channel.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete URL.
     */
    public function rssFeed($channel, $key=null, $echo=ZM_ECHO_DEFAULT) { 
        $params = 'channel='.$channel;
        if (null !== $key) {
            $params .= "&key=".$key;
        }
        $url = $this->url(ZM_FILENAME_RSS, $params, false, false);

        if ($echo) echo $url;
        return $url;
    }

    /**
     * Encode a given URL to valid HTML.
     *
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     */
    public function encode($url) {
        $url = htmlentities($url, ENT_QUOTES, zm_i18n('HTML_CHARSET'));
        $url = str_replace(' ', '%20', $url);
        return $url;
    }

    /**
     * Decode a HTML encoded URL.
     *
     * @param string url The url to decode.
     * @return string The decoded URL.
     */
    public function decode($url) {
        $s = html_entity_decode($url, ENT_QUOTES, zm_i18n('HTML_CHARSET'));
        $s = str_replace('%20', ' ', $s);
        return $s;
    }

    /**
     * Build a result list URL pointing to the previous page.
     *
     * @param ZMResultList resultList The current result list.
     * @param boolean secure If <code>true</code>, the URI will be secure; default is <code>null</code> to use the current
     *  request state.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A URL pointing to the previous page or <code>null</code>.
     */
    public function resultListBack($resultList, $secure=null, $echo=ZM_ECHO_DEFAULT) {
        if (!$resultList->hasPreviousPage()) {
            return null;
        }

        $secure = null !== $secure ? $secure : ZMRequest::isSecure();
        $url = $this->url(null, "&page=".$resultList->getPreviousPageNumber(), $secure, false);

        if ($echo) echo $url;
        return $url;
    }

    /**
     * Build a URL pointing to the next page.
     *
     * @param ZMResultList resultList The current result list.
     * @param boolean secure If <code>true</code>, the URI will be secure; default is <code>null</code> to use the current
     *  request state.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A URL pointing to the next page or <code>null</code>.
     */
    public function resultListNext($resultList, $secure=null, $echo=ZM_ECHO_DEFAULT) {
        if (!$resultList->hasNextPage()) {
            return null;
        }

        $secure = null !== $secure ? $secure : ZMRequest::isSecure();
        $url = $this->url(null, "&page=".$resultList->getNextPageNumber(), $secure, false);

        if ($echo) echo $url;
        return $url;
    }


}

?>
