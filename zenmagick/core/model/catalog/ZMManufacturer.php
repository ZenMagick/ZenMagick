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
 * Manufacturer.
 *
 * @author DerManoMann
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMManufacturer extends ZMModel {
    private $id_;
    private $name_;
    private $image_;
    private $languageId_;
    private $url_;
    private $clickCount_;
    private $lastClick_;


    /**
     * Create new instance
     */
    function __construct() {
        parent::__construct();
        $this->id_ = 0;
        $this->name_ = '';
        $this->image_ = null;
        $this->languageId_ = 0;
        $this->url_ = null;
        $this->clickCount_ = 0;
        $this->lastClick_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the manufacturer id.
     *
     * @return int The manufacturer id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the manufacturer name.
     *
     * @return string The manufacturer name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the manufacturer image.
     *
     * @return string The manufacturer image.
     */
    function getImage() { return $this->image_; }

    /**
     * Check if a manufacturer image exists.
     *
     * @return boolean <code>true</code> if an image exists, <code>false</code> if not.
     */
    function hasImage() { return !empty($this->image_); }

    /**
     * Get the manufacturer image info.
     *
     * @return ZMImageInfo The image info.
     */
    function getImageInfo() { return ZMLoader::make("ImageInfo", $this->image_, $this->name_); }

    /**
     * Get the manufacturer URL.
     *
     * @return string The manufacturer URL.
     */
    function getURL() { return $this->url_; }

    /**
     * Set the manufacturer id.
     *
     * @param int id The manufacturer id.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Set the manufacturer name.
     *
     * @param string name The manufacturer name.
     */
    function setName($name) { $this->name_ = $name; }

    /**
     * Set the manufacturer image.
     *
     * @param string image The manufacturer image.
     */
    function setImage($image) { $this->image_ = $image; }

    /**
     * Set the manufacturer URL.
     *
     * @param string url The manufacturer URL.
     */
    function setURL($url) { $this->url_ = $url; }

    /**
     * Get the language id.
     *
     * @return int The language id.
     */
    function getLanguageId() { return $this->languageId_; }

    /**
     * Set the language id.
     *
     * @param int languageId The language id.
     */
    function setLanguageId($languageId) { $this->languageId_ = $languageId; }

    /**
     * Get the click counter.
     *
     * @return int The click count for this manufactuer and language.
     */
    function getClickCount() { return $this->clickCount_; }

    /**
     * Set the click counter.
     *
     * @param int clickCount The click count for this manufactuer and language.
     */
    function setClickCount($clickCount) { $this->clickCount_ = $clickCount; }

    /**
     * Get the date of the last click.
     *
     * @return date The last click date.
     */
    function getLastClick() { return $this->lastClick_; }

    /**
     * Set the date of the last click.
     *
     * @param date lastClick The last click date.
     */
    function setLastClick($lastClick) { $this->lastClick_ = $lastClick; }

}

?>
