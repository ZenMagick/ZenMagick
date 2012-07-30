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
namespace zenmagick\apps\store\rss;

use ArrayIterator;
use DateTime;
use zenmagick\base\ZMObject;
use zenmagick\http\rss\RssChannel;
use zenmagick\http\rss\RssFeed;
use zenmagick\http\rss\RssItem;
use zenmagick\http\rss\RssSource;

/**
 * RSS source for default feeds.
 *
 * @author DerManoMann
 */
class DefaultRssFeedSource extends ZMObject implements RssSource {

    /**
     * {@inheritDoc}
     */
    public function getFeed($request, $channel, $args=array()) {
        // delegate items to channel method
        $method = "get".ucwords($channel)."Feed";
        if (!method_exists($this, $method)) {
            return null;
        }

        $key = array_key_exists('key', $args) ? $args['key'] : null;
        // get feed data
        $feed = call_user_func(array($this, $method), $request, $key);
        if (null == $feed) {
            return null;
        }

        return $feed;
    }

    /**
     * Generate RSS feed for reviews.
     *
     * @param zenmagick\http\Request request The current request.
     * @param string key Optional product id.
     * @return RssFeed The feed.
     */
    protected function getReviewsFeed($request, $key=null) {
        $product = null;
        $reviewService = $this->container->get('reviewService');
        $languageId = $request->getSession()->getLanguageId();
        if (null != $key)  {
            $reviews = array_reverse($reviewService->getReviewsForProductId($key, $languageId));
            $product = $this->container->get('productService')->getProductForId($key, $languageId);
        } else {
            $reviews = array_reverse($reviewService->getAllReviews($languageId));
        }
        if (null != $key && null == $product) {
            return null;
        }

        $items = array();
        $html = $this->container->get('htmlTool');
        $lastPubDate = null;
        foreach ($reviews as $review) {
            if (null == $key) {
                $product = $this->container->get('productService')->getProductForId($review->getProductId());
            }
            $item = new RssItem();
            $item->setTitle(sprintf(_zm("Review: %s"), $product->getName()));

            $params = 'productId='.$review->getProductId().'&reviews_id='.$review->getId();
            $item->setLink($request->url('product_reviews_info', $params));
            $item->setDescription($html->more($review->getText(), 60));
            $item->setPubDate($review->getDateAdded());
            $items[] = $item;

            if (null === $lastPubDate) {
                $lastPubDate = $review->getDateAdded();
            }
        }

        $settingsService = $this->container->get('settingsService');
        $channel = new RssChannel();
        $channel->setTitle(_zm("Product Reviews"));
        $channel->setLink($request->url('reviews'));
        if (null != $key)  {
            $channel->setDescription(sprintf(_zm("Product Reviews for %s at %s"), $product->getName(), $settingsService->get('storeName')));
        } else {
            $channel->setDescription(sprintf(_zm("Product Reviews at %s"), $settingsService->get('storeName')));
        }
        $channel->setLastBuildDate($lastPubDate);

        $feed = new RssFeed();
        $feed->setChannel($channel);
        $feed->setItems(new ArrayIterator($items));

        return $feed;
    }

    /**
     * Generate RSS feed for EZPages chapter.
     *
     * @param zenmagick\http\Request request The current request.
     * @param string key EZPages chapter.
     * @return RssFeed The feed data.
     */
    protected function getChapterFeed($request, $key=null) {
        $items = array();
        $toc = $this->container->get('ezPageService')->getPagesForChapterId($key, $request->getSession()->getLanguageId());
        foreach ($toc as $page) {
            $item = new RssItem();
            $item->setTitle($page->getTitle());
            $item->setLink($request->getToolbox()->net->ezPage($page));
            $item->setDescription($page->getTitle());
            $items[] = $item;
        }

        $channel = new RssChannel();
        $channel->setTitle(sprintf(_zm("Chapter %s"), $key));
        $channel->setLink($request->url('page', 'chapter='.$key));
        $channel->setDescription(sprintf(_zm("All pages of Chapter %s"), $key));
        $channel->setLastBuildDate(new DateTime());

        $feed = new RssFeed();
        $feed->setChannel($channel);
        $feed->setItems(new ArrayIterator($items));

        return $feed;
    }

    /**
     * Generate RSS feed for products.
     *
     * @param zenmagick\http\Request request The current request.
     * @param string key Optional key value for various product types; supported: 'new'
     * @return RssFeed The feed data.
     */
    protected function getProductsFeed($request, $key=null) {
        if ('new' != $key) {
            return null;
        }

        $toolbox = $request->getToolbox();
        $lastPubDate = null;
        $items = array();
        $products = array_slice(array_reverse($this->container->get('productService')->getNewProducts()), 0, 20);
        foreach ($products as $product) {
            $item = new RssItem();
            $item->setTitle($product->getName());
            $item->setLink($toolbox->net->product($product->getId(), null, false));
            $item->setDescription($toolbox->html->more($toolbox->html->strip($product->getDescription()), 60));
            $item->setPubDate($product->getDateAdded());
            $items[] = $item;

            if (null === $lastPubDate) {
                $lastPubDate = $product->getDateAdded();
            }
        }

        $settingsService = $this->container->get('settingsService');
        $channel = new RssChannel();
        $channel->setTitle(sprintf(_zm("New Products at %s"), $settingsService->get('storeName')));
        $channel->setLink($request->url('index'));
        $channel->setDescription(sprintf(_zm("The latest updates to %s's product list"), $settingsService->get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = new RssFeed();
        $feed->setChannel($channel);
        $feed->setItems(new ArrayIterator($items));

        return $feed;
    }

}
