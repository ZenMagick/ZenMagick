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

use zenmagick\base\Beans;

/**
 * RSS source for default feeds.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.provider
 */
class ZMDefaultRssFeedSource implements ZMRssSource {

    /**
     * {@inheritDoc}
     */
    public function getFeed($request, $channel, $key=null) {
        // delegate items to channel method
        $method = "get".ucwords($channel)."Feed";
        if (!method_exists($this, $method)) {
            return null;
        }


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
     * @param ZMRequest request The current request.
     * @param string key Optional product id.
     * @return ZMRssFeed The feed data.
     */
    protected function getReviewsFeed($request, $key=null) {
        $product = null;
        $languageId = $request->getSession()->getLanguageId();
        if (null != $key)  {
            $reviews = array_reverse(ZMReviews::instance()->getReviewsForProductId($key, $languageId));
            $product = ZMProducts::instance()->getProductForId($key, $languageId);
        } else {
            $reviews = array_reverse(ZMReviews::instance()->getAllReviews($languageId));
        }
        if (null != $key && null == $product) {
            return null;
        }

        $items = array();
        $lastPubDate = null;
        foreach ($reviews as $review) {
            if (null == $key) {
                $product = ZMProducts::instance()->getProductForId($review->getProductId());
            }
            $item = Beans::getBean("ZMRssItem");
            $item->setTitle(sprintf(_zm("Review: %s"), $product->getName()));

            $params = 'products_id='.$review->getProductId().'&reviews_id='.$review->getId();
            $item->setLink($request->url('product_reviews_info', $params));
            $item->setDescription(ZMHtmlUtils::more($review->getText(), 60));
            $item->setPubDate(ZMRssUtils::mkRssDate($review->getDateAdded()));
            array_push($items, $item);

            if (null === $lastPubDate) {
                $lastPubDate = $review->getDateAdded();
            }
        }

        $channel = Beans::getBean("ZMRssChannel");
        $channel->setTitle(_zm("Product Reviews"));
        $channel->setLink($request->url('index'));
        if (null != $key)  {
            $channel->setDescription(sprintf(_zm("Product Reviews for %s at %s"), $product->getName(), ZMSettings::get('storeName')));
        } else {
            $channel->setDescription(sprintf(_zm("Product Reviews at %s"), ZMSettings::get('storeName')));
        }
        $channel->setLastBuildDate(ZMRssUtils::mkRssDate($lastPubDate));

        $feed = Beans::getBean("ZMRssFeed");
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

    /**
     * Generate RSS feed for EZPages chapter.
     *
     * @param ZMRequest request The current request.
     * @param string key EZPages chapter.
     * @return ZMRssFeed The feed data.
     */
    protected function getChapterFeed($request, $key=null) {
        $items = array();
        $toc = ZMEZPages::instance()->getPagesForChapterId($key, $request->getSession()->getLanguageId());
        foreach ($toc as $page) {
            $item = Beans::getBean("ZMRssItem");
            $item->setTitle($page->getTitle());
            $item->setLink($request->getToolbox()->net->ezPage($page));
            $item->setDescription($page->getTitle());
            array_push($items, $item);
        }

        $channel = Beans::getBean("ZMRssChannel");
        $channel->setTitle(sprintf(_zm("Chapter %s"), $key));
        $channel->setLink($request->url('index'));
        $channel->setDescription(sprintf(_zm("All pages of Chapter %s"), $key));
        $channel->setLastBuildDate(ZMRssUtils::mkRssDate());

        $feed = Beans::getBean("ZMRssFeed");
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

    /**
     * Generate RSS feed for products.
     *
     * @param ZMRequest request The current request.
     * @param string key Optional key value for various product types; supported: 'new'
     * @return ZMRssFeed The feed data.
     */
    protected function getProductsFeed($request, $key=null) {
        if ('new' != $key) {
            return null;
        }

        $lastPubDate = null;
        $items = array();
        $products = array_slice(array_reverse(ZMProducts::instance()->getNewProducts()), 0, 20);
        foreach ($products as $product) {
            $item = Beans::getBean("ZMRssItem");
            $item->setTitle($product->getName());
            $item->setLink($request->getToolbox()->net->product($product->getId(), null, false));
            $item->setDescription(ZMHtmlUtils::more(ZMHtmlUtils::strip($product->getDescription()), 60));
            $item->setPubDate(ZMRssUtils::mkRssDate($product->getDateAdded()));
            array_push($items, $item);

            if (null === $lastPubDate) {
                $lastPubDate = $product->getDateAdded();
            }
        }

        $channel = Beans::getBean("ZMRssChannel");
        $channel->setTitle(sprintf(_zm("New Products at %s"), ZMSettings::get('storeName')));
        $channel->setLink($request->url('index'));
        $channel->setDescription(sprintf(_zm("The latest updates to %s's product list"), ZMSettings::get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = Beans::getBean("ZMRssFeed");
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

}
