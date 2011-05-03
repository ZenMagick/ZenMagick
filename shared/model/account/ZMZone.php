<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * A address zone.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.account
 * @Table(name="zones")
 * @Entity
 */
class ZMZone extends ZMObject {
    /**
     * @var integer $zoneId
     *
     * @Column(name="zone_id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     * @todo rename to private $id;
     */
    private $zoneId;
    /**
     * @var integer $countryId
     *
     * @Column(name="zone_country_id", type="integer", nullable=false)
     */
    private $countryId;
    /**
     * @var string $code
     *
     * @Column(name="zone_code", type="string", length=32, nullable=false)
     */
    private $code;
    /**
     * @var string $name
     *
     * @Column(name="zone_name", type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * Create new zone.
     */
    function __construct() {
        parent::__construct();
        $this->setId(0);
        $this->countryId = 0;
        $this->code = null;
        $this->name = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get the id.
     *
     * @return integer $zoneId The id.
     */
    public function getId() { return $this->zoneId; }

    /**
     * Get the country id.
     *
     * @return integer $countryId
     */
    public function getCountryId() { return $this->countryId; }

    /**
     * Get the code.
     *
     * @return string $code The code.
     */
    public function getCode() { return $this->code; }

    /**
     * Get the name.
     *
     * @return string $name The name.
     */
    public function getName() { return $this->name; }

    /**
     * Set the id.
     *
     * @param string id The id.
     */
    public function setId($id) { $this->zoneId =  $id; }

    /**
     * Set the country id.
     *
     * @param integer $countryId
     */
    public function setCountryId($countryId) { $this->countryId = $countryId; }

    /**
     * Set the code.
     *
     * @param string $code The code.
     */
    public function setCode($code) { $this->code = $code; }

    /**
     * Set the name.
     *
     * @param string $name The name.
     */
    public function setName($name) { $this->name = $name; }
}