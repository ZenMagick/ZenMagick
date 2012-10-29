<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\Http\Rss;

use DateTime;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * A RSS feed generator.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RssFeedGenerator extends ZMObject {

    /**
     * Generate feed data.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @param RssFeed feed The feed.
     * @return string The feed data.
     */
    public function generate($request, RssFeed $feed) {
        ob_start();
        $this->rssHeader($request, $feed->getChannel());
        $gcCount = 0;
        foreach ($feed->getItems() as $item) {
            $this->rssItem($request, $item);
            if (0 == ++$gcCount%133) { gc_collect_cycles(); }
            $this->rssItem($request, $item);

        }
        $this->rssFooter();
        return trim(ob_get_clean());
    }

    /**
     * Encode data.
     *
     * @param string s The string to encode.
     * @param boolean tag Optinal flag indicating that <code>$s</code> is a tag.
     * @return string Encoded string.
     */
    protected function encode($s, $tag=false) {
        $encoding = array(
            '<' => '&lt;',
            '>' => '&gt;',
            '&' => '&amp;'
        );
        if ($tag) {
            $encoding[' '] = '-';
            $encoding['/'] = '-';
        }

        foreach ($encoding as $char => $entity) {
            $s = str_replace($char, $entity, $s);
        }

        if ($tag && (string) (int) $s == $s) {
            // can't have numeric element name
            $s = 'n_'.$s;
        }

        return $s;
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
     * @param ZenMagick\Http\Request request The current request.
     * @param RssChannel channel The channel data.
     */
    protected function rssHeader($request, $channel) {
        $lines = array(
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<!-- generator="ZenMagick '.Runtime::getSettings()->get('zenmagick.version').'" -->',
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:zm="http://www.zenmagick.org/">',
            ' <channel>',
            '  <title><![CDATA['.$this->encode($channel->getTitle()).']]></title>',
            '  <atom:link href="'.$this->encode($channel->getLink()).'" rel="self" type="application/rss+xml" />',
            '  <link><![CDATA['.$channel->getLink().']]></link>',
            '  <description><![CDATA['.$this->encode($channel->getDescription()).']]></description>',
            '  <lastBuildDate>'.$channel->getLastBuildDate()->format(DateTime::RSS).'</lastBuildDate>'
        );

        $this->customTags($channel, '  ');

        foreach ($lines as $line) {
            echo $line . "\n";
        }
    }

    /**
     * Format a tag value.
     *
     * @param mixed value The value.
     * @param string indent The leading whitespace.
     */
    protected function tagValue($value, $indent) {
        if (is_array($value)) {
            echo "\n";
            $tindent = '  '.$indent;
            foreach ($value as $tkey => $tvalue) {
                $tkey = $this->encode($tkey, true);
                echo $tindent.'<zm:'.$tkey.'>';
                $this->tagValue($tvalue, $tindent);
                echo '</zm:'.$tkey.">\n";
            }
            echo $indent;
        } else {
            echo $this->encode($value);
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
            $tag = $this->encode($tag, true);
            $value = $obj->get($tag);
            echo $indent."<zm:".$tag.">";
            $this->tagValue($value, $indent);
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
     * @param ZenMagick\Http\Request request The current request.
     * @param RssItem item The item to render.
     */
    protected function rssItem($request, $item) {
        $netTool = Runtime::getContainer()->get('netTool');
        echo "  <item>\n";
        echo "   <title>".$this->encode($item->getTitle())."</title>\n";
        echo "   <link>".$this->encode($netTool->absoluteUrl($item->getLink(), true))."</link>\n";
        echo "   <description>".$this->encode($item->getDescription())."</description>\n";
        echo "   <guid>".$this->encode($netTool->absoluteUrl($item->getLink(), true))."</guid>\n";
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
