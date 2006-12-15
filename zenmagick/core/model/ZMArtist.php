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
 * An artist.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMArtist {
    var $id_;
    var $name_;
    var $genre_;
    var $image_;
    var $url_;
    var $recordCompany_;


    // create new instance
    function ZMArtist() {
        $this->id_ = 0;
        $this->name_ = '';
        $this->genre_ = '';
        $this->image_ = null;
        $this->url_ = null;
        $this->recordCompany_ = null;
    }

    // create new instance
    function __construct() {
        $this->ZMArtist();
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getName() { return $this->name_; }
    function getGenre() { return $this->genre_; }
    function hasImage() { return !zm_is_empty($this->image_); }
    function getImage() { return $this->image_; }
    function hasUrl() { return !zm_is_empty($this->url_); }
    function getUrl() { return $this->url_; }
    function getRecordCompany() { return $this->recordCompany_; }

}

?>
