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
 * A single banner.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMBanner extends ZMModel {
    var $id_;
    var $title_;
    var $image_;
    var $text_;
    var $isNewWin_;
    var $url_;


    /**
     * Default c'tor.
     */
    function ZMBanner() {
        parent::__construct();

        $this->id_ = 0;
        $this->title_ = '';
        $this->image_ = null;
        $this->text_ = '';
        $this->isNewWin_ = false;
        $this->url_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMBanner();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the bannder id.
     *
     * @return int The banner id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the bannder title.
     *
     * @return string The banner title.
     */
    function getTitle() { return $this->title_; }

    /**
     * Get the bannder image.
     *
     * @return string The banner image.
     */
    function getImage() { return $this->image_; }

    /**
     * Get the bannder text.
     *
     * @return string The banner text.
     */
    function getText() { return $this->text_; }

    /**
     * Check if the banner click should open a new window.
     *
     * @return bool <code>true</code> if the banner URL should be opened in a new window, <code>false</code> if not.
     */
    function isNewWin() { return $this->isNewWin_; }

    /**
     * Get the bannder URL.
     *
     * @return string The banner URL.
     */
    function getUrl() { return $this->url_; }

}

?>
