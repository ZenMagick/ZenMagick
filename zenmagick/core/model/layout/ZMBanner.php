<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @package org.zenmagick.model.layout
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
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->id_ = 0;
        $this->title_ = '';
        $this->image_ = null;
        $this->text_ = '';
        $this->isNewWin_ = false;
        $this->url_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the banner id.
     *
     * @return int The banner id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the banner title.
     *
     * @return string The banner title.
     */
    function getTitle() { return $this->title_; }

    /**
     * Get the banner image.
     *
     * @return string The banner image.
     */
    function getImage() { return $this->image_; }

    /**
     * Get the banner text.
     *
     * @return string The banner text.
     */
    function getText() { return $this->text_; }

    /**
     * Check if the banner click should open a new window.
     *
     * @return boolean <code>true</code> if the banner URL should be opened in a new window, <code>false</code> if not.
     */
    function isNewWin() { return $this->isNewWin_; }

    /**
     * Get the banner URL.
     *
     * @return string The banner URL.
     */
    function getUrl() { return $this->url_; }

    /**
     * Set the banner id.
     *
     * @param int id The banner id.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Set the banner title.
     *
     * @param string title The banner title.
     */
    function setTitle($title) { $this->title_ = $title; }

    /**
     * Set the banner image.
     *
     * @param string image The banner image.
     */
    function setImage($image) { $this->image_ = $image; }

    /**
     * Set the banner text.
     *
     * @param string text The banner text.
     */
    function setText($text) { $this->text_ = $text; }

    /**
     * Set if the banner click should open a new window.
     *
     * @param boolean newWin <code>true</code> if the banner URL should be opened in a new window, <code>false</code> if not.
     */
    function setNewWin($newWin) { $this->isNewWin_ = $newWin; }

    /**
     * Set the banner URL.
     *
     * @param string url The banner URL.
     */
    function setUrl($url) { $this->url_ = $url; }

}

?>
