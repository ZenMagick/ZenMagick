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
use zenmagick\base\Toolbox;
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
    public function encode($url) {
        $url = htmlentities($url, ENT_QUOTES, Runtime::getSettings()->get('zenmagick.http.html.charset'));
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
        $s = html_entity_decode($url, ENT_QUOTES, Runtime::getSettings()->get('zenmagick.http.html.charset'));
        $s = str_replace('%20', ' ', $s);
        return $s;
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
                $cPath = '&cPath='.implode('_', $category->getPath());
            }
        }
        return $this->getRequest()->url('product_info', '&productId='.$productId.$cPath);
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
        if (!Toolbox::isEmpty($page->getAltUrl())) {
            $url = parse_url($page->getAltUrl());
            parse_str($url['query'], $query);
            $view = $query[Runtime::getSettings()->get('zenmagick.http.request.idName')];
            unset($query[Runtime::getSettings()->get('zenmagick.http.request.idName')]);
            $params = '';
            foreach ($query as $name => $value) {
                $params .= "&".$name."=".$value;
            }
            $href = $this->getRequest()->url($view, $params, $page->isSSL());
        } else if (!Toolbox::isEmpty($page->getAltUrlExternal())) {
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
        if ('url' == $action && false === strpos('://', $id)) {
            $id = 'http://'.$id;
        }
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
            $params .= '&'.$name.'='.$this->getRequest()->query->get($name);
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
            $params .= '&'.$name.'='.$this->getRequest()->query->get($name);
        }

        $secure = null !== $secure ? $secure : $this->getRequest()->isSecure();
        $url = $this->getRequest()->url(null, $params, $secure);

        return $url;
    }

    /**
     * Get the top level domain from a given url.
     *
     * @param string url The url
     * @return string The top level domain.
     * @see http://stackoverflow.com/questions/399250/going-where-php-parse-url-doesnt-parsing-only-the-domain
     */
    public function getDomain($url) {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        return $domain;
    }

    /**
     * Wrapper around Request::absoluteUrl();
     *
     * {@inheritDoc}
     * @todo probably replace with methods specifically for assets
     */
    public function absoluteUrl($url, $full=false, $secure=false) {
        return $this->getRequest()->absoluteUrl($url, $full, $secure);
    }
}
