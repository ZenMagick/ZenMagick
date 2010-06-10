<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * RSS source to create a full catalog feed.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.provider
 */
class ZMCatalogRssFeedSource implements ZMRssSource {

    /**
     * {@inheritDoc}
     */
    public function getFeed($request, $channel, $key=null) {
        if ('catalog' != $channel) {
            return null;
        }

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
     * @return ZMRssFeed The feed.
     */
    protected function getCatalogFeed($request) {
        $categoriesFeed = $this->getCategoriesFeed($request, true);
        $productsFeed = $this->getProductsFeed($request, true);

        $lastPubDate = $categoriesFeed->getLastBuildDate();
        if ($productsFeed->getLastBuildDate() > $lastPubDate) {
            $lastPubDate = $productsFeed->getLastBuildDate();
        }

        $channel = ZMLoader::make("RssChannel");
        $channel->setTitle(zm_l10n_get("%s Catalog", ZMSettings::get('storeName')));
        $channel->setLink($request->url(FILENAME_DEFAULT));
        $channel->setDescription(zm_l10n_get("All categories and products at %s", ZMSettings::get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $items = array_merge($categoriesFeed->getItems(), $productsFeed->getItems());

        $feed = ZMLoader::make("RssFeed");
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

    /**
     * Generate RSS feed for all products.
     *
     * @param ZMRequest request The current request.
     * @param boolean isCatalog Indicates whether the call is part of the catalog feed or not; default is <code>false</code>.
     * @return ZMRssFeed The feed.
     */
    protected function getProductsFeed($request, $isCatalog=false) {
        $lastPubDate = null;
        $items = array();
        foreach (ZMProducts::instance()->getAllProducts(true, $request->getSession()->getLanguageId()) as $product) {
            $item = ZMLoader::make("RssItem");
            $item->setTitle($product->getName());
            $item->setLink($request->getToolbox()->net->product($product->getId(), null, false));
            $desc = ZMHtmlUtils::strip($product->getDescription());
            if (!$isCatalog) {
                $desc = ZMHtmlUtils::more($desc, 60);
            }
            $item->setDescription($desc);
            $item->setPubDate(ZMRssUtils::mkRssDate($product->getDateAdded()));

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

        $channel = ZMLoader::make("RssChannel");
        $channel->setTitle(zm_l10n_get("Products at %s", ZMSettings::get('storeName')));
        $channel->setLink($request->url(FILENAME_DEFAULT));
        $channel->setDescription(zm_l10n_get("All products at %s", ZMSettings::get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = ZMLoader::make("RssFeed");
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

    /**
     * Generate RSS feed for all categories.
     *
     * @param ZMRequest request The current request.
     * @param boolean isCatalog Indicates whether the call is part of the catalog feed or not; default is <code>false</code>.
     * @return ZMRssFeed The feed.
     */
    protected function getCategoriesFeed($request, $isCatalog) {
        $lastPubDate = null;
        $items = array();
        foreach (ZMCategories::instance()->getAllCategories($request->getSession()->getLanguageId()) as $category) {
            if ($category->isActive()) {
                $item = ZMLoader::make("RssItem");
                $item->setTitle($category->getName());
                $item->setLink($request->url('category', $category->getPath(), false));
                $desc = ZMHtmlUtils::strip($category->getDescription());
                if (!$isCatalog) {
                    $desc = ZMHtmlUtils::more($desc, 60);
                }
                $item->setDescription($desc);
                $item->setPubDate(ZMRssUtils::mkRssDate($category->getDateAdded()));
                $tags = array('id', 'path', 'children');
                $item->set('id', $category->getId());
                $item->set('path', implode('_', $category->getPathArray()));
                $item->set('children', array('id' => $category->getChildIds(false)));

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

        $channel = ZMLoader::make("RssChannel");
        $channel->setTitle(zm_l10n_get("Categories at %s", ZMSettings::get('storeName')));
        $channel->setLink($request->url(FILENAME_DEFAULT));
        $channel->setDescription(zm_l10n_get("All categories at %s", ZMSettings::get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = ZMLoader::make("RssFeed");
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

}
