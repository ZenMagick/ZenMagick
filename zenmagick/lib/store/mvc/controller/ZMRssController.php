<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Request controller for RSS feeds.
 *
 * <p>The <code>processGet($request)</code> method is generic and will call an appropriate method
 * for item generation based on the <em>channel</em> request parameter.</p>
 *
 * <p>The item method is expected to return the last modified date of the channel.</p>
 *
 * @todo Support for custom/additional channel/item properties.
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMRssController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     *
     * <p>This implementation will grab the channel parameter from the request. All further processing
     * is based on that value.</p>
     *
     * <p>Ideally, the only method that needs to be implemented (if not already there), is one that generates the
     * feed contents. The method name is generated as: <code>get[ucwords($channel)]Feed</code>. So, for example,
     * if channel is <em>reviews</em>, the method to be expected would be <code>getReviewsFeed($request, $key)</code>.</p>
     */
    public function processGet($request) {
        $channel = ucwords($request->getParameter('channel', null));
        $key = $request->getParameter('key', null);

        // delegate items to channel method
        $method = "get".$channel."Feed";
        if (!method_exists($this, $method)) {
            return $this->findView('error');
        } 

        // get feed data
        $feed = call_user_func(array($this, $method), $request, $key);
        if (null == $feed) {
            return null;
        }

        // create content
        ob_start();
        $this->setContentType('application/xhtml+xml');
        $this->rssHeader($request, $feed->getChannel());
        foreach ($feed->getItems() as $item) {
            $this->rssItem($request, $item);
        }
        $this->rssFooter();
        echo ob_get_clean();

        return null;
    }


    /**
     * Write RSS header.
     *
     * <p>Required data are:</p>
     * <ul>
     *  <li>title</li>
     *  <li>link</li>
     *  <li>description</li>
     *  <li>lastBuildDate</li>
     * <ul>
     *
     * @param ZMRequest request The current request.
     * @param ZMRssChannel channel The channel data.
     */
    function rssHeader($request, $channel) {
        $toolbox = $request->getToolbox();
        $lines = array(
          '<?xml version="1.0" encoding="UTF-8"?>',
          '<!-- generator="ZenMagick '.ZMSettings::get('zenmagick.version').'" -->',
          '<rss version="2.0">',
          '  <channel>',
          '    <title><![CDATA['.$toolbox->utils->encodeXML($channel->getTitle()).']]></title>',
          '    <link><![CDATA['.$channel->getLink().']]></link>',
          '    <description><![CDATA['.$toolbox->utils->encodeXML($channel->getDescription()).']]></description>',
          '    <lastBuildDate>'.ZMTools::mkRssDate($channel->getLastBuildDate()).'</lastBuildDate>'
          );

        foreach ($lines as $line) {
            echo $line . "\n";
        }
    }

    /**
     * Write RSS footer.
     */
    function rssFooter() {
        $lines = array(
          '  </channel>',
          '</rss>'
        );

        foreach ($lines as $line) {
            echo $line . "\n";
        }
    }

    /**
     * Generate RSS item.
     *
     * <p>Required data are:</p>
     * <ul>
     *  <li>title</li>
     *  <li>link</li>
     *  <li>description</li>
     * <ul>
     *
     * @param ZMRequest request The current request.
     * @param ZMRssItem item The item to render.
     */
    function rssItem($request, $item) {
        $toolbox = $request->getToolbox();
        echo "    <item>\n";
        echo "      <title>".$toolbox->utils->encodeXML($item->getTitle())."</title>\n";
        echo "      <link>".$item->getLink()."</link>\n";
        echo "      <description>".$toolbox->utils->encodeXML($item->getDescription())."</description>\n";
        echo "      <guid>".$item->getLink()."</guid>\n";
        if (null !== $item->getPubDate()) {
            echo "      <pubDate>".zm_mk_rss_date($item->getPubDate())."</pubDate>\n";
        }
        echo "    </item>\n";
    }


    /**
     * Generate RSS feed for reviews.
     *
     * @param ZMRequest request The current request.
     * @param string key Optional product id.
     * @return ZMRssFeed The feed data.
     */
    function getReviewsFeed($request, $key=null) {
        $product = null;
        if (null != $key)  {
            $reviews = array_reverse(ZMReviews::instance()->getReviewsForProductId($key));
            $product = ZMProducts::instance()->getProductForId($key);
        } else {
            $reviews = array_reverse(ZMReviews::instance()->getAllReviews());
        }
        if (null != $key && null == $product) {
            return null;
        }

        $toolbox = $request->getToolbox();
        $items = array();
        $lastPubDate = null;
        foreach ($reviews as $review) {
            if (null == $key) {
                $product = ZMProducts::instance()->getProductForId($review->getProductId());
            }
            $item = ZMLoader::make("RssItem");
            $item->setTitle(zm_l10n_get("Review: %s", $product->getName()));

            $params = 'products_id='.$review->getProductId().'&reviews_id='.$review->getId();
            $item->setLink($toolbox->net->url(FILENAME_PRODUCT_REVIEWS_INFO, $params, false, false));
            $item->setDescription($toolbox->html->more($review->getText(), 60));
            $item->setPubDate(zm_mk_rss_date($review->getDateAdded()));
            array_push($items, $item);

            if (null === $lastPubDate) {
                $lastPubDate = $review->getDateAdded(); 
            }
        }

        $channel = ZMLoader::make("RssChannel");
        $channel->setTitle(zm_l10n_get("Product Reviews"));
        $channel->setLink($toolbox->net->url(FILENAME_DEFAULT, '', false, false));
        if (null != $key)  {
            $channel->setDescription(zm_l10n_get("Product Reviews for %s at %s", $product->getName(), ZMSettings::get('storeName')));
        } else {
            $channel->setDescription(zm_l10n_get("Product Reviews at %s", ZMSettings::get('storeName')));
        }
        $channel->setLastBuildDate(zm_mk_rss_date($lastPubDate));

        $feed = ZMLoader::make("RssFeed");
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
    function getChapterFeed($request, $key=null) {
        $items = array();
        $toc = ZMEZPages::instance()->getPagesForChapterId($key);
        foreach ($toc as $page) {
            $item = ZMLoader::make("RssItem");
            $item->setTitle($page->getTitle());
            $item->setLink($request->getToolbox()->net->ezPage($page, false));
            $item->setDescription($page->getTitle());
            array_push($items, $item);
        }

        $channel = ZMLoader::make("RssChannel");
        $channel->setTitle(zm_l10n_get("Chapter %s", $key));
        $channel->setLink($request->getToolbox()->net->url(FILENAME_DEFAULT, '', false, false));
        $channel->setDescription(zm_l10n_get("All pages of Chapter %s", $key));
        $channel->setLastBuildDate(zm_mk_rss_date());

        $feed = ZMLoader::make("RssFeed");
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
    function getProductsFeed($request, $key=null) {
        if ('new' != $key) {
            return null;
        }

        $toolbox = $request->getToolbox();
        $lastPubDate = null;
        $items = array();
        $products = array_slice(array_reverse(ZMProducts::instance()->getNewProducts()), 0, 20);
        foreach ($products as $product) {
            $item = ZMLoader::make("RssItem");
            $item->setTitle($product->getName());
            $item->setLink(zm_product_href($product->getId(), null, false));
            $item->setDescription($toolbox->html->more($toolbox->html->strip($product->getDescription()), 60));
            $item->setPubDate(zm_mk_rss_date($product->getDateAdded()));
            array_push($items, $item);

            if (null === $lastPubDate) {
                $lastPubDate = $product->getDateAdded(); 
            }
        }

        $channel = ZMLoader::make("RssChannel");
        $channel->setTitle(zm_l10n_get("New Products at %s", ZMSettings::get('storeName')));
        $channel->setLink($toolbox->net->url(FILENAME_DEFAULT, '', false, false));
        $channel->setDescription(zm_l10n_get("The latest updates to %s's product list", ZMSettings::get('storeName')));
        $channel->setLastBuildDate($lastPubDate);

        $feed = ZMLoader::make("RssFeed");
        $feed->setChannel($channel);
        $feed->setItems($items);

        return $feed;
    }

}

?>
