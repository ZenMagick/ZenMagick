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
 * A single country.
 *
 * @author mano
 * @package org.zenmagick.model
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

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCountry();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the country id.
     *
     * @return int The country id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the country name.
     *
     * @return string The country name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the country ISO code 2.
     *
     * @return string The country ISO code 2.
     */
    function getIsoCode2() { return $this->isoCode2_; }

    /**
     * Get the country ISO code 3.
     *
     * @return string The country ISO code 3.
     */
    function getIsoCode3() { return $this->isoCode3_; }

    /**
     * Get the address format id.
     *
     * @return int The address format id.
     */
    function getAddressFormatId() { return $this->addressFormatId_; }

}

?>
