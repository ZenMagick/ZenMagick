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

namespace ZenMagick\plugins\wordpress;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

/**
 * WP request handler.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class WordpressRequestHandler extends DefaultController
{
    public $wp_filter_id;
    private $plugin;
    private $request;
    private $viewName;

    /**
     * Create new instance.
     *
     * @param ZMPlugin plugin The parent plugin reference.
     * @param ZenMagick\Http\Request request The current request.
     */
    public function __construct($plugin, $request)
    {
        parent::__construct();
        $this->wp_filter_id = get_class($this);
        $this->plugin = $plugin;
        $this->request = $request;
        $this->viewName = null;
    }

    /**
     * Do the WP specific request processing and figure out a view name if the current
     * request is wp.
     *
     * @return string A view name.
     */
    public function preProcess($request)
    {
        if (null !== $this->viewName) {
            return $this->viewName;
        }

        $this->viewName = 'wp_index';

        if (null != ($p = $request->query->get('p'))) {
            $this->viewName = 'wp_single';
            $this->plugin->query_posts('p='.$p);
        } elseif (null != ($pageId = $request->query->get('page_id'))) {
            $this->viewName = 'wp_page';
            $this->plugin->query_posts('page_id='.$pageId);
        } elseif (null != ($cat = $request->query->get('cat'))) {
            $this->viewName = 'wp_archive';
            $this->plugin->query_posts('cat='.$cat);
        } elseif (null != ($m = $request->query->get('m'))) {
            $this->viewName = 'wp_archive';
            $this->plugin->query_posts('m='.$m);
        } elseif (null != ($s = $request->query->get('s'))) {
            $this->viewName = 'wp_search';
            $this->plugin->query_posts('s='.$s);
        } elseif (null != ($tag = $request->query->get('tag'))) {
            $this->viewName = 'wp_archive';
            $this->plugin->query_posts('tag='.$tag);
        }

        if ('wp_index' == $this->viewName) {
            $this->plugin->query_posts();
        }

        return $this->viewName;
    }

    /**
     * Register all necessary filter to manipulate WP content to fit into ZenMagick.
     *
     * @param View view The current view.
     */
    public function registerFilter($view)
    {
        add_filter('tag_link', array($this, 'link_filter'));
        add_filter('post_link', array($this, 'link_filter'));
        add_filter('page_link', array($this, 'link_filter'));
        add_filter('category_link', array($this, 'link_filter'));
        add_filter('get_pagenum_link', array($this, 'link_filter'));
        add_filter('month_link', array($this, 'link_filter'));
        add_filter('comments_template', array($this, 'comments_template_filter'));
        add_filter('search_template', array($this, 'link_filter'));
    }

    /**
     * WP filter to adjust links.
     */
    public function link_filter($arg)
    {
        $urlToken = parse_url($arg);
        if ($this->plugin->isPermalinksEnabled()) {
            // make sure we stay on the same server
            $host = $this->request->getHost();
            if ($urlToken['host'] != $host) {
                $arg =  str_replace($urlToken['host'], $host, $arg);
            }

            // fix path
            $path = $this->request->getBaseUrl().$this->plugin->get('permaPrefix').'/';
            // does url path start with WP installation folder?
            $wpDir = basename($this->plugin->get('wordpressDir'));
            if (!Toolbox::isEmpty($wpDir) && 0 === strpos($urlToken['path'], '/'.$wpDir.'/')) {
                return str_replace('/'.$wpDir.'/', $path, $arg);
            } else {
                //TODO:
                //$_SERVER['REQUEST_URI'] = str_replace($this->request->getBaseUrl().$this->plugin->get('permaPrefix').'/', '', $_SERVER['REQUEST_URI']);
            }
        } else {
            $router = Runtime::getContainer()->get('router');
            return $router->generate('wp', $urlToken['query']);
        }
    }

    /**
     * WP filter to adjust comments include.
     */
    public function comments_template_filter($arg)
    {
        $locator = Runtime::getContainer()->get('templating.locator');
        return $locator->locate('wp/comments.html.php');
    }

}
