<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * Published: 200801 :: blacknet :: via rssphp.net
 * Author: <rssphp.net>
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

/**
 * Rss feed parser.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RssParser
{
    private $document;
    private $channel;
    private $items;
    private $config;

    /**
     * Create new instance.
     */
    public function __construct(array $config=array())
    {
        $defaults = array('strictErrorChecking' => false);
        $this->config = array_merge($defaults, $config);
        $this->document = array();
        $this->channel = array();
        $this->items = array();
    }

    /**
     * Get the full feed.
     *
     * @param  boolean $attr Optional flag to return also attributes.
     * @return array   The full feed.
     */
    public function getFeed($attr=false)
    {
        return $attr ? $this->document : $this->valueExtractor($this->document);
    }

    /**
     * Get channel data.
     *
     * @param  boolean $attr Optional flag to return also attributes.
     * @return array   The channel.
     */
    public function getChannel($attr=false)
    {
        return $attr ? $this->channel : $this->valueExtractor($this->channel);
    }

    /**
     * Get item data.
     *
     * @param  boolean $attr Optional flag to return also attributes.
     * @return array   The items.
     */
    public function getItems($attr=false)
    {
        return $attr ? $this->items : $this->valueExtractor($this->items);
    }

    /**
     * Parse the given feed data.
     *
     * <p>With <code>$attr</code> set to <code>true</code>, the returned structure will have data as the value of a <em>'value'</em> key and attributes
     * for a note as value of a <em>'attributes'</em> key.<p>
     *
     * @param string feed The feed content.
     * @param  boolean $attr Optional flag to return also attributes.
     * @return array   Map of feed, channel and item elements.
     */
    public function parse($feed, $attr=false)
    {
        $this->document = array();
        $this->channel = array();
        $this->items = array();
        $domDocument = new \DOMDocument;
        $domDocument->strictErrorChecking = $this->config['strictErrorChecking'];
        $domDocument->loadXML($feed);
        $this->document = $this->parseNodes($domDocument->childNodes);
    }

    /**
     * Extract just the value part of the given value block.
     *
     * @param array valueBlock Nested arrays containing attributes/values.
     * @return array Just the value part of the tree, with the <code>'value' =&gt; XXX</code> mapping reduced to just <code>XXX</code>.
     */
    private function valueExtractor($valueBlock)
    {
        foreach ($valueBlock as $valueName => $values) {
            if (isset($values['value'])) {
                $values = $values['value'];
            }
            if (is_array($values)) {
                $valueBlock[$valueName] = $this->valueExtractor($values);
            } else {
                $valueBlock[$valueName] = $values;
            }
        }

        return $valueBlock;
    }

    /**
     * Parse a list of nodes into a value block.
     *
     * @param DOMNamedNodeMap nodeList A list of nodes.
     * @param DOMNode parentNode Optional parent node; default is <code>null</code>.
     */
    private function parseNodes($nodeList, $parentNode=null)
    {
        $itemCounter = 0;
        $nodeValue = array();
        $multiValues = array();
        foreach ($nodeList as $node) {
            if ('#' != $node->nodeName[0]) {
                $value = array();
                if ($node->attributes) {
                    for ($i=0;$node->attributes->item($i);$i++) {
                        $value['attributes'][$node->attributes->item($i)->nodeName] = $node->attributes->item($i)->nodeValue;
                    }
                }
                if (!$node->firstChild) {
                    $value['value'] = $node->textContent;
                } else {
                    $value['value'] = $this->parseNodes($node->childNodes, $node);
                }
                if ($parentNode && in_array($parentNode->nodeName, array('channel','rdf:RDF'))) {
                    if ($node->nodeName == 'item') {
                        $this->items[] = $value['value'];
                    } elseif (!in_array($node->nodeName, array('rss','channel'))) {
                        $this->channel[$node->nodeName] = $value;
                    }
                }
                if (array_key_exists($node->nodeName, $nodeValue)) {
                    if (!in_array($node->nodeName, $multiValues)) {
                        $nodeValue[$node->nodeName] = array($nodeValue[$node->nodeName], $value);
                        $multiValues[] = $node->nodeName;
                    } else {
                        $nodeValue[$node->nodeName][] = $value;
                    }
                } else {
                    $nodeValue[$node->nodeName] = $value;
                }
            } elseif ('#text' == $node->nodeName) {
                if ($value = trim(preg_replace('/\s\s+/',' ',str_replace("\n",' ', $node->textContent)))) {
                    return $value;
                }
            } elseif ('#cdata-section' == $node->nodeName) {
                return $node->textContent;
            }
        }

        return $nodeValue;
    }

}
