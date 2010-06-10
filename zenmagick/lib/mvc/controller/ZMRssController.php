<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * <p>Feed content is taken from the first of the configured <code>ZMRssSource</code> instances that returns data.</p>
 *
 * <p>Sources are configured by appending the implementation class name to '<em>zenmagick.mvc.rss.sources</em>'.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.controller
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
     * {@inheritDoc}
     */
    public function processGet($request) {
        $channel = $request->getParameter('channel');
        $key = $request->getParameter('key');

        // try registered RSS sources first...
        $feed = null;
        foreach (explode(',', ZMSettings::get('zenmagick.mvc.rss.sources')) as $def) {
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
          '  <title><![CDATA['.ZMXmlUtils::encodeXML($channel->getTitle()).']]></title>',
          '  <link><![CDATA['.$channel->getLink().']]></link>',
          '  <description><![CDATA['.ZMXmlUtils::encodeXML($channel->getDescription()).']]></description>',
          '  <lastBuildDate>'.ZMRssUtils::mkRssDate($channel->getLastBuildDate()).'</lastBuildDate>'
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
            if (is_array($value)) {
                echo "\n";
                foreach ($value as $stag => $svalues) {
                    foreach ($svalues as $sval) {
                        echo $indent." <zm:".$stag.">";
                        echo ZMXmlUtils::encodeXML($sval);
                        echo "</zm:".$stag.">\n";
                    }
                }
                echo $indent;
            } else {
                // treat as string
                echo ZMXmlUtils::encodeXML($obj->get($tag));
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
        echo "   <title>".ZMXmlUtils::encodeXML($item->getTitle())."</title>\n";
        echo "   <link>".$item->getLink()."</link>\n";
        echo "   <description>".ZMXmlUtils::encodeXML($item->getDescription())."</description>\n";
        echo "   <guid>".$item->getLink()."</guid>\n";
        if (null !== $item->getPubDate()) {
            echo "   <pubDate>".ZMRssUtils::mkRssDate($item->getPubDate())."</pubDate>\n";
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
