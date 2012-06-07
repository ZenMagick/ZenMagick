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
namespace zenmagick\http\rss;

use ArrayIterator;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A RSS feed loader.
 *
 * <p>ZenMagick wrapper around <code>lastRSS</code>.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RssFeedLoader extends ZMObject {
    private $config;
    private $cache;


    /**
     * Create a new instance.
     */
    public function __construct(array $config=array()) {
        parent::__construct();
        $defaults = array('strictErrorChecking' => false, 'useIncludePath' => false, 'urlContext' => false);
        $this->config = array_merge($defaults, $config);
        $this->cache = null;
    }


    /**
     * Set the cache.
     *
     * @param zenmagick\base\cache\Cache cache The cache.
     */
    public function setCache($cache) {
        $this->cache = $cache;
    }

    /**
     * Get/load the feed.
     *
     * @param string url The feed url.
     * @param string category An optional category; default is <code>null</code>.
     * @param int limit An optional item limit; default is 5; use 0 for all.
     * @return RssFedd A <code>RssFeed</code> instance.
     */
    public function getFeed($url, $category=null, $limit=5) {
        $cacheKey = \ZMLangUtils::mkUnique('feeds', $url, implode(':', $this->config));

        if (!$this->cache || false === ($rssParser = $this->cache->lookup($cacheKey))) {
            $rssParser = new RssParser($this->config);
            // todo: conditional GET
            $rssParser->parse(file_get_contents($url, $this->config['useIncludePath'], $this->getContext()));
        }

        $feed = new RssFeed();
        $feed->setChannel(new RssChannel($rssParser->getChannel()));
        $items = array();
        foreach ($rssParser->getItems() as $itemData) {
            $item = new RssItem($itemData);
            if (null == $category || in_array($category, $item->getCategories())) {
                $items[] = $item;
            }
            if (0 != $limit && $limit <= count($items)) {
                break;
            }
        }
        $feed->setItems(new ArrayIterator($items));

        // cache if enabled
        if ($this->cache) {
            $this->cache->save($rssParser, $cacheKey);
        }

        return $feed;
    }

    /**
     * Get a context to be used when loading a feed from a url.
     *
     * @return A stream context or <code>null</code>.
     */
    private function getContext() {
        switch ($this->config['urlContext']) {
        case 'zenmagick':
            $header = array(
                'Accept: text/xml,application/xml,application/xhtml+xml,;q=0.9',
                'Accept-Language: en-gb,en;q=0.5',
                'User-Agent: ZenMagick RssFeedLoader 1.0'
            );
            break;
        case 'random':
            $header = array(
                'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
                'Accept-Charset: '.(rand(0,1) ? 'en-gb,en;q=0.'.rand(3,8) : 'en-us,en;q=0.'.rand(3,8)),
                'Accept-Language: en-us,en;q=0.'.rand(4,6),
                'User-Agent: Mozilla/5.0 (Windows U; Windows NT 5.'.rand(0,2).'; en-US; rv:1.'.rand(2,9).'.'.rand(0,4).'.'.rand(1,9).') Gecko/2007'.rand(10,12).rand(10,30).' Firefox/2.0.'.rand(0,1).'.'.rand(1,9)
            );
            break;
        default:
            return null;
        }

        $contextOptions = array(
            'http' => array(
                'method'=> 'GET',
                'header' => implode("\r\n", $header)
            )
        );

        return stream_context_create($contextOptions);
    }

}
