<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Tax rates.
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMTaxRates extends ZMObject {
    var $taxRates_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->taxRates_ = array();
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
        return parent::instance('TaxRates');
    }


    /**
     * Get tax for the given parameter.
     *
     * <p>If neither <code>countryId</code> nor <code>zoneId</code> are specified, the customers default address
     * details will be used, or if not available, the store defaults.</p>
     *
     * @param int classId The tax class id.
     * @param int countryId Optional country id; default is <em>0</em>.
     * @param int zoneId Optional zoneId; default is <em>0</em>.
     * @return ZMTaxRate The tax rate.
     */
    function getTaxRateForClassId($classId, $countryId=0, $zoneId=0) {
        if (0 == $countryId && 0 == $zoneId) {
            $account = ZMRequest::getAccount();
            if (null != $account && ZM_ACCOUNT_TYPE_REGISTERED == $account->getType()) {
                $defaultAddress = ZMAddresses::instance()->getAddressForId($account->getDefaultAddressId());
                $zoneId = $defaultAddress->getZoneId();
                $countryId = $defaultAddress->getCountryId();
            } else {
                $zoneId = ZMSettings::get('storeZone');
                $countryId = ZMSettings::get('storeCountry');
            }
        }

        $taxRateId = $classId.'_'.$countryId.'_'.$zoneId;
        if (isset($this->taxRates_[$taxRateId])) {
            // cache hit
            return $this->taxRates_[$taxRateId];
        }

        if (ZM_PRODUCT_TAX_BASE_STORE == ZMSettings::get('productTaxBase')) {
            if (ZMSettings::get('storeZone') != $zoneId) {
                $taxRate = $this->create("TaxRate");
                $taxRate->setId($taxRateId);
                $taxRate->setClassId($classId);
                $taxRate->setCountryId($countryId);
                $taxRate->setZoneId($zoneId);
                $taxRate->setRate(0);
                $this->taxRates_[$taxRateId] = $taxRate;
                return $taxRate;
            }
        }

        $db = ZMRuntime::getDB();
        $sql = "select sum(tax_rate) as tax_rate
                from (" . TABLE_TAX_RATES . " tr
                left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id)
                left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id))
                where (za.zone_country_id is null
                  or za.zone_country_id = 0
                  or za.zone_country_id = :countryId)
                and (za.zone_id is null
                  or za.zone_id = 0
                  or za.zone_id = :zoneId)
                and tr.tax_class_id = :classId
                group by tr.tax_priority";
        $sql = $db->bindVars($sql, ":countryId", $countryId, "integer");
        $sql = $db->bindVars($sql, ":zoneId", $zoneId, "integer");
        $sql = $db->bindVars($sql, ":classId", $classId, "integer");
        $results = $db->Execute($sql);

        if ($results->RecordCount() > 0) {
            $multiplier = 1.0;
            while (!$results->EOF) {
                $multiplier *= 1.0 + ($results->fields['tax_rate'] / 100);
                $results->MoveNext();
            }

            $taxRate = $this->create("TaxRate");
            $taxRate->setId($taxRateId);
            $taxRate->setClassId($classId);
            $taxRate->setCountryId($countryId);
            $taxRate->setZoneId($zoneId);
            $taxRate->setRate(($multiplier - 1.0) * 100);
            $this->taxRates_[$taxRateId] = $taxRate;
            return $taxRate;
        }

        $taxRate = $this->create("TaxRate");
        $taxRate->setId($taxRateId);
        $taxRate->setClassId($classId);
        $taxRate->setCountryId($countryId);
        $taxRate->setZoneId($zoneId);
        $taxRate->setRate(0);
        $this->taxRates_[$taxRateId] = $taxRate;
        return $taxRate;
    }

    /**
     * Get the tax description for the give tax details.
     *
     * @param int classId The tax class id.
     * @param int countryId The country id.
     * @param int zoneId The zoneId.
     * @return string The decription or <code>null</code>.
     */
    function getTaxDescription($classId, $countryId, $zoneId) {
        $db = ZMRuntime::getDB();
        $sql = "select tax_description
                from (" . TABLE_TAX_RATES . " tr
                left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id)
                left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) )
                where (za.zone_country_id is null
                  or za.zone_country_id = 0
                  or za.zone_country_id = :countryId)
                and (za.zone_id is null
                  or za.zone_id = 0
                  or za.zone_id = :zoneId)
                and tr.tax_class_id = :classId
                order by tr.tax_priority";
        $sql = $db->bindVars($sql, ":countryId", $countryId, "integer");
        $sql = $db->bindVars($sql, ":zoneId", $zoneId, "integer");
        $sql = $db->bindVars($sql, ":classId", $classId, "integer");

        $results = $db->Execute($sql);
        if ($tax->RecordCount() > 0) {
            $description = '';
            $first = true;
            while (!$results->EOF) {
                if (!$first) { $description .= _zm_l10n_lookup('tax.delim', ' + '); }
                $description .= $tax->fields['tax_description'];
                $results->MoveNext();
                $first = false;
            }
            return $description;
        }

        return null;
    }

    /**
     * Get the tax rate for the given description.
     *
     * @param string description The tax description.
     * @return float The tax rate.
     */
    function getTaxRateForDescription($description) {
        $rate = 0.00;
        $descriptions = explode(_zm_l10n_lookup('tax.delim', ' + '), $description);

        $db = ZMRuntime::getDB();
        foreach ($descriptions as $desc) {
            $sql = "SELECT tax_rate FROM " . TABLE_TAX_RATES . "
                    WHERE tax_description = :desc";
            $sql = $db->bindVars($sql, ':desc', $desc, 'string'); 
            $results = $db->Execute($sql);
            $rate += $tax->fields['tax_rate'];
        }

        return $rate;
    }

}

?>
