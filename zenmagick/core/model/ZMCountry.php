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
 * A single country.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMCountry extends ZMModel {
    var $id_;
    var $name_;
    var $isoCode2_;
    var $isoCode3_;
    var $addressFormatId_;


    /**
     * Default c'tor.
     */
    function ZMCountry() {
        parent::__construct();

        $this->id_ = 0;
        $this->name_ = '';
        $this->isoCode2_ = '';
        $this->isoCode3_ = '';
        $this->addressFormatId_ = 0;
    }

    // create new instance
    function __construct() {
        $this->ZMCountry();
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
    function getIsoCode2() { return $this->isoCode2_; }
    function getIsoCode3() { return $this->isoCode3_; }
    function getAddressFormatId() { return $this->addressFormatId_; }

}

?>
