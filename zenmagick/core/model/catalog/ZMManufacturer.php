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
 * @author mano
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMManufacturer extends ZMModel {
    var $id_;
    var $name_;
    var $image_;
    var $url_;


    /**
     * Create new manufacturer
     *
     * @param int id The manufacturer id.
     * @param string name The name.
     */
    function ZMManufacturer($id, $name) {
        parent::__construct();

        $this->id_ = $id;
        $this->name_ = $name;
        $this->image_ = null;
        $this->url_ = null;
    }

    /**
     * Create new manufacturer
     *
     * @param int id The manufacturer id.
     * @param string name The name.
     */
    function __construct($id, $name) {
        $this->ZMManufacturer($id, $name);
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
    function hasImage() { return !zm_is_empty($this->image_); }

    /**
     * Get the manufacturer image info.
     *
     * @return ZMImageInfo The image info.
     */
    function getImageInfo() { return $this->create("ImageInfo", $this->image_, $this->name_); }

    /**
     * Get the manufacturer URL.
     *
     * @return string The manufacturer URL.
     */
    function getURL() { return $this->url_; }

}

?>
