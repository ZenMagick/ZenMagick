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
 *
 * $Id$
 */
?>
<?php


/**
 * A single banner.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMBanner {
    var $id_;
    var $title_;
    var $image_;
    var $text_;
    var $isNewWin_;
    var $url_;


    // create new instance
    function ZMBanner() {
        $this->id_ = 0;
        $this->title_ = '';
        $this->image_ = null;
        $this->text_ = '';
        $this->isNewWin_ = false;
        $this->url_ = null;
    }

    // create new instance
    function __construct() {
        $this->ZMBanner();
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getTitle() { return $this->title_; }
    function getImage() { return $this->image_; }
    function getText() { return $this->text_; }
    function isNewWin() { return $this->isNewWin_; }
    function getUrl() { return $this->url_; }

}

?>
