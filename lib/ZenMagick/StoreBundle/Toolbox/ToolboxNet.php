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
namespace ZenMagick\StoreBundle\Toolbox;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Http\Toolbox\ToolboxTool;

/**
 * Networking/URL related functions.
 *
 * @author DerManoMann
 */
class ToolboxNet extends ToolboxTool
{
    /**
     * Create a URL.
     *
     * <p>Mother of all URL related methods.</p>
     *
     * <p>If the <code>requestId</code> parameter is <code>null</code>, the current requestId will be
     * used. The provided parameter(s) will be merged into the current query string.</p>
     *
     * <p>If the <code>params</code> parameter is <code>null</code>, all parameters of the
     * current request will be added.</p>
     *
     * <p>This default implementation relies on at least a single (default) SEO rewriter being configured.</p>
     *
     * @param string requestId The request id; default is <code>null</code> to use the value of the current request.
     * @param string params Query string style parameter; if <code>null</code> add all current parameters; default is an empty string for none.
     * @param boolean secure Flag indicating whether to create a secure or non secure URL; default is <code>false</code>.
     * @return string A full URL.
     */
    public function url($requestId=null, $params='', $secure=false)
    {
        // default to current requestId
        $requestId = $requestId === null ? $this->getRequest()->getRequestId() : $requestId;

        parse_str(ltrim($params, '&'), $parameters);
        $url = $this->container->get('router')->generate($requestId, $parameters);

        return $url;
    }

    /**
     * Encode a given URL to valid HTML.
     *
     * @param string url The url to encode.
     * @return string The URL encoded in valid HTM.
     */
    public function encode($url)
    {
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
    public function decode($url)
    {
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
    public function product($productId, $categoryId=null)
    {
        $cPath = '';
        if (null != $categoryId) {
            $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $this->getRequest()->getSession()->getLanguageId());
            if (null != $category) {
                $cPath = '&cPath='.implode('_', $category->getPath());
            }
        }

        return $this->url('product_info', '&productId='.$productId.$cPath);
    }

    /**
     * Create a static page URL for the given static page name.
     *
     * @param string name The static page name.
     * @return string A complete URL for the given static page.
     */
    public function staticPage($name)
    {
        return $this->url('static', '&cat='.$name);
    }

    /**
     * Build an ez-page URL.
     *
     * @param ZenMagick\StoreBundle\Entity\EZPage an EZPage instance
     * @return string A complete URL for the given ez-page.
     */
    public function ezPage($page)
    {
        if (null === $page) {
            $href = _zm('ezpage not found');

            return $href;
        }

        $params = '&id='.$page->getId();
        if (0 != $page->getTocChapter()) {
            $params .= '&chapter='.$page->getTocChapter();
        }

        $href = $this->url('page', $params, $page->isSsl());
        if (!Toolbox::isEmpty($page->getAltUrl())) {
            $url = parse_url($page->getAltUrl());
            parse_str($url['query'], $query);
            $view = $query['main_page'];
            unset($query['main_page']);
            $params = '';
            foreach ($query as $name => $value) {
                $params .= "&".$name."=".$value;
            }
            $href = $this->url($view, $params, $page->isSsl());
        } elseif (!Toolbox::isEmpty($page->getAltUrlExternal())) {
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
    public function image($src)
    {
        // TODO: where are images coming from in the future??
        $href = $this->getRequest()->getBaseUrl().'/images/'.$src;

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
    public function trackLink($action, $id)
    {
        if ('url' == $action && false === strpos('://', $id)) {
            $id = 'http://'.$id;
        }

        return $this->url('redirect', "action=".$action."&goto=".$id);
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
    public function ajax($controller, $method, $params='')
    {
        if (Runtime::isContextMatch('admin')) {
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
    public function rssFeed($channel, $key=null)
    {
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
     * @param array keep Optional list of parameters to keep.
     * @return string A URL pointing to the previous page or <code>null</code>.
     */
    public function resultListBack($resultList, $secure=null, $keep=array())
    {
        if (!$resultList->hasPreviousPage()) {
            return null;
        }

        $params = 'page='.$resultList->getPreviousPageNumber();
        foreach ($keep as $name) {
            $params .= '&'.$name.'='.$this->getRequest()->query->get($name);
        }

        $secure = null !== $secure ? $secure : $this->getRequest()->isSecure();
        $url = $this->url(null, $params, $secure);

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
    public function resultListNext($resultList, $secure=null, $keep=array())
    {
        if (!$resultList->hasNextPage()) {
            return null;
        }

        $params = 'page='.$resultList->getNextPageNumber();
        foreach ($keep as $name) {
            $params .= '&'.$name.'='.$this->getRequest()->query->get($name);
        }

        $secure = null !== $secure ? $secure : $this->getRequest()->isSecure();
        $url = $this->url(null, $params, $secure);

        return $url;
    }

    /**
     * Get the top level domain from a given url.
     *
     * @param string url The url
     * @return string The top level domain.
     * @see http://stackoverflow.com/questions/399250/going-where-php-parse-url-doesnt-parsing-only-the-domain
     */
    public function getDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        return $domain;
    }

    /**
     * Convert a given relative URL into an absolute one.
     *
     * @param string url The (relative) URL to convert.
     * @param boolean full Set to true to create a full URL incl. the protocol, hostname, port, etc.; default is <code>false</code>.
     * @param boolean secure Set to true to force a secure URL; default is <code>false</code>.
     * @return string The absolute URL.
     * @todo probably replace with methods specifically for assets
     */
    public function absoluteUrl($url, $full=false, $secure=false)
    {
        $url = (!empty($url) && ('/' == $url[0] || false !== strpos($url, '://'))) ? $url : $this->getBaseUrl().'/'.$url;
        $secure = $this->container->get('settingsService')->get('zenmagick.http.request.enforceSecure') && $secure;
        if ($full || ($secure && !$this->isSecure())) {
            // full requested or we need a full URL to ensure it will be secure
            $isSecure = ($this->isSecure() || $secure);
            $scheme = ($this->isSecure() || $secure) ? 'https://' : 'http://';
            $url = $scheme.$this->getHttpHost().$url;
        }

        return $url;
    }
}
