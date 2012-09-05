<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\plugins\pageCache;

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Events\Event;


define('ZM_PLUGINS_PAGE_CACHE_ALLOWED_DEFAULT', 'index,category,product_info,page,static,products_new,featured_products,specials,product_reviews');
define('ZM_EVENT_PLUGINS_PAGE_CACHE_CONTENTS_DONE', 'plugins_page_cache_contents_done');


/**
 * Plugin for page caching.
 *
 * <p><strong>Note:</strong> If a cache hit is found, this plugin will effectively terminate request handling at that point.
 * Therefore it is essential that this plugin is configured to be the last to run.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PageCachePlugin extends Plugin {
    private $cache_ = null;
    protected $activeThemeId;

    /**
     * Set up cache.
     */
    public function onContainerReady($event) {
        // TODO: we can't just change the TTL of an existing cache!
        $this->cache_ = Runtime::getContainer()->get('persistentCache');
        $this->cache_->setOption('cacheTTL', $this->get('ttl'));
    }


    /**
     * Create unique cache key for the context of the current request.
     *
     * <p>Depending on whether the user is logged in or not, a user
     * specifc keu will be generated, to avoid session leaks.</p>
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return string A cache id.
     */
    protected function getRequestKey($request) {
        $session = $request->getSession();
        $parameters = $request->query->all();
        ksort($parameters);
        $accountId = $request->getAccount() ? $request->getAccount()->getId() : 0;
        return $request->getRequestId() . '-' . http_build_query($parameters) . '-' . $accountId . '-' .
                  $session->getLanguageId() . '-' . $this->activeThemeId;
    }

    /**
     * Controls if the current page is cacheable or not.
     *
     * <p>Evaluation is delegated to the configured strategy function (<em>pageCacheStrategyCallback</em>).</p>
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return boolean <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    protected function isCacheable($request) {
        $fkt = $this->container->get('settingsService')->get('plugins.pageCache.strategy.callback', array($this, 'defaultStrategy'));
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
        $this->activeThemeId = $event->get('themeId');
        $request = $event->get('request');

        // handle page caching
        if ($this->isEnabled() && Runtime::isContextMatch('storefront')) {
            if (false !== ($contents = $this->cache_->lookup($this->getRequestKey($request))) && $this->isCacheable($request)) {
                Runtime::getLogging()->debug('cache hit for requestId: '.$request->getRequestId());
                echo $contents;
                $event->getDispatcher()->dispatch(ZM_EVENT_PLUGINS_PAGE_CACHE_CONTENTS_DONE, new Event($this, $event->all()));
                if ($this->get('loadStats')) {
                    $time = round(microtime(true) - $this->container->get('kernel')->getStartTime(), 4);
                    echo '<!-- pageCache stats: page: ' . $time . ' sec.; ';
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
          && !$request->getSession()->getFlashBag()->hasMessages()
          && in_array($request->getRequestId(), $this->container->get('settingsService')->get('plugins.pageCache.strategy.allowed', explode(',', ZM_PLUGINS_PAGE_CACHE_ALLOWED_DEFAULT)));
    }

}
