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


define('ZM_PLUGINS_PAGE_CACHE_ALLOWED_DEFAULT', 'index,category,product_info,page,static,products_new,featured_products,specials,product_reviews');
define('ZM_EVENT_PLUGINS_PAGE_CACHE_CONTENTS_DONE', 'plugins_page_cache_contents_done');


/**
 * Plugin for page caching.
 *
 * <p><strong>Note:</strong> If a cache hit is found, this plugin will effectively terminate request handling at that point.
 * Therefore it is essential that this plugin is configured to be the last to run.</p>
 *
 * @package org.zenmagick.plugins.pageCache
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMPageCachePlugin extends \Plugin {
    private $cache_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Page Cache', 'ZenMagick page caching', '${plugin.version}');
        $this->setContext('admin,storefront');
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
        $this->cache_ = Runtime::getContainer()->get('persistentCache');
        $this->cache_->setOption('cacheTTL', $this->get('ttl'));
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
        $fkt = \ZMSettings::get('plugins.pageCache.strategy.callback', array($this, 'defaultStrategy'));
        $val = false;
        if (is_callable($fkt)) {
            $val = call_user_func($fkt, $request);
        } else if (is_string($fkt) && function_exists($fkt)) {
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
                Runtime::getLogging()->debug('cache hit for requestId: '.$request->getRequestId());
                echo $contents;
                Runtime::getEventDispatcher()->dispatch(ZM_EVENT_PLUGINS_PAGE_CACHE_CONTENTS_DONE, new Event($this, $event->all()));
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
        $content = $event->get('content');

        if ($this->isEnabled() && $this->isCacheable($request)) {
            $this->cache_->save($content, $this->getRequestKey($request));
        }

        return null;
    }

    /**
     * Default caching strategy for page caching.
     *
     * <p>The strategy is as follows:</p>
     * <ol>
     *  <li>The request is not a <em>POST</em> request</li>
     *  <li>The shoppingcart is empty</li>
     *  <li>There are no messages that need to be  displayed</li>
     *  <li>The request's page name parameter is in the list of configured opt-in pages</li>
     * </ol>
     *
     * @package org.zenmagick.plugins.pageCache
     * @return boolean <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    protected function defaultStrategy($request) {
        return 'POST' != $request->getMethod()
          && (null == $request->getShoppingCart() || $request->getShoppingCart()->isEmpty())
          && !$this->container->get('messageService')->hasMessages()
          && ZMLangUtils::inArray($request->getRequestId(), ZMSettings::get('plugins.pageCache.strategy.allowed', ZM_PLUGINS_PAGE_CACHE_ALLOWED_DEFAULT));
    }

}
