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
 * WP controller.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_wordpress
 * @version $Id$
 */
class ZMWpController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_wordpress;

        $viewName = 'wp_index';

        if (null != ZMRequest::getParameter('p')) {
            $viewName = 'wp_single';
        } else if (null != ($pageId = ZMRequest::getParameter('page_id'))) {
            $viewName = 'wp_page';
            $zm_wordpress->query_posts('page_id='.$pageId);
        } else if (null != ($cat = ZMRequest::getParameter('cat'))) {
            $viewName = 'wp_archive';
            $zm_wordpress->query_posts('cat='.$cat);
        } else if (null != ($m = ZMRequest::getParameter('m'))) {
            $viewName = 'wp_archive';
            $zm_wordpress->query_posts('m='.$m);
        } else if (null != ($s = ZMRequest::getParameter('s'))) {
            $viewName = 'wp_search';
            $zm_wordpress->query_posts('s='.$s);
        } else if (null != ($tag = ZMRequest::getParameter('tag'))) {
            $viewName = 'wp_archive';
            $zm_wordpress->query_posts('tag='.$tag);
        }

        if ('wp_index' == $viewName) {
            $zm_wordpress->query_posts();
        }

        add_filter('tag_link', array($this, 'link_filter'));
        add_filter('post_link', array($this, 'link_filter'));
        add_filter('page_link', array($this, 'link_filter'));
        add_filter('category_link', array($this, 'link_filter'));
        add_filter('month_link', array($this, 'link_filter'));
        add_filter('comments_template', array($this, 'comments_template_filter'));
        add_filter('search_template', array($this, 'link_filter'));

        return $this->findView($viewName);
    }

    /**
     * WP filter to adjust links.
     */
    public function link_filter($arg) {
        $urlToken = parse_url($arg);
        return ZMToolbox::instance()->net->url(FILENAME_WP, $urlToken['query'], false, false);
    }

    /**
     * WP filter to adjust comments include.
     */
    public function comments_template_filter($arg) {
        return ZMRuntime::getTheme()->themeFile('views/wp/comments.php');
    }

}

?>
