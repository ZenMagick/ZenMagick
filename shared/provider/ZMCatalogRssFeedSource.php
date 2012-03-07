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

use zenmagick\base\ZMObject;
use zenmagick\http\rss\RssChannel;
use zenmagick\http\rss\RssFeed;
use zenmagick\http\rss\RssItem;
use zenmagick\http\rss\RssSource;

/**
 * RSS source to create a full catalog feed.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.provider
 */
class ZMCatalogRssFeedSource extends ZMObject implements RssSource {

    /**
     * {@inheritDoc}
     */
    public function getFeed($request, $channel, $args=array()) {
        if ('catalog' != $channel) {
            return null;
        }

        $key = array_key_exists('key', $args) ? $args['key'] : null;

        if (null == $key) {
            // do both categories and products
            $key = 'catalog';
        }

        $method = "get".ucwords($key)."Feed";
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
     * Generate RSS feed for the whole catalog (categories plus products).
     *
     * @param ZMRequest request The current request.
     * @return RssFeed The feed.
     */
    protected function getCatalogFeed($request) {
        $categoriesFeed = $this->getCategoriesFeed($request, true);
        $productsFeed = $this->getProductsFeed($request, true);

        $lastPubDate = $categoriesFeed->getLastBuildDate();
        if ($productsFeed->getLastBuildDate() > $lastPubDate) {
            $lastPubDate = $productsFeed->getLastBuildDate();
        }

        $channel = new RssChannel();
        $channel->setTitle(sprintf(_zm("%s Catalog"), ZMSettings::get('storeName')));
        $channel->setLink($request->url('index'));
        $channel->setDescription(sprintf(_zm("All categories and products at %s"), ZMSettings::get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $items = array_merge($categoriesFeed->getItems(), $productsFeed->getItems());

        $feed = new RssFeed();
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

    /**
     * Generate RSS feed for all products.
     *
     * @param ZMRequest request The current request.
     * @param boolean isCatalog Indicates whether the call is part of the catalog feed or not; default is <code>false</code>.
     * @return RssFeed The feed.
     * @todo add support for attributes
     */
    protected function getProductsFeed($request, $isCatalog=false) {
        $lastPubDate = null;
        $items = array();
        foreach ($this->container->get('productService')->getAllProducts(true, $request->getSession()->getLanguageId()) as $product) {
            $item = new RssItem();
            $item->setTitle($product->getName());
            $item->setLink($request->getToolbox()->net->product($product->getId(), null, false));
            $desc = ZMHtmlUtils::strip($product->getDescription());
            if (!$isCatalog) {
                $desc = ZMHtmlUtils::more($desc, 60);
            }
            $item->setDescription($desc);
            $item->setPubDate($product->getDateAdded());

            $tags = array('category', 'model');
            $item->set('category', $product->getDefaultCategory()->getId());
            $item->set('model', $product->getModel());

            if ($isCatalog) {
                $tags[] = 'price';
                $offers = $product->getOffers();
                $item->set('price', $offers->getCalculatedPrice());
                $tags[] = 'type';
                $item->set('type', 'product');
            }

            $item->setTags($tags);
            $items[] = $item;

            // make newest product
            if (null === $lastPubDate || $lastPubDate < $product->getLastModified()) {
                $lastPubDate = $product->getLastModified();
            }
        }

        $channel = new RssChannel();
        $channel->setTitle(sprintf(_zm("Products at %s"), ZMSettings::get('storeName')));
        $channel->setLink($request->url('index'));
        $channel->setDescription(sprintf(_zm("All products at %s"), ZMSettings::get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = new RssFeed();
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

    /**
     * Generate RSS feed for all categories.
     *
     * @param ZMRequest request The current request.
     * @param boolean isCatalog Indicates whether the call is part of the catalog feed or not; default is <code>false</code>.
     * @return RssFeed The feed.
     */
    protected function getCategoriesFeed($request, $isCatalog) {
        $lastPubDate = null;
        $items = array();
        foreach ($this->container->get('categoryService')->getAllCategories($request->getSession()->getLanguageId()) as $category) {
            if ($category->isActive()) {
                $item = new RssItem();
                $item->setTitle($category->getName());
                $item->setLink($request->url('category', $category->getPath(), false));
                $desc = ZMHtmlUtils::strip($category->getDescription());
                if (!$isCatalog) {
                    $desc = ZMHtmlUtils::more($desc, 60);
                }
                $item->setDescription($desc);
                $item->setPubDate($category->getDateAdded());
                $tags = array('id', 'path', 'children');
                $item->set('id', $category->getId());
                $item->set('path', implode('_', $category->getPathArray()));
                $item->set('children', array('id' => $category->getDecendantIds(false)));

                if ($isCatalog) {
                    $tags[] = 'type';
                    $item->set('type', 'category');
                }

                $item->setTags($tags);
                $items[] = $item;

                // make newest product
                if (null === $lastPubDate || $lastPubDate < $category->getLastModified()) {
                    $lastPubDate = $category->getLastModified();
                }
            }
        }

        $channel = new RssChannel();
        $channel->setTitle(sprintf(_zm("Categories at %s"), ZMSettings::get('storeName')));
        $channel->setLink($request->url('index'));
        $channel->setDescription(sprintf(_zm("All categories at %s"), ZMSettings::get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = new RssFeed();
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

}
