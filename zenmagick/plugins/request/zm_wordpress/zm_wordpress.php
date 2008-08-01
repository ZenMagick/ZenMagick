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

define('FILENAME_WP', 'wp');

/**
 * Allow Wordpress content to be displayed in ZenMagick.
 *
 * @package org.zenmagick.plugins.zm_wordpress
 * @author DerManoMann
 * @version $Id$
 */
class zm_wordpress extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Wordpress', 'Allows to display Wordpress content in ZenMagick', '${plugin.version}');
        $this->setLoaderSupport('FOLDER');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Wordpress Installation Folder', 'wordpressDir', '', 'Path to your Wordpress installation');
        $this->addConfigValue('WP enabled pages', 'wordpressEnabled', 'wp', 'Comma separated list of pages that can display WP content (leave empty for all).');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        // use API
        define('WP_USE_THEMES', false);

        // set up view mappings used by the wp controller
        $view = 'PageView';
        $parameter = 'subdir=wp';
        if (ZMSettings::get('plugins.zm_wordpress.isUseOwnViews', false)) {
            $view = 'PluginView';
            $parameter = array('plugin' => $this, 'subdir' => 'wp');
        }

        ZMUrlMapper::instance()->setMapping(null, 'wp_index', 'index', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, 'wp_single', 'single', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, 'wp_page', 'page', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, 'wp_archive', 'archive', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, 'wp_archives', 'archives', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, 'wp_search', 'search', $view, $parameter);
    }


    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function filterResponse($contents) {
        if ('wp' == ZMRequest::getPageName()) {
            ob_start();
            wp_head();
            $wp_head = ob_get_clean();
            $contents = preg_replace('/<\/head>/', $wp_head . '</head>', $contents, 1);
        }

        return $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobal() {
        $wordpressEnabled = $this->get('wordpressEnabled');
        if (empty($wordpressEnabled) || ZMTools::inArray(ZMRequest::getPageName(), $wordpressEnabled)) {
            // load as proper global to make WP work - @#!!$&^ globals
            return array('wp-blog-header.gphp');
        }
        return parent::getGlobal();
    }

    /**
     * Execute WP index init.
     *
     * @param string query Parameter for WP <code>query_posts</code> as per WP docs.
     */
    public function query_posts($query='') {
    global $wpdb;

        $wpdb->select(DB_NAME);
        query_posts($query);
    }

}

?>
