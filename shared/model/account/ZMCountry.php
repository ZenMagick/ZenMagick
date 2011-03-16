<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package zenmagick.store.shared.model
 * @Table(name="countries")
 * @Entity
 */
class ZMCountry extends ZMObject {
    /**
     * @var integer $countryId
     *
     * @Column(name="countries_id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $countryId;
    /**
     * @var string $name
     *
     * @Column(name="countries_name", type="string", length=64, nullable=false)
     */
    private $name;
    /**
     * @var string $isoCode2
     *
     * @Column(name="countries_iso_code_2", type="string", length=2, nullable=false)
     */
    private $isoCode2;
    /**
     * @var string $isoCode3
     *
     * @Column(name="countries_iso_code_3", type="string", length=3, nullable=false)
     */
    private $isoCode3;
    /**
     * @var integer $addressFormatId
     *
     * @Column(name="address_format_id", type="integer", nullable=false)
     */
    private $addressFormatId;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->setId(0);
        $this->name = null;
        $this->isoCode2 = null;
        $this->isoCode3 = null;
        $this->addressFormatId = 0;
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
     * @return integer $countryId The country id.
     */
    public function getId() { return $this->countryId; }

    /**
     * Get the country name.
     *
     * @return string $name The country name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the country ISO code 2.
     *
     * @return string $isoCode2 The country ISO code 2.
     */
    public function getIsoCode2() { return $this->isoCode2; }

    /**
     * Get the country ISO code 3.
     *
     * @return string $isoCode3 The country ISO code 3.
     */
    public function getIsoCode3() { return $this->isoCode3; }

    /**
     * Get the address format id.
     *
     * @return int $addressFormatId The address format id.
     */
    public function getAddressFormatId() { return $this->addressFormatId; }

    /**
     * Set the country id.
     *
     * @param int id The country id.
     */
    public function setId($id) { $this->countryId = $id; }

    /**
     * Set the country name.
     *
     * @param string $name The country name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the country ISO code 2.
     *
     * @param string $code The country ISO code 2.
     */
    public function setIsoCode2($code) { $this->isoCode2 = $code; }

    /**
     * Set the country ISO code 3.
     *
     * @param string $code The country ISO code 3.
     */
    public function setIsoCode3($code) { $this->isoCode3 = $code; }

    /**
     * Set the address format id.
     *
     * @param int $addressFormatId The address format id.
     */
    public function setAddressFormatId($addressFormatId) { $this->addressFormatId = $addressFormatId; }
}