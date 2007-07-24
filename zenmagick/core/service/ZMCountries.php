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
 * Countries.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMCountries extends ZMService {
    var $countries_;
    var $countriesById_;


    /**
     * Default c'tor.
     */
    function ZMCountries() {
        parent::__construct();

        $this->countries_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCountries();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get country for the given name.
     *
     * @param string name The country name.
     * @return ZMCountry The country or <code>null</code>.
     */
    function &getCountryForName($name) {
        $this->getCountries();
        foreach ($this->countries_ as $country) {
            if ($name == $country->name_) {
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
    function getCountries() {
        if (null != $this->countries_)
            return $this->countries_;

        $db = $this->getDB();
        $sql = "select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id
                from " . TABLE_COUNTRIES . "
                order by countries_name";

        $this->countries_ = array();
        $results = $db->Execute($sql);
        while (!$results->EOF) {
            $country = $this->create("Country");
            $country->id_ = $results->fields['countries_id'];
            $country->name_ = $results->fields['countries_name'];
            $country->isoCode2_ = $results->fields['countries_iso_code_2'];
            $country->isoCode3_ = $results->fields['countries_iso_code_3'];
            $country->addressFormatId_ = $results->fields['address_format_id'];
            array_push($this->countries_, $country);
            $results->MoveNext();
        }

        return $this->countries_;
    }


    /**
     * Get country for the given id.
     *
     * @param int id The country id.
     * @return ZMCountry The country or <code>null</code>.
     */
    function &getCountryForId($id) {
        $this->getCountries();
        foreach ($this->countries_ as $country) {
            if ($id == $country->id_) {
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
    function getZoneCode($countryId, $zoneId, $defaultZone='') {
        $db = $this->getDB();
        $sql = "select zone_code
                from " . TABLE_ZONES . "
                where zone_country_id = :countryId
                and zone_id = :zoneId";
        $sql = $db->bindVars($sql, ":countryId", $countryId, "integer");
        $sql = $db->bindVars($sql, ":zoneId", $zoneId, "integer");

        $results = $db->Execute($sql);
        if ($results->RecordCount() > 0) {
            return $results->fields['zone_code'];
        } else {
            return $defaultZone;
        }
    }


    /**
     * Get all zones for the given country id.
     *
     * @param int countryId The country id.
     * @return array List of <code>ZMZone</code> objects.
     */
    function getZonesForCountryId($countryId) {
        if (null == $countryId || '' == $countryId) {
            return array();
        }

        $db = $this->getDB();
        $sql = "select distinct zone_id, zone_code, zone_name
                  from " . TABLE_ZONES . "
                  where zone_country_id = :countryId
                  order by zone_name";
        $sql = $db->bindVars($sql, ":countryId", $countryId, "integer");
        $results = $db->Execute($sql);

        $zones = array();
        while (!$results->EOF) {
            $zone = $this->create("Zone");
            $zone->setId($results->fields['zone_id']);
            $zone->setCode($results->fields['zone_code']);
            $zone->setName($results->fields['zone_name']);
            $zones[$zone->getCode()] = $zone;
            $results->MoveNext();
        }

        return $zones;
    }

}

?>
