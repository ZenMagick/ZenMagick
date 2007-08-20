<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * A single media item.
 *
 * @author mano
 * @package net.radebatz.zenmagick.plugins.zm_product_music_info.model
 * @version $Id: ZMMedia.php 158 2007-04-05 07:35:49Z radebatz $
 */
class ZMMedia extends ZMModel {
    var $id_;
    var $filename_;
    var $dateAdded_;
    var $type_;


    /**
     * Default c'tor.
     */
    function ZMMedia() {
        parent::__construct();

        $this->id_ = 0;
        $this->filename_ = null;
        $this->dateAdded_ = null;
        $this->type_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMMedia();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the media id.
     *
     * @return int The media id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the media filename.
     *
     * @return string The media filename.
     */
    function getFilename() { return $this->filename_; }

    /**
     * Get the added date.
     *
     * @return string The date the media was added.
     */
    function getDateAdded() { return $this->dateAdded_; }

    /**
     * Get the media type.
     *
     * @return ZMMediaType The media type.
     */
    function getType() { return $this->type_; }

}

?>
