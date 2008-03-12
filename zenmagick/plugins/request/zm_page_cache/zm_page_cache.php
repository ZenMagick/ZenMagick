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
 * Plugin for page caching.
 *
 * <p><strong>Note:</strong> If a cache hit is found, this plugin will effectively terminate request handling at that point.
 * Therefore it is essential that this plugin is configured to be the last to run.</p>
 *
 * @package org.zenmagick.plugins.zm_page_cache
 * @author DerManoMann
 * @version $Id$
 */
class zm_page_cache extends ZMPlugin {
    private $pageCache_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Page Cache', 'ZenMagick page caching', '${plugin.version}');
        $this->setLoaderSupport('ALL');
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
    function init() {
        parent::init();

        $this->zcoSubscribe();

        $config = array(
            'pageCacheTTL' => 300, // in sec.
        );
        foreach ($config as $key => $value) {
            if (null !== zm_setting($key)) {
                $config[$key] = zm_setting($key);
            }
        }

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
    global $zm_request, $zm_theme;

        $session = ZMRequest::getSession();
        return ZMRequest::getPageName() . '-' . ZMRequest::getQueryString() . '-' . ZMRequest::getAccountId() . '-' . $session->getLanguageId() . '-' . $zm_theme->getThemeId();
    }

    /**
     * Controls if the current page is cacheable or not.
     *
     * <p>Evaluation is delegated to the configured strategy function (<em>pageCacheStrategyCallback</em>).</p>
     *
     * @return boolean <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    function isCacheable() {
        $callback = zm_setting('pageCacheStrategyCallback');
        if (function_exists($callback)) {
            return $callback();
        }

        return zm_page_cache_request_cacheable();
    }


    /**
     * Theme resolved event handler.
     *
     * <p>This is the point during request handling where it is decided whether to
     * use the page cache or not.</p>
     *
     * @param array args Contains the final theme (key: 'theme').
     */
    function onZMThemeResolved($args) {
    global $zm_theme;

        // handle page caching
        if ($this->isEnabled()) {
            // need $zm_theme for PageCache::getId()
            $zm_theme = $args['theme'];
            if ($this->isCacheable() && $contents = $this->pageCache_->get($this->getRequestKey())) {
                if (!zm_eval_if_modified_since($this->pageCache_->lastModified())) {
                    echo $contents;
                    if (zm_setting('isDisplayTimerStats')) {
                        $db = ZMRuntime::getDB();
                        echo '<!-- zm_page_cache stats: ' . round($db->queryTime(), 4) . ' sec. for ' . $db->queryCount() . ' queries; ';
                        echo 'page: ' . ZMRuntime::getExecutionTime() . ' sec.; ';
                        echo 'lastModified: ' . $this->pageCache_->lastModified() . ' -->';
                    }
                }
                require('includes/application_bottom.php');
                exit;
            }
        }
    }


    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function filterResponse($contents) {
        if ($this->isEnabled() && $this->isCacheable()) {
            $this->pageCache_->save($contents, $this->getRequestKey());
        }

        return $contents;
    }

}

?>
