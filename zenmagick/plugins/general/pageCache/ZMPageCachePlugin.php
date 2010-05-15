<?php
/*
 * ZenMagick - Extensions for zen-cart
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

define('ZM_EVENT_PLUGINS_PAGE_CACHE_STATS', 'plugins_page_cache_stats');


/**
 * Plugin for page caching.
 *
 * <p><strong>Note:</strong> If a cache hit is found, this plugin will effectively terminate request handling at that point.
 * Therefore it is essential that this plugin is configured to be the last to run.</p>
 *
 * @package org.zenmagick.plugins.pageCache
 * @author DerManoMann
 * @version $Id$
 */
class ZMPageCachePlugin extends Plugin {
    private $cache_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Page Cache', 'ZenMagick page caching', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setContext(Plugin::CONTEXT_STOREFRONT|Plugin::CONTEXT_ADMIN);
        $this->setPreferredSortOrder(9999);
        $this->cache_ = null;
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
        ZMEvents::instance()->attach($this);
        $config = array('cacheTTL' => ZMSettings::get('plugins.pageCache.ttl', 300));
        $this->cache_ = ZMCaches::instance()->getCache('pages', $config);
    }


    /**
     * Create unique cache key for the context of the current request.
     *
     * <p>Depending on whether the user is logged in or not, a user 
     * specifc keu will be generated, to avoid session leaks.</p>
     *
     * @param ZMRequest request The current request.
     * @return string A cache id.
     */
    protected function getRequestKey($request) {
        $session = $request->getSession();
        $parameters = $request->getParameterMap();
        ksort($parameters);
        return $request->getRequestId() . '-' . http_build_query($parameters) . '-' . $request->getAccountId() . '-' . 
                  $session->getLanguageId() . '-' . Runtime::getThemeId();
    }

    /**
     * Controls if the current page is cacheable or not.
     *
     * <p>Evaluation is delegated to the configured strategy function (<em>pageCacheStrategyCallback</em>).</p>
     *
     * @param ZMRequest request The current request.
     * @return boolean <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    protected function isCacheable($request) {
        $fkt = ZMSettings::get('plugins.pageCache.strategy.callback', 'zm_page_cache_default_strategy');
        $val = false;
        if (function_exists($fkt)) {
            $val = $fkt($request);
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
        $request = $args['request'];

        // handle page caching
        if ($this->isEnabled() && !ZMSettings::get('isAdmin')) {
            if (false !== ($contents = $this->cache_->lookup($this->getRequestKey($request))) && $this->isCacheable($request)) {
                ZMLogging::instance()->log('cache hit for requestId: '.$request->getRequestId(), ZMLogging::DEBUG);
                echo $contents;
                if (ZMSettings::get('plugins.pageCache.stats', true)) {
                    ZMEvents::instance()->fireEvent($this, ZM_EVENT_PLUGINS_PAGE_CACHE_STATS, $args);
                    echo '<!-- pageCache stats: page: ' . Runtime::getExecutionTime() . ' sec.; ';
                    echo 'lastModified: ' . $this->cache_->lastModified() . ' -->';
                }
                exit;
            }
            if (false !== $contents) {
                $this->cache_->remove($this->getRequestKey($request));
            }
        }
    }


    /**
     * {@inheritDoc}
     */
    public function onZMAllDone($args) {
        $contents = $args['contents'];
        $request = $args['request'];

        if ($this->isEnabled() && $this->isCacheable($request)) {
            $this->cache_->save($contents, $this->getRequestKey($request));
        }

        return null;
    }

}
