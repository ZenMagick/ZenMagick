<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\toolbox;

use zenmagick\base\Runtime;
use zenmagick\http\toolbox\ToolboxTool;

/**
 * Networking/URL related functions.
 *
 * @author DerManoMann
 */
class ToolboxNet extends ToolboxTool {

    /**
     * Create a URL.
     *
     * <p>Convenience/compatibility method calling <code>url()</code> on ZMRequest.</p>
     *
     * @param string requestId The request id.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @param boolean secure Flag indicating whether to create a secure or non secure URL; default is <code>false</code>.
     * @return string A full URL.
     */
    public function url($requestId=null, $params='', $secure=false) {
        return $this->getRequest()->url($requestId, $params, $secure);
    }

    /**
     * Encode a given URL to valid HTML.
     *
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     */
    public static function encode($url) {
        return \ZMNetUtils::encode($url);
    }

    /**
     * Decode a HTML encoded URL.
     *
     * @param string url The url to decode.
     * @return string The decoded URL.
     */
    public static function decode($url) {
        return \ZMNetUtils::decode($url);
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
            $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $this->getRequest()->getSession()->getLanguageId());
            if (null != $category) {
                $cPath = '&'.$category->getPath();
            }
        }
        return $this->getRequest()->url('product_info', '&products_id='.$productId.$cPath);
    }

    /**
     * Create a static page URL for the given static page name.
     *
     * @param string name The static page name.
     * @return string A complete URL for the given static page.
     */
    public function staticPage($name) {
        return $this->getRequest()->url('static', '&cat='.$name);
    }

    /**
     * Build an ez-page URL.
     *
     * @param ZMEZPage page A <code>ZMEZPage</code> instance.
     * @return string A complete URL for the given ez-page.
     */
    public function ezPage($page) {
        if (null === $page) {
            $href = _zm('ezpage not found');
            return $href;
        }

        $params = '&id='.$page->getId();
        if (0 != $page->getTocChapter()) {
            $params .= '&chapter='.$page->getTocChapter();
        }

        $href = $this->getRequest()->url('page', $params, $page->isSSL());
        if (!\ZMLangUtils::isEmpty($page->getAltUrl())) {
            $url = parse_url($page->getAltUrl());
            parse_str($url['query'], $query);
            $view = $query[Runtime::getSettings()->get('zenmagick.http.request.idName')];
            unset($query[Runtime::getSettings()->get('zenmagick.http.request.idName')]);
            $params = '';
            foreach ($query as $name => $value) {
                $params .= "&".$name."=".$value;
            }
            $href = $this->getRequest()->url($view, $params, $page->isSSL());
        } else if (!\ZMLangUtils::isEmpty($page->getAltUrlExternal())) {
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
        // TODO: where are images coming from in the future??
        $href = $this->getRequest()->getContext().'/images/'.$src;

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
        return $this->getRequest()->url('redirect', "action=".$action."&goto=".$id);
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
        if (Runtime::isContextMatch('admin')) {
            $params .= '&controller=ajax_'.$controller;
            $controller = 'zmAjaxHandler.php';
        } else {
            $controller = 'ajax_'.$controller;
        }

        $url = str_replace('&amp;', '&', $this->getRequest()->url($controller, $params.'&method='.$method, $this->getRequest()->isSecure()));

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
        $url = $this->getRequest()->url('rss', $params);

        return $url;
    }

    /**
     * Build a result list URL pointing to the previous page.
     *
     * @param ZMResultList resultList The current result list.
     * @param boolean secure If <code>true</code>, the URI will be secure; default is <code>null</code> to use the current
     *  request state.
     * @param array keep Optional list of parameters to keep.
     * @return string A URL pointing to the previous page or <code>null</code>.
     */
    public function resultListBack($resultList, $secure=null, $keep=array()) {
        if (!$resultList->hasPreviousPage()) {
            return null;
        }

        $params = 'page='.$resultList->getPreviousPageNumber();
        foreach ($keep as $name) {
            $params .= '&'.$name.'='.$this->getRequest()->getParameter($name);
        }

        $secure = null !== $secure ? $secure : $this->getRequest()->isSecure();
        $url = $this->getRequest()->url(null, $params, $secure);

        return $url;
    }

    /**
     * Build a URL pointing to the next page.
     *
     * @param ZMResultList resultList The current result list.
     * @param boolean secure If <code>true</code>, the URI will be secure; default is <code>null</code> to use the current
     *  request state.
     * @param array keep Optional list of parameters to keep.
     * @return string A URL pointing to the next page or <code>null</code>.
     */
    public function resultListNext($resultList, $secure=null, $keep=array()) {
        if (!$resultList->hasNextPage()) {
            return null;
        }

        $params = 'page='.$resultList->getNextPageNumber();
        foreach ($keep as $name) {
            $params .= '&'.$name.'='.$this->getRequest()->getParameter($name);
        }

        $secure = null !== $secure ? $secure : $this->getRequest()->isSecure();
        $url = $this->getRequest()->url(null, $params, $secure);

        return $url;
    }

}
