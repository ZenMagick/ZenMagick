<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c)      Vojtech Semecky, webmaster @ webdot . cz
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
 * A RSS feed.
 *
 * <p>ZenMagick wrapper around <code>lastRSS</code>.
 *
 * @author DerManoMann
 * @package org.zenmagick.model.rss
 * @version $Id$
 */
class ZMRss extends ZMObject {
    var $url_;
    var $limit_;
    var $channel_;
    var $items_;


    /**
     * Create a new RSS feed
     *
     * @param string url The feed url.
     * @param string category An optional category.
     * @param int limit An optional item limit (default is 5).
     */
    function __construct($url, $category=null, $limit=5) {
        parent::__construct();

        $this->url_ = $url;
        $this->limit_ = $limit;
        $rss = new lastRSS();
        $rss->cache_dir = ZMSettings::get('rssCacheDir');
        $rss->cache_time = ZMSettings::get('rssCacheTimeout');
        $rss->CDATA = 'strip';
        $rs = $rss->Get($this->url_);
        $this->channel_ = ZMLoader::make("RssChannel", $rs);
        $this->items_ = array();
        if (null != $rs) {
            foreach($rs['items'] as $rs_item) {
                $item = ZMLoader::make("RssItem", $rs_item);
                if (null == $category || $category == $item->getCategory()) {
                    array_push($this->items_, $item);
                }
                if ($this->limit_ <= count($this->items_)) {
                    break;
                }
            }
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get channel information.
     *
     * @return ZMRssChannel The channel information.
     */
    function getChannel() {
        return $this->channel_;
    }


    /**
     * Get feed items.
     *
     * @param array A list of <code>ZMRssItem</code>s.
     */
    function getItems() {
        return $this->items_;
    }


    /**
     * Returns <code>true</code> if contents is available.
     *
     * @return boolean <code>true</code> if feed items are available, <code>false</code>, if not.
     */
    function hasContents() {
        return 0 != count($this->items_);
    }

}

?>
