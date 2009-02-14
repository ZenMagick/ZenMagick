<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
    private $requestHandler;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Wordpress', 'Allows to display Wordpress content in ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->requestHandler = null;
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
        $this->addConfigValue('Permalink Path Prefix', 'permaPrefix', '', 'Path prefix for Wordpress permalinks; leave empty if not using permalinks');
        $this->addConfigValue('WP enabled pages', 'wordpressEnabled', FILENAME_WP, 'Comma separated list of pages that can display WP content (leave empty for all).');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        $this->zcoSubscribe();

        // use API
        define('WP_USE_THEMES', false);

        // set up view mappings used by the wp controller
        $view = 'PageView';
        $parameter = 'subdir='.FILENAME_WP;
        if (ZMSettings::get('plugins.zm_wordpress.isUseOwnViews', false)) {
            $view = 'PluginView';
            $parameter = array('plugin' => $this, 'subdir' => FILENAME_WP);
        }

        ZMUrlMapper::instance()->setMapping(null, FILENAME_WP.'_index', 'index', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, FILENAME_WP.'_single', 'single', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, FILENAME_WP.'_page', 'page', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, FILENAME_WP.'_archive', 'archive', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, FILENAME_WP.'_archives', 'archives', $view, $parameter);
        ZMUrlMapper::instance()->setMapping(null, FILENAME_WP.'_search', 'search', $view, $parameter);
    }

    /**
     * Handle init done event.
     *
     * <p>Code in here can't be executed in <code>init()</code>, as it depends on the global
     * WP stuff being loaded first.</p>
     *
     * @param array args Optional event args.
     */
    public function onZMInitDone($args=null) {
        // create single request handler
        $this->requestHandler = ZMLoader::make('WpRequestHandler', $this);
        $wordpressEnabled = $this->get('wordpressEnabled');
        if ($this->initWP() && (empty($wordpressEnabled) || ZMTools::inArray(ZMRequest::getPageName(), $wordpressEnabled))) {
            // need to do this on all enabled pages, not just wp
            $this->requestHandler->handleRequest();
            $this->requestHandler->register();
        }
    }


    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $contents = $args['contents'];

        if (FILENAME_WP == ZMRequest::getPageName()) {
            ob_start();
            wp_head();
            $wp_head = ob_get_clean();
            $args['contents'] = preg_replace('/<\/head>/', $wp_head . '</head>', $contents, 1);
        }

        return $args;
    }

    /**
     * Check if permalinks are configured.
     *
     * @return boolean <code>true</code> if permalink support is enabled, <code>false</code> if not.
     */
    public function isPermalinksEnabled() {
        return !ZMTools::isEmpty($this->get('permaPrefix'));
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobal() {
        $wordpressEnabled = $this->get('wordpressEnabled');
        if (empty($wordpressEnabled) || ZMTools::inArray(ZMRequest::getPageName(), $wordpressEnabled)) {
            if ($this->isPermalinksEnabled()) {
                $path = ZMRuntime::getContext().$this->get('permaPrefix');
                if (false === strpos($_SERVER['REQUEST_URI'], '?')) {
                    // simulate empty query arg to make WP homepage work
                    $_SERVER['REQUEST_URI'] .= '?';
                }
                // make WP permalink parsing work
                $_SERVER['REQUEST_URI'] = str_replace($path, '', $_SERVER['REQUEST_URI']);
            }
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

        if ($this->initWP() && !$this->isPermalinksEnabled()) {
            $wpdb->select(DB_NAME);
            query_posts($query);
        }
    }

    /**
     * Check if wp is available.
     *
     * @return boolean <code>true</code> if Wordpress is initialized.
     */
    public function initWP() {
    global $wpdb;

        return isset($wpdb);
    }

    /**
     * Get the request handler.
     *
     * @return WpRequestHandler The single request handler for this request.
     */
    public function getRequestHandler() {
        return $this->requestHandler;
    }

}

?>
