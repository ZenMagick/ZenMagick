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

use ArrayIterator;
use DateTime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Rss\RssChannel;
use ZenMagick\Http\Rss\RssFeed;
use ZenMagick\Http\Rss\RssItem;
use ZenMagick\Http\Rss\RssSource;

/**
 * RSS source for default feeds.
 *
 * @author DerManoMann
 */
class DefaultRssFeedSource extends ZMObject implements RssSource
{
    /**
     * {@inheritDoc}
     */
    public function getFeed($request, $channel, $args=array())
    {
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
     * @param ZenMagick\Http\Request request The current request.
     * @param string key Optional product id.
     * @return RssFeed The feed.
     */
    protected function getReviewsFeed($request, $key=null)
    {
        $product = null;
        $reviewService = $this->container->get('reviewService');
        $languageId = $request->getSession()->getLanguageId();
        if (null != $key) {
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
        $net = $this->container->get('netTool');
        $lastPubDate = null;
        foreach ($reviews as $review) {
            if (null == $key) {
                $product = $this->container->get('productService')->getProductForId($review->getProductId());
            }
            $item = new RssItem();
            $item->setTitle(sprintf(_zm("Review: %s"), $product->getName()));

            $params = array('productId' => $review->getProductId(), 'reviews_id' => $review->getId());
            $item->setLink($net->url('product_reviews_info', $params));
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
        $channel->setLink($net->url('reviews'));
        if (null != $key) {
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
     * @param ZenMagick\Http\Request request The current request.
     * @param string key EZPages chapter.
     * @return RssFeed The feed data.
     */
    protected function getChapterFeed($request, $key=null)
    {
        $items = array();
        $toc = $this->container->get('ezPageService')->getPagesForChapterId($key, $request->getSession()->getLanguageId());
        $net = $this->container->get('netTool');
        foreach ($toc as $page) {
            $item = new RssItem();
            $item->setTitle($page->getTitle());
            $item->setLink($net->ezPage($page));
            $item->setDescription($page->getTitle());
            $items[] = $item;
        }

        $channel = new RssChannel();
        $channel->setTitle(sprintf(_zm("Chapter %s"), $key));
        $channel->setLink($net->url('page', array('chapter' => $key)));
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
     * @param ZenMagick\Http\Request request The current request.
     * @param string key Optional key value for various product types; supported: 'new'
     * @return RssFeed The feed data.
     */
    protected function getProductsFeed($request, $key=null)
    {
        if ('new' != $key) {
            return null;
        }

        $toolbox = $this->container->get('toolbox');
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
        $channel->setLink($toolbox->net->url('index'));
        $channel->setDescription(sprintf(_zm("The latest updates to %s's product list"), $settingsService->get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = new RssFeed();
        $feed->setChannel($channel);
        $feed->setItems(new ArrayIterator($items));

        return $feed;
    }

}
