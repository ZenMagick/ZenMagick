<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;


define('ZM_EVENT_PLUGINS_PAGE_CACHE_CONTENTS_DONE', 'plugins_page_cache_contents_done');


/**
 * Plugin for page caching.
 *
 * <p><strong>Note:</strong> If a cache hit is found, this plugin will effectively terminate request handling at that point.
 * Therefore it is essential that this plugin is configured to be the last to run.</p>
 *
 * @package org.zenmagick.plugins.pageCache
 * @author DerManoMann
 */
class ZMPageCachePlugin extends \Plugin {
    private $cache_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Page Cache', 'ZenMagick page caching', '${plugin.version}');
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
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Cache TTL', 'ttl', 300, '(T)ime (T)o (L)ive for cache entries in seconds.');
        $this->addConfigValue('Load stats', 'loadStats', 'false', 'If set to true, add some hidden (HTML comment) page load stats',
            'widget@ZMBooleanFormWidget#name=loadStats&default=false&label=Add hidden page load stats.');
    }

    /**
     * Init this plugin.
     */
    public function init() {
        parent::init();
        Runtime::getEventDispatcher()->listen($this);
        $this->cache_ = \ZMCaches::instance()->getCache('pages', array('cacheTTL' => $this->get('ttl')));

        // TODO: manually load lib for now
        require_once dirname(__FILE__).'/lib/defaults.php';
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
                  $session->getLanguageId() . '-' . \ZMThemes::instance()->getActiveThemeId($session->getLanguageId());
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
        $fkt = \ZMSettings::get('plugins.pageCache.strategy.callback', 'zm_page_cache_default_strategy');
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
     */
    public function onThemeResolved($event) {
        $request = $event->get('request');

        // handle page caching
        if ($this->isEnabled() && !\ZMSettings::get('isAdmin')) {
            if (false !== ($contents = $this->cache_->lookup($this->getRequestKey($request))) && $this->isCacheable($request)) {
                \ZMLogging::instance()->log('cache hit for requestId: '.$request->getRequestId(), \ZMLogging::DEBUG);
                echo $contents;
                Runtime::getEventDispatcher()->dispatch(ZM_EVENT_PLUGINS_PAGE_CACHE_CONTENTS_DONE, new Event($this, $args));
                if ($this->get('loadStats')) {
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
     * Event handler
     */
    public function onAllDone($event) {
        $request = $event->get('request');
        $contents = $event->get('contents');

        if ($this->isEnabled() && $this->isCacheable($request)) {
            $this->cache_->save($contents, $this->getRequestKey($request));
        }

        return null;
    }

}
