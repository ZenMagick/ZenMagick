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
        $params = array('productId' => $productId);
        if (null != $categoryId) {
            $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $this->getRequest()->getSession()->getLanguageId());
            if (null != $category) {
                $params['cPath'] = implode('_', $category->getPath());
            }
        }

        $router = $this->container->get('router');

        return $router->generate('product_info', $params);
    }

    /**
     * Create a static page URL for the given static page name.
     *
     * @param string name The static page name.
     * @return string A complete URL for the given static page.
     */
    public function staticPage($name)
    {
        $router = $this->container->get('router');

        return $router->generate('static', array('cat' => $name));
    }

    /**
     * Build an ez-page URL.
     *
     * @param ZenMagick\StoreBundle\Entity\EZPage an EZPage instance
     * @return string A complete URL for the given ez-page.
     * @todo create ezpage router loader for SSL links.
     */
    public function ezPage($page)
    {
        if (null === $page) {
            $href = _zm('ezpage not found');

            return $href;
        }

        $params = array('id' => $page->getId());
        if (0 != $page->getTocChapter()) {
            $params['chapter'] = $page->getTocChapter();
        }

        // We are abusing the 3rd generate param to hack up SSL links
        $router = $this->container->get('router');
        $href = $router->generate('page', $params, $page->isSsl());
        if (!Toolbox::isEmpty($page->getAltUrl())) {
            $url = parse_url($page->getAltUrl());
            parse_str($url['query'], $query);
            $view = $query['main_page'];
            unset($query['main_page']);
            $href = $router->generate($view, $query, $page->isSsl());
        } elseif (!Toolbox::isEmpty($page->getAltUrlExternal())) {
            $href = $page->getAltUrlExternal();
        }
        if ($page->isSsl()) {
            $href = str_replace('http:', 'https:', $href);
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
        $href = $this->getRequest()->getBasePath().'/images/'.$src;

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

        $router = $this->container->get('router');

        return $router->generate('redirect', array('action' => $action, 'goto' => $id));
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
    public function ajax($controller, $method, $params=array())
    {
        $controller = 'ajax_'.$controller;

        $params['method'] = $method;
        $router = $this->container->get('router');
        $url = str_replace('&amp;', '&', $router->generate($controller, $params));

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

        $params = array('page' => $resultList->getPreviousPageNumber());
        foreach ($keep as $name) {
            $params[$name] = $this->getRequest()->query->get($name);
        }
        $router = $this->container->get('router');
        $url = $router->generate($this->getRequest()->getRequestId(), $params);

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

        $params = array('page' => $resultList->getNextPageNumber());
        foreach ($keep as $name) {
            $params[$name] = $this->getRequest()->query->get($name);
        }

        $router = $this->container->get('router');
        $url = $router->generate($this->getRequest()->getRequestId(), $params);

        return $url;
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
        if ($full || ($secure && !$this->isSecure())) {
            // full requested or we need a full URL to ensure it will be secure
            $isSecure = ($this->isSecure() || $secure);
            $scheme = ($this->isSecure() || $secure) ? 'https://' : 'http://';
            $url = $scheme.$this->getHttpHost().$url;
        }

        return $url;
    }
}
