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
namespace ZenMagick\StoreBundle\Rss;

use DateTime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Rss\RssChannel;
use ZenMagick\Http\Rss\RssFeed;
use ZenMagick\Http\Rss\RssSource;

/**
 * RSS source to create a full catalog feed.
 *
 * @author DerManoMann
 */
class CatalogRssFeedSource extends ZMObject implements RssSource
{
    protected $fullFeed;
    protected $multiCurrency;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->fullFeed = true;
        $this->multiCurrency = true;
    }

    /**
     * Set a flag to indicate whether to produce a full feed or not.
     *
     * @param boolean value The new value.
     */
    public function setFullFeed($value)
    {
        $this->fullFeed = $value;
    }

    /**
     * Check if a full feed needs to be generated.
     *
     * @return boolean <code>true</code> if a full feed should be generated.
     */
    public function isFullFeed()
    {
        return $this->fullFeed;
    }

    /**
     * Set a flag to indicate whether to generate pricing in multiple currencies.
     *
     * @param boolean value The new value.
     */
    public function setMultiCurrency($value)
    {
        $this->multiCurrency = $value;
    }

    /**
     * Check if the multi currency option is set.
     *
     * @return boolean <code>true</code> if a full feed should be generated.
     */
    public function isMultiCurrency()
    {
        return $this->multiCurrency;
    }

    /**
     * {@inheritDoc}
     */
    public function getFeed($request, $channel, $args=array())
    {
        if ('catalog' != $channel) {
            return null;
        }

        $key = array_key_exists('key', $args) ? $args['key'] : null;

        if (null == $key) {
            return null;
        }

        $method = "get".ucwords($key)."Feed";
        if (!method_exists($this, $method)) {
            return null;
        }

        // get feed data
        $feed = call_user_func(array($this, $method), $request, $this->fullFeed);
        if (null == $feed) {
            return null;
        }

        return $feed;
    }

    /**
     * Generate RSS feed for all products.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @param boolean full Indicates whether to generate a full feed or not; default is <code>false</code>.
     * @return RssFeed The feed.
     */
    protected function getProductsFeed($request, $full=false)
    {
        $settingsService = $this->container->get('settingsService');
        $net = $this->container->get('netTool');

        $channel = new RssChannel();
        $channel->setTitle(sprintf(_zm("Products at %s"), $settingsService->get('storeName')));
        $channel->setLink($net->url('index'));
        $channel->setDescription(sprintf(_zm("All products at %s"), $settingsService->get('storeName')));
        $channel->setLastBuildDate(new DateTime());

        $feed = new RssFeed();
        $feed->setChannel($channel);

        // set up item iterator
        $languageId = $request->getSession()->getLanguageId();
        $productService = $this->container->get('productService');
        // do not cache products for this
        $productService->setCache(null);
        $productInfo = array();
        foreach ($productService->getAllProductIds(true, $languageId) as $productId) {
            $productInfo[] = array('id' => $productId, 'url' => $net->product($productId, null, false));
        }
        $itemIterator = new CatalogProductRssItemIterator($productInfo, $languageId, $this->fullFeed, $this->multiCurrency);
        $itemIterator->setContainer($this->container);
        $feed->setItems($itemIterator);

        return $feed;
    }

    /**
     * Generate RSS feed for all categories.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @param boolean full Indicates whether to generate a full feed or not; default is <code>false</code>.
     * @return RssFeed The feed.
     */
    protected function getCategoriesFeed($request, $full)
    {
        $settingsService = $this->container->get('settingsService');
        $net = $this->container->get('netTool');
        $channel = new RssChannel();
        $channel->setTitle(sprintf(_zm("Categories at %s"), $settingsService->get('storeName')));
        $channel->setLink($net->url('index'));
        $channel->setDescription(sprintf(_zm("All categories at %s"), $settingsService->get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = new RssFeed();
        $feed->setChannel($channel);

        // set up item iterator
        $languageId = $request->getSession()->getLanguageId();
        $categoryService = $this->container->get('categoryService');
        //TODO: $categoryService->setCache(null);
        $categoryInfo = array();
        foreach ($categoryService->getAllCategories($languageId) as $category) {
            if ($category->isActive()) {
                $categoryInfo[] = array('id' => $category->getId(), 'url' => $net->url('category', 'cPath='.implode('_', $category->getPath()), false));
            }
        }
        $itemIterator = new CatalogCategoryRssItemIterator($categoryInfo, $languageId, $this->fullFeed);
        $itemIterator->setContainer($this->container);
        $feed->setItems($itemIterator);

        return $feed;
    }

}
