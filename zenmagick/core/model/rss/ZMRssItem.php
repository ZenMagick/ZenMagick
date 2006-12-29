<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * A RSS feed item.
 *
 * <p>ZenMagick wrapper around <code>lastRSS</code>.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model.rss
 * @version $Id$
 */
class ZMRssItem extends ZMModel {
    var $item_;


    /**
     * Create new RSS item.
     *
     * @param array Array of item data.
     */
    function ZMRssItem($item) {
        parent::__construct();

        $this->item_ = $item;
    }

    /**
     * Create new RSS item.
     *
     * @param array Array of item data.
     */
    function __construct($item) {
        $this->ZMRssChannel($item);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the item title.
     *
     * @return string The item title.
     */
    function getTitle() { return $this->item_['title']; }

    /**
     * Get the item link.
     *
     * @return string The item link.
     */
    function getLink() { return $this->item_['link']; }

    /**
     * Get the item description.
     *
     * @return string The item description.
     */
    function getDescription() { return $this->item_['description']; }

    /**
     * Get the item category.
     *
     * @return string The item category.
     */
    function getCategory() { return $this->item_['category']; }

    /**
     * Get the item publish date.
     *
     * @return string The item publish date.
     */
    function getPubDate() { return $this->item_['pubDate']; }

}

?>
