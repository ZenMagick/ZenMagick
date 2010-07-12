<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * WP request handler.
 *
 * @author mano
 * @package org.zenmagick.plugins.wordpress
 */
class ZMWordpressRequestHandler extends ZMController {
    private $plugin_;
    private $request_;
    private $viewName_;


    /**
     * Create new instance.
     *
     * @param ZMPlugin plugin The parent plugin reference.
     * @param ZMRequest request The current request.
     */
    function __construct($plugin, $request) {
        parent::__construct();
        $this->plugin_ = $plugin;
        $this->request_ = $request;
        $this->viewName_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Do the WP specific request processing and figure out a view name if the current
     * request is wp.
     * 
     * @return string A view name.
     */
    public function preProcess($request) {
        if (null !== $this->viewName_) {
            return $this->viewName_;
        }

        $this->viewName_ = 'wp_index';

        if (null != ($p = $request->getParameter('p'))) {
            $this->viewName_ = 'wp_single';
            $this->plugin_->query_posts('p='.$p);
        } else if (null != ($pageId = $request->getParameter('page_id'))) {
            $this->viewName_ = 'wp_page';
            $this->plugin_->query_posts('page_id='.$pageId);
        } else if (null != ($cat = $request->getParameter('cat'))) {
            $this->viewName_ = 'wp_archive';
            $this->plugin_->query_posts('cat='.$cat);
        } else if (null != ($m = $request->getParameter('m'))) {
            $this->viewName_ = 'wp_archive';
            $this->plugin_->query_posts('m='.$m);
        } else if (null != ($s = $request->getParameter('s'))) {
            $this->viewName_ = 'wp_search';
            $this->plugin_->query_posts('s='.$s);
        } else if (null != ($tag = $request->getParameter('tag'))) {
            $this->viewName_ = 'wp_archive';
            $this->plugin_->query_posts('tag='.$tag);
        }

        if ('wp_index' == $this->viewName_) {
            $this->plugin_->query_posts();
        }

        return $this->viewName_;
    }

    /**
     * Register all necessary filter to manipulate WP content to fit into ZenMagick.
     */
    public function register() {
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
    public function link_filter($arg) {
        $urlToken = parse_url($arg);
        if ($this->plugin_->isPermalinksEnabled()) {
            // make sure we stay on the same server
            $selfUrlToken = parse_url($this->request_->getPageBase());
            if ($urlToken['host'] != $selfUrlToken['host']) {
                $arg =  str_replace($urlToken['host'], $selfUrlToken['host'], $arg);
            }

            // fix path
            $path = $this->request_->getContext().$this->plugin_->get('permaPrefix').'/';
            // does url path start with WP installation folder?
            $wpDir = basename($this->plugin_->get('wordpressDir'));
            if (!ZMLangUtils::isEmpty($wpDir) && 0 === strpos($urlToken['path'], '/'.$wpDir.'/')) {
                return str_replace('/'.$wpDir.'/', $path, $arg);
            } else {
                //TODO:
                //$_SERVER['REQUEST_URI'] = str_replace($this->request_->getContext().$this->plugin_->get('permaPrefix').'/', '', $_SERVER['REQUEST_URI']);
            }
        } else {
            return ZMRequest::instance()->url(FILENAME_WP, $urlToken['query']);
        }
    }

    /**
     * WP filter to adjust comments include.
     */
    public function comments_template_filter($arg) {
        if (ZMSettings::get('plugins.wordpress.isUseOwnViews', false)) {
            return $this->plugin_->getPluginDirectory().'content/wp/comments.php';
          } else {
            return Runtime::getTheme()->themeFile('views/wp/comments.php');
          }
    }

}
