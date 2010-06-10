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
        $channel = $request->getParameter('channel');
        $key = $request->getParameter('key');

        // try registered RSS sources first...
        $feed = null;
        foreach (explode(',', ZMSettings::get('apps.store.rss.sources')) as $def) {
            if (null != ($source = ZMBeanUtils::getBean(trim($def)))) {
                if (null != ($feed = $source->getFeed($request, $channel, $key))) {
                    break;
                }
            }
        }

        if (null == $feed) {
            return $this->findView('error');
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
    protected function rssHeader($request, $channel) {
        $lines = array(
          '<?xml version="1.0" encoding="UTF-8"?>',
          '<!-- generator="ZenMagick '.ZMSettings::get('zenmagick.version').'" -->',
          '<rss version="2.0" xmlns:zm="http://www.zenmagick.org/">',
          ' <channel>',
          '  <title><![CDATA['.ZMTools::encodeXML($channel->getTitle()).']]></title>',
          '  <link><![CDATA['.$channel->getLink().']]></link>',
          '  <description><![CDATA['.ZMTools::encodeXML($channel->getDescription()).']]></description>',
          '  <lastBuildDate>'.ZMTools::mkRssDate($channel->getLastBuildDate()).'</lastBuildDate>'
          );

        $this->customTags($channel, '  ');

        foreach ($lines as $line) {
            echo $line . "\n";
        }
    }

    /**
     * Process custom tags.
     *
     * @param mixed obj The object.
     * @param string indent The leading whitespace.
     */
    protected function customTags($obj, $indent) {
        foreach ($obj->getTags() as $tag) {
            $value = $obj->get($tag);
            echo $indent."<zm:".$tag.">";
            if (is_string($value)) {
                echo ZMTools::encodeXML($obj->get($tag));
            } else if (is_array($value)) {
                echo "\n";
                foreach ($value as $stag => $svalues) {
                    foreach ($svalues as $sval) {
                        echo $indent." <zm:".$stag.">";
                        echo ZMTools::encodeXML($sval);
                        echo "</zm:".$stag.">\n";
                    }
                }
                echo $indent;
            }
            echo "</zm:".$tag.">\n";
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
    protected function rssItem($request, $item) {
        echo "  <item>\n";
        echo "   <title>".ZMTools::encodeXML($item->getTitle())."</title>\n";
        echo "   <link>".$item->getLink()."</link>\n";
        echo "   <description>".ZMTools::encodeXML($item->getDescription())."</description>\n";
        echo "   <guid>".$item->getLink()."</guid>\n";
        if (null !== $item->getPubDate()) {
            echo "   <pubDate>".ZMTools::mkRssDate($item->getPubDate())."</pubDate>\n";
        }

        $this->customTags($item, '   ');

        echo "  </item>\n";
    }

    /**
     * Write RSS footer.
     */
    protected function rssFooter() {
        $lines = array(
          ' </channel>',
          '</rss>'
        );

        foreach ($lines as $line) {
            echo $line . "\n";
        }
    }

}
