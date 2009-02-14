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
 * @author DerManoMann
 * @package org.zenmagick.model.rss
 * @version $Id$
 */
class ZMRssFeed extends ZMModel {
    var $channel_;
    var $items_;


    /**
     * Create new RSS feed.
     */
    function __construct() {
        parent::__construct();
        $this->channel_ = null;
        $this->items_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the channel.
     *
     * @return ZMRssChannel The channel.
     */
    function getChannel() { return $this->channel_; }

    /**
     * Get the feed items.
     *
     * @return array A list of <code>ZMRssItem</code> instances.
     */
    function getItems() { return $this->items_; }

    /**
     * Set the channel.
     *
     * @param ZMRssChannel channel The channel.
     */
    function setChannel($channel) { $this->channel_ = $channel; }

    /**
     * Set the feed items.
     *
     * @param array items A list of <code>ZMRssItem</code> instances.
     */
    function setItems($items) { $this->items_ = $items; }

}

?>
