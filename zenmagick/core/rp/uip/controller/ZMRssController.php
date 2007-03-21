<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * <p>The <code>processGet()</code> method is generic and will call an appropriate method
 * for item generation based on the <em>channel</em> request parameter.</p>
 *
 * <p>The item method is expected to return the last modified date of the channel.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMRssController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMRssController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMRssController();
    }

    /**
     * Default d'tor.
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
     * item content. The method name is generated as: <code>generate[ucwords($channel)]Items</code>. So, for example,
     * if channel is <em>reviews</em>, the method to be expected would be <code>generateReviewsItems($key)</code>.</p>
     */
    function processGet() {
    global $zm_request;

        $channel = ucwords($zm_request->getRequestParameter('channel', null));
        $key = $zm_request->getRequestParameter('key', null);

        // delegate items to channel method
        $method = "generate".$channel."Items";
        if (!method_exists($this, $method)) {
            return $this->findView('error');
        } 

        $lastModified = zm_mk_rss_date();
        ob_start();
        $mlm = call_user_func(array($this, $method), $key);
        if (null != $mlm) {
            $lastModified = $mlm;
        }
        $itemContents = ob_get_clean();

        // create content
        $this->setContentType('application/xhtml+xml');
        $this->rssHeader($channel, $lastModified, zm_href(FILENAME_DEFAULT, '', false), zm_l10n_get("%s %s for %s", $channel, $key, zm_setting('storeName')));
        echo $itemContents;
        $this->rssFooter();

        return null;
    }


    /**
     * Write RSS header.
     *
     * @param string title The title.
     * @param string lastModified The last modified date.
     * @param string link The link.
     * @param string description The channel description.
     */
    function rssHeader($title, $lastModified, $link, $description) {
        $lines = array(
          '<?xml version="1.0" encoding="UTF-8"?>',
          '<!-- generator="ZenMagick '.zm_setting('ZenMagickVersion').'" -->',
          '<rss version="2.0">',
          '  <channel>',
          '    <title><![CDATA['.$title.']]></title>',
          '    <link><![CDATA['.$link.']]></link>',
          '    <description><![CDATA['.$description.']]></description>',
          '    <lastBuildDate>'.$lastModified.'</lastBuildDate>'
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
     * @param ZMRssItem item The item to render.
     */
    function rssItem($item) {
        echo "    <item>\n";
        echo "      <title>".zm_xml_encode($item->getTitle())."</title>\n";
        echo "      <link>".$item->getLink()."</link>\n";
        echo "      <description>".zm_xml_encode($item->getDescription())."</description>\n";
        echo "      <guid>".$item->getLink()."</guid>\n";
        if (null !== $item->getPubDate()) {
            echo "      <pubDate>".zm_mk_rss_date($item->getPubDate())."</pubDate>\n";
        }
        echo "    </item>\n";
    }


    /**
     * Generate RSS items for reviews.
     *
     * @param string key Optional key value for channel that have multiple values; eg EZPages chapter.
     * @return string The last modified date or <code>null</code>.
     */
    function generateReviewsItems($key=null) {
    global $zm_reviews, $zm_products;

        $lastReviewDate = null;
        $reviews = array_reverse($zm_reviews->getAllReviews());
        foreach ($zm_reviews->getAllReviews() as $review) {
            $product = $zm_products->getProductForId($review->getProductId());
            echo "    <item>\n";
            echo "      <title>".zm_xml_encode(zm_l10n_get("Review: %s", $product->getName()))."</title>\n";

            $params = 'products_id='.$review->getProductId().'&reviews_id='.$review->getId();
            $href = zm_href(FILENAME_PRODUCT_REVIEWS_INFO, $params, false);
            echo "      <link>".$href."</link>\n";

            echo "      <description>".zm_xml_encode(zm_more($review->getText(), 60, false))."</description>\n";
            echo "      <guid>".$href."</guid>\n";
            if (null === $lastReviewDate) {
                $lastReviewDate = $review->getDateAdded(); 
            }
            echo "      <pubDate>".zm_mk_rss_date($review->getDateAdded())."</pubDate>\n";

            echo "    </item>\n";
        }

        return zm_mk_rss_date($lastReviewDate);
    }

    /**
     * Generate RSS items for EZPages chapter.
     *
     * @param string key Optional key value for channel that have multiple values; eg EZPages chapter.
     * @return string The last modified date or <code>null</code>.
     */
    function generateChapterItems($key=null) {
    global $zm_pages;

        $toc = $zm_pages->getPagesForChapterId($key);
        foreach ($toc as $page) {
            echo "    <item>\n";
            echo "      <title>".zm_xml_encode($page->getTitle())."</title>\n";
            echo "      <link>".zm_ezpage_href($page, false)."</link>\n";
            echo "      <description>".zm_xml_encode($page->getTitle())."</description>\n";
            echo "    </item>\n";
        }

        return zm_mk_rss_date();
    }

    /**
     * Generate RSS items for products.
     *
     * @param string key Optional key value for various product types; supported: 'new'
     * @return string The last modified date or <code>null</code>.
     */
    function generateProductsItems($key=null) {
    global $zm_products;

        $lastReviewDate = null;
        $products = array_slice(array_reverse($zm_products->getNewProducts()), 0, 20);
        foreach ($products as $product) {
            echo "    <item>\n";
            echo "      <title>".zm_xml_encode($product->getName())."</title>\n";
            echo "      <link>".zm_product_href($product->getId(), false)."</link>\n";
            echo "      <description>".zm_xml_encode(zm_more(zm_strip_html($product->getDescription(), false), 60, false))."</description>\n";
            echo "      <pubDate>".zm_mk_rss_date($product->getDateAdded())."</pubDate>\n";
            echo "    </item>\n";
            if (null === $lastReviewDate) {
                $lastReviewDate = $product->getDateAdded(); 
            }
        }

        return zm_mk_rss_date($lastReviewDate);
    }

}

?>
