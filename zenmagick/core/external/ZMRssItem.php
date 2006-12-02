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
 * A RSS feed item.
 *
 * <p>ZenMagick wrapper around <code>lastRSS</code>.
 *
 * @author mano
 * @package net.radebatz.zenmagick.external
 * @version $Id$
 */
class ZMRssItem {
    var $item_;


    // create new instance
    function ZMRssItem($item) {
        $this->item_ = $item;
    }

    // create new instance
    function __construct($item) {
        $this->ZMRssChannel($item);
    }

    function __destruct() {
    }


    /** getter **/
    function getTitle() { return $this->item_['title']; }
    function getLink() { return $this->item_['link']; }
    function getDescription() { return $this->item_['description']; }
    function getCategory() { return $this->item_['category']; }
    function getPubDate() { return $this->item_['pubDate']; }

}

?>
