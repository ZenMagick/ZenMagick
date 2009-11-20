<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Countries.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.account
 * @version $Id$
 */
class ZMCountries extends ZMObject {
    private $countries_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->countries_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Countries');
    }


    /**
     * Get country for the given name.
     *
     * @param string name The country name.
     * @return ZMCountry The country or <code>null</code>.
     */
    public function getCountryForName($name) {
        $this->getCountries();
        foreach ($this->countries_ as $country) {
            if ($name == $country->getName()) {
                return $country;
            }
        }
        return null;
    }

    /**
     * Get a list of all countries.
     *
     * @return array A list of <code>ZMCountry</code> objects.
     */
    public function getCountries() {
        if (null !== $this->countries_)
            return $this->countries_;

        $sql = "SELECT *
                FROM " . TABLE_COUNTRIES . "
                ORDER BY countries_name";
        $this->countries_ = ZMRuntime::getDatabase()->query($sql, array(), TABLE_COUNTRIES, 'Country');
        return $this->countries_;
    }

    /**
     * Get country for the given id.
     *
     * @param int id The country id.
     * @return ZMCountry The country or <code>null</code>.
     */
    public function getCountryForId($id) {
        $this->getCountries();
        foreach ($this->countries_ as $country) {
            if ($id == $country->getId()) {
                return $country;
            }
        }
        return null;
    }


    /**
     * Get the zone code for the given country and zone id.
     * 
     * @param int countryId The country id.
     * @param int zoneId The zone id.
     * @param string defaultZone Optional default value; default is <code>''</code>.
     * @return string The zone code or the provided default value.
     */
    public function getZoneCode($countryId, $zoneId, $defaultZone='') {
        $sql = "SELECT zone_code
                FROM " . TABLE_ZONES . "
                WHERE zone_country_id = :countryId
                 AND zone_id = :zoneId";
        $zone = ZMRuntime::getDatabase()->querySingle($sql, array('zoneId' => $zoneId, 'countryId' => $countryId), TABLE_ZONES);
        return null !== $zone ? $zone['code'] : $defaultZone;
    }


    /**
     * Get all zones for the given country id.
     *
     * @param int countryId The country id.
     * @return array List of <code>ZMZone</code> objects.
     */
    public function getZonesForCountryId($countryId) {
        if (empty($countryId)) {
            return array();
        }

        $sql = "SELECT distinct *
                FROM " . TABLE_ZONES . "
                WHERE zone_country_id = :countryId
                ORDER BY zone_name";
        $zones = ZMRuntime::getDatabase()->query($sql, array('countryId' => $countryId), TABLE_ZONES, 'Zone');
        return $zones;
    }

}

?>
