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
 * Countries.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMCountries extends ZMDao {
    var $countries_;
    var $countriesById_;


    // create new instance
    function ZMCountries() {
        parent::__construct();

        $this->countries_ = null;
    }

    // create new instance
    function __construct() {
        $this->ZMCountries();
    }

    function __destruct() {
    }


    function getCountryForName($name) {
        $this->getCountries();
        foreach ($this->countries_ as $country) {
            if ($name == $country->name_) {
                return $country;
            }
        }
        return null;
    }


    function getCountries() {
        if (null != $this->countries_)
            return $this->countries_;

        $sql = "select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id
                from " . TABLE_COUNTRIES . "
                order by countries_name";

        $this->countries_ = array();
        $results = $this->db_->Execute($sql);
        while (!$results->EOF) {
            $country =& $this->create("Country");
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


    function getCountryForId($id) {
        $this->getCountries();
        foreach ($this->countries_ as $country) {
            if ($id == $country->id_) {
                return $country;
            }
        }
        return null;
    }


    function getZoneCode($countryId, $zoneId, $defaultZone='') {
        $sql = "select zone_code
                from " . TABLE_ZONES . "
                where zone_country_id = :countryId
                and zone_id = :zoneId";
        $sql = $this->db_->bindVars($sql, ":countryId", $countryId, "integer");
        $sql = $this->db_->bindVars($sql, ":zoneId", $zoneId, "integer");

        $results = $this->db_->Execute($sql);
        if ($results->RecordCount() > 0) {
            return $results->fields['zone_code'];
        } else {
            return $defaultZone;
        }
    }


    function getZonesForCountryId($countryId) {
        if (null == $countryId || '' == $countryId) {
            return array();
        }
        $sql = "select distinct zone_code, zone_name
                  from " . TABLE_ZONES . "
                  where zone_country_id = :countryId
                  order by zone_name";
        $sql = $this->db_->bindVars($sql, ":countryId", $countryId, "integer");
        $results = $this->db_->Execute($sql);

        $zones = array();
        while (!$results->EOF) {
            $zone =& $this->create("IdNamePair", $results->fields['zone_code'], $results->fields['zone_name']);
            $zones[$zone->getId()] = $zone;
            $results->MoveNext();
        }

        return $zones;
    }

}

?>
