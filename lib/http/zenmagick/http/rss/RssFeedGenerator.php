<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * Portions Copyright (c)      Vojtech Semecky, webmaster @ webdot . cz
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
namespace zenmagick\http\rss;

use DateTime;

use zenmagick\base\ZMObject;
use zenmagick\base\Runtime;

/**
 * A RSS feed generator.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.rss
 * @todo add support for caching and move into container
 */
class RssFeedGenerator extends ZMObject {

    /**
     * Generate feed data.
     *
     * @param ZMRequest request The current request.
     * @param RssFeed feed The feed.
     */
    public function generate($request, RssFeed $feed) {
        ob_start();
        $this->rssHeader($request, $feed->getChannel());
        foreach ($feed->getItems() as $item) {
            $this->rssItem($request, $item);
        }
        $this->rssFooter();
        return trim(ob_get_clean());
    }

    /**
     * Encode data.
     *
     * @param string s The string to encode.
     * @return string Encoded string.
     */
    protected function encode($s) {
        return \ZMXmlUtils::encodeXML($s);
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
     * @param RssChannel channel The channel data.
     */
    protected function rssHeader($request, $channel) {
        $lines = array(
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<!-- generator="ZenMagick '.Runtime::getSettings()->get('zenmagick.version').'" -->',
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:zm="http://www.zenmagick.org/">',
            ' <channel>',
            '  <title><![CDATA['.$this->encode($channel->getTitle()).']]></title>',
            '  <atom:link href="'.$this->encode($request->absoluteUrl($channel->getLink(), true)).'" rel="self" type="application/rss+xml" />',
            '  <link><![CDATA['.$request->absoluteUrl($channel->getLink(), true).']]></link>',
            '  <description><![CDATA['.$this->encode($channel->getDescription()).']]></description>',
            '  <lastBuildDate>'.$channel->getLastBuildDate()->format(DateTime::RSS).'</lastBuildDate>'
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
                        echo $this->encode($sval);
                        echo "</zm:".$stag.">\n";
                    }
                }
                echo $indent;
            } else {
                // treat as string
                echo $this->encode($obj->get($tag));
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
        echo "   <title>".$this->encode($item->getTitle())."</title>\n";
        echo "   <link>".$this->encode($request->absoluteUrl($item->getLink(), true))."</link>\n";
        echo "   <description>".$this->encode($item->getDescription())."</description>\n";
        echo "   <guid>".$this->encode($request->absoluteUrl($item->getLink(), true))."</guid>\n";
        if (null !== $item->getPubDate()) {
            echo "   <pubDate>".$item->getPubDate()->format(DateTime::RSS)."</pubDate>\n";
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
