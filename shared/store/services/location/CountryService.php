<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\services\location;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Countries.
 *
 * @author DerManoMann
 */
class CountryService extends ZMObject {
    private $countries;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->countries = null;
    }


    /**
     * Get country for the given name.
     *
     * @param string name The country name.
     * @return Country The country or <code>null</code>.
     */
    public function getCountryForName($name) {
        $this->getCountries();
        foreach ($this->countries as $country) {
            if ($name == $country->getName()) {
                return $country;
            }
        }
        return null;
    }

    /**
     * Get a list of all countries.
     *
     * @return array A list of <code>Country</code> objects.
     */
    public function getCountries() {
        if (null !== $this->countries)
            return $this->countries;

        $sql = "SELECT *
                FROM " . TABLE_COUNTRIES . "
                ORDER BY countries_name";
        $this->countries = \ZMRuntime::getDatabase()->fetchAll($sql, array(), TABLE_COUNTRIES, 'zenmagick\apps\store\model\location\Country');
        return $this->countries;
    }

    /**
     * Get country for the given id.
     *
     * @param int id The country id.
     * @return Country The country or <code>null</code>.
     */
    public function getCountryForId($id) {
        $this->getCountries();
        foreach ($this->countries as $country) {
            if ($id == $country->getId()) {
                return $country;
            }
        }
        return null;
    }

    /**
     * Get country for the given ISO code2.
     *
     * @param string code The country code.
     * @return Country The country or <code>null</code>.
     */
    public function getCountryForIsoCode2($code) {
        $this->getCountries();
        foreach ($this->countries as $country) {
            if ($code == $country->getIsoCode2()) {
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
        $zone = \ZMRuntime::getDatabase()->querySingle($sql, array('zoneId' => $zoneId, 'countryId' => $countryId), 'zones');
        return null !== $zone ? $zone['code'] : $defaultZone;
    }


    /**
     * Get all zones for the given country id.
     *
     * @param int countryId The country id.
     * @return array List of <code>Zone</code> objects.
     */
    public function getZonesForCountryId($countryId) {
        if (empty($countryId)) {
            return array();
        }

        $sql = "SELECT distinct *
                FROM " . TABLE_ZONES . "
                WHERE zone_country_id = :countryId
                ORDER BY zone_name";
        $zones = \ZMRuntime::getDatabase()->fetchAll($sql, array('countryId' => $countryId), 'zones', 'zenmagick\apps\store\model\location\Zone');
        return $zones;
    }

}
