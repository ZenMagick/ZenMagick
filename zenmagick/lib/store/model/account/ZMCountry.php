<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @author DerManoMann
 * @package org.zenmagick.store.model
 * @version $Id$
 */
class ZMCountry extends ZMObject {
    private $name_;
    private $isoCode2_;
    private $isoCode3_;
    private $addressFormatId_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->setId(0);
        $this->name_ = null;
        $this->isoCode2_ = null;
        $this->isoCode3_ = null;
        $this->addressFormatId_ = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the country id.
     *
     * @return int The country id.
     */
    public function getId() { return $this->get('countryId'); }

    /**
     * Get the country name.
     *
     * @return string The country name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the country ISO code 2.
     *
     * @return string The country ISO code 2.
     */
    public function getIsoCode2() { return $this->isoCode2_; }

    /**
     * Get the country ISO code 3.
     *
     * @return string The country ISO code 3.
     */
    public function getIsoCode3() { return $this->isoCode3_; }

    /**
     * Get the address format id.
     *
     * @return int The address format id.
     */
    public function getAddressFormatId() { return $this->addressFormatId_; }

    /**
     * Set the country id.
     *
     * @param int id The country id.
     */
    public function setId($id) { $this->set('countryId', $id); }

    /**
     * Set the country name.
     *
     * @param string name The country name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the country ISO code 2.
     *
     * @param string code The country ISO code 2.
     */
    public function setIsoCode2($code) { $this->isoCode2_ = $code; }

    /**
     * Set the country ISO code 3.
     *
     * @param string code The country ISO code 3.
     */
    public function setIsoCode3($code) { $this->isoCode3_ = $code; }

    /**
     * Set the address format id.
     *
     * @param int addressFormatId The address format id.
     */
    public function setAddressFormatId($addressFormatId) { $this->addressFormatId_ = $addressFormatId; }

}
