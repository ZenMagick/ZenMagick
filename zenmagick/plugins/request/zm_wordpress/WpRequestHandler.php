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
 * WP request handler.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_wordpress
 * @version $Id$
 */
class WpRequestHandler extends ZMController {
    private $plugin;
    private $viewName;


    /**
     * Create new instance.
     *
     * @param ZMPlugin plugin The parent plugin reference.
     */
    function __construct($plugin) {
        parent::__construct();
        $this->plugin = $plugin;
        $this->viewName = null;
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
    public function handleRequest() {
        if (null !== $this->viewName) {
            return $this->viewName;
        }

        $this->viewName = 'wp_index';

        if (null != ($p = ZMRequest::getParameter('p'))) {
            $this->viewName = 'wp_single';
            $this->plugin->query_posts('p='.$p);
        } else if (null != ($pageId = ZMRequest::getParameter('page_id'))) {
            $this->viewName = 'wp_page';
            $this->plugin->query_posts('page_id='.$pageId);
        } else if (null != ($cat = ZMRequest::getParameter('cat'))) {
            $this->viewName = 'wp_archive';
            $this->plugin->query_posts('cat='.$cat);
        } else if (null != ($m = ZMRequest::getParameter('m'))) {
            $this->viewName = 'wp_archive';
            $this->plugin->query_posts('m='.$m);
        } else if (null != ($s = ZMRequest::getParameter('s'))) {
            $this->viewName = 'wp_search';
            $this->plugin->query_posts('s='.$s);
        } else if (null != ($tag = ZMRequest::getParameter('tag'))) {
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
     */
    public function register() {
        add_filter('tag_link', array($this, 'link_filter'));
        add_filter('post_link', array($this, 'link_filter'));
        add_filter('page_link', array($this, 'link_filter'));
        add_filter('category_link', array($this, 'link_filter'));
        add_filter('month_link', array($this, 'link_filter'));
        add_filter('comments_template', array($this, 'comments_template_filter'));
        add_filter('search_template', array($this, 'link_filter'));
    }

    /**
     * WP filter to adjust links.
     */
    public function link_filter($arg) {
        $urlToken = parse_url($arg);
        if ($this->plugin->isPermalinksEnabled()) {
            $path = ZMRuntime::getContext().$this->plugin->get('permaPrefix').'/';
            // does url path start with WP installation folder?
            $wpDir = basename($this->plugin->get('wordpressDir'));
            if (!ZMTools::isEmpty($wpDir) && 0 === strpos($urlToken['path'], '/'.$wpDir.'/')) {
                return str_replace('/'.$wpDir.'/', $path, $arg);
            } else {
                //TODO:
                //$_SERVER['REQUEST_URI'] = str_replace(ZMRuntime::getContext().$this->plugin->get('permaPrefix').'/', '', $_SERVER['REQUEST_URI']);
            }
        } else {
            return ZMToolbox::instance()->net->url(FILENAME_WP, $urlToken['query'], false, false);
        }
    }

    /**
     * WP filter to adjust comments include.
     */
    public function comments_template_filter($arg) {
        if (ZMSettings::get('plugins.zm_wordpress.isUseOwnViews', false)) {
            return $this->plugin->getPluginDir().'wp/comments.php';
          } else {
            return ZMRuntime::getTheme()->themeFile('views/wp/comments.php');
          }
    }

}

?>
