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

define('ZM_EVENT_PLUGINS_PAGE_CACHE_STATS', 'plugins_page_cache_stats');


/**
 * Plugin for page caching.
 *
 * <p><strong>Note:</strong> If a cache hit is found, this plugin will effectively terminate request handling at that point.
 * Therefore it is essential that this plugin is configured to be the last to run.</p>
 *
 * @package org.zenmagick.plugins.zm_page_cache
 * @author DerManoMann
 * @version $Id$
 */
class zm_page_cache extends Plugin {
    private $pageCache_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Page Cache', 'ZenMagick page caching', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setPreferredSortOrder(9999);
        $this->pageCache_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    public function init() {
        parent::init();

        $this->zcoSubscribe();

        $config = array('cacheTTL' => ZMSettings::get('plugins.zm_page_cache.ttl', 300));

        // get one now to make cache admin work
        $this->pageCache_ = ZMCaches::instance()->getCache('pages', $config);
    }


    /**
     * Create unique cache key for the context of the current request.
     *
     * <p>Depending on whether the user is logged in or not, a user 
     * specifc keu will be generated, to avoid session leaks.</p>
     *
     * @return string A cache id.
     */
    function getRequestKey() {
        $session = ZMRequest::getSession();
        return ZMRequest::getPageName() . '-' . ZMRequest::getQueryString() . '-' . ZMRequest::getAccountId() . '-' . 
                  $session->getLanguageId() . '-' . Runtime::getThemeId();
    }

    /**
     * Controls if the current page is cacheable or not.
     *
     * <p>Evaluation is delegated to the configured strategy function (<em>pageCacheStrategyCallback</em>).</p>
     *
     * @return boolean <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    function isCacheable() {
        $fkt = ZMSettings::get('plugins.zm_page_cache.strategy.callback', 'zm_page_cache_default_strategy');
        $val = false;
        if (function_exists($fkt)) {
            $val = $fkt();
        }

        return $val;
    }


    /**
     * Theme resolved event handler.
     *
     * <p>This is the point during request handling where it is decided whether to
     * use the page cache or not.</p>
     *
     * @param array args Contains the final theme (key: 'theme').
     */
    public function onZMThemeResolved($args) {
        // handle page caching
        if ($this->isEnabled()) {
            if (false !== ($contents = $this->pageCache_->lookup($this->getRequestKey())) && $this->isCacheable()) {
                echo $contents;
                if (ZMSettings::get('plugins.zm_page_cache.stats', true)) {
                    ZMEvents::instance()->fireEvent($this, ZM_EVENT_PLUGINS_PAGE_CACHE_STATS);
                    echo '<!-- zm_page_cache stats: page: ' . Runtime::getExecutionTime() . ' sec.; ';
                    echo 'lastModified: ' . $this->pageCache_->lastModified() . ' -->';
                }
                require('includes/application_bottom.php');
                exit;
            }
            if (false !== $contents) {
                $this->pageCache_->remove($this->getRequestKey());
            }
        }
    }


    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $contents = $args['contents'];

        if ($this->isEnabled() && $this->isCacheable()) {
            $this->pageCache_->save($contents, $this->getRequestKey());
        }

        return null;
    }

}

?>
