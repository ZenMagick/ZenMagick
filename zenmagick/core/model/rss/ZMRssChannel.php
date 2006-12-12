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
 * A RSS feed channel.
 *
 * <p>ZenMagick wrapper around <code>lastRSS</code>.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model.rss
 * @version $Id$
 */
class ZMRssChannel {
    var $rs_;


    // create new instance
    function ZMRssChannel($rs) {
        $this->rs_ = $rs;
    }

    // create new instance
    function __construct($rs) {
        $this->ZMRssChannel($rs);
    }

    function __destruct() {
    }


    /** getter **/
    function getTitle() { return $this->rs_['title']; }
    function getLink() { return $this->rs_['link']; }
    function getEncoding() { return $this->rs_['encoding']; }
    function getDescription() { return $this->rs_['description']; }
    function getLastBuildDate() { return $this->rs_['lastBuildDate']; }
    function getImageTitle() { return $this->rs_['image_title']; }
    function getImageLink() { return $this->rs_['image_link']; }
    function getImageWidth() { return $this->rs_['image_width']; }
    function getImageHeight() { return $this->rs_['image_height']; }
    function hasImage() { return array_key_exists($this->rs_, 'image_url'); }

}

?>
