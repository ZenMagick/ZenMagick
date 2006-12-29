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
 * Manufacturer.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
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

    // create new instance
    function __construct($id, $name) {
        $this->ZMManufacturer($id, $name);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getName() { return $this->name_; }
    function getImage() { return $this->image_; }
    function getURL() { return $this->url_; }

}

?>
