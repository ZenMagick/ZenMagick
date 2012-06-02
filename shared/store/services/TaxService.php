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
namespace zenmagick\apps\store\services;

use ZMAccount;
use ZMRuntime;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\apps\store\model\TaxRate;

/**
 * Tax rate service.
 *
 * <p>Rate values will have a precision that is 2 digits more than <em>$settingsService->get('calculationDecimals')</em>.</p>
 *
 * @author DerManoMann
 */
class TaxService extends ZMObject {
    private $taxRates;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->taxRates = array();
    }


    /**
     * Get tax for the given parameter.
     *
     * <p>If neither <code>countryId</code> nor <code>zoneId</code> are specified, the customers default address
     * details will be used, or, if not available, the store defaults.</p>
     *
     * @param int taxClassId The tax class id.
     * @param int countryId Optional country id; default is <em>0</em>.
     * @param int zoneId Optional zoneId; default is <em>0</em>.
     * @return TaxRate The tax rate.
     */
    public function getTaxRateForClassId($taxClassId, $countryId=0, $zoneId=0) {
        $settingsService = $this->container->get('settingsService');
        if (0 == $countryId && 0 == $zoneId) {
            $account = $this->container->get('request')->getAccount();
            if (null != $account && ZMAccount::ANONYMOUS == $account->getType()) {
                $defaultAddress = $this->container->get('addressService')->getAddressForId($account->getDefaultAddressId());
                if (null != $defaultAddress) {
                    $zoneId = $defaultAddress->getZoneId();
                    $countryId = $defaultAddress->getCountryId();
                } else {
                    $zoneId = $settingsService->get('storeZone');
                    $countryId = $settingsService->get('storeCountry');
                }
            } else {
                $zoneId = $settingsService->get('storeZone');
                $countryId = $settingsService->get('storeCountry');
            }
        }

        $taxRateId = $taxClassId.'_'.$countryId.'_'.$zoneId;
        if (isset($this->taxRates[$taxRateId])) {
            // cache hit
            return $this->taxRates[$taxRateId];
        }

        if (TaxRate::TAX_BASE_STORE == $settingsService->get('productTaxBase')) {
            if ($settingsService->get('storeZone') != $zoneId) {
                $taxRate = Beans::getBean('zenmagick\apps\store\model\TaxRate');
                $taxRate->setId($taxRateId);
                $taxRate->setClassId($taxClassId);
                $taxRate->setCountryId($countryId);
                $taxRate->setZoneId($zoneId);
                $taxRate->setRate(0);
                $this->taxRates[$taxRateId] = $taxRate;
                return $taxRate;
            }
        }

        $sql = "SELECT SUM(tax_rate) AS tax_rate
                FROM (" . TABLE_TAX_RATES . " tr
                  LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
                  LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id))
                WHERE (za.zone_country_id IS NULL OR za.zone_country_id = 0 OR za.zone_country_id = :countryId)
                  AND (za.zone_id IS NULL OR za.zone_id = 0 OR za.zone_id = :zoneId)
                  AND tr.tax_class_id = :taxClassId
                GROUP BY tr.tax_priority";
        $args = array('taxClassId' => $taxClassId, 'countryId' => $countryId, 'zoneId' => $zoneId);
        $results = ZMRuntime::getDatabase()->fetchAll($sql, $args, array('tax_rates', 'zones_to_geo_zones', 'geo_zones'));

        if (0 < count($results)) {
            $multiplier = 1.0;
            foreach ($results as $result) {
                $multiplier *= 1.0 + ($result['rate'] / 100);
            }

            $taxRate = Beans::getBean('zenmagick\apps\store\model\TaxRate');
            $taxRate->setId($taxRateId);
            $taxRate->setClassId($taxClassId);
            $taxRate->setCountryId($countryId);
            $taxRate->setZoneId($zoneId);
            $taxRate->setRate(($multiplier - 1.0) * 100);
            $this->taxRates[$taxRateId] = $taxRate;
            return $taxRate;
        }

        $taxRate = Beans::getBean('zenmagick\apps\store\model\TaxRate');
        $taxRate->setId($taxRateId);
        $taxRate->setClassId($taxClassId);
        $taxRate->setCountryId($countryId);
        $taxRate->setZoneId($zoneId);
        $taxRate->setRate(0);
        $this->taxRates[$taxRateId] = $taxRate;
        return $taxRate;
    }

    /**
     * Get the tax description for the give tax details.
     *
     * @param int taxClassId The tax class id.
     * @param int countryId The country id.
     * @param int zoneId The zoneId.
     * @return string The decription or <code>null</code>.
     */
    public function getTaxDescription($taxClassId, $countryId, $zoneId) {
        $sql = "SELECT tax_description
                FROM (" . TABLE_TAX_RATES . " tr
                  LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
                  LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id) )
                WHERE (za.zone_country_id IS NULL OR za.zone_country_id = 0 OR za.zone_country_id = :countryId)
                  AND (za.zone_id IS NULL OR za.zone_id = 0 OR za.zone_id = :zoneId)
                  AND tr.tax_class_id = :taxClassId
                ORDER BY tr.tax_priority";
        $args = array('taxClassId' => $taxClassId, 'countryId' => $countryId, 'zoneId' => $zoneId);
        $description = null;
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, array('tax_rates', 'zones_to_geo_zones', 'geo_zones')) as $result) {
            if (null !== $description) { $description .= _zm($this->container->get('settingsService')->get('tax.delim', ' + ')); }
            $description .= $result['description'];
        }
        return $description;
    }

    /**
     * Get the tax rate for the given description.
     *
     * @param string description The tax description.
     * @return float The tax rate.
     */
    public function getTaxRateForDescription($description) {
        $rate = 0.00;
        $settingsService = $this->container->get('settingsService');
        $descriptions = explode(_zm($settingsService->get('tax.delim', ' + ')), $description);
        foreach ($descriptions as $description) {
            $sql = "SELECT tax_rate
                    FROM " . TABLE_TAX_RATES . "
                    WHERE tax_description = :description";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('description' => $description), 'tax_rates');
            if (null != $result) {
                $rate += $result['rate'];
            }
        }

        // round 2 better as calculations use
        return round($rate, $settingsService->get('calculationDecimals') + 2);
    }

    /**
     * Get a tax class for the given id.
     *
     * @param int id The tax class id.
     * @return TaxClass A <code>TaxClass</code> instance or <code>null</code>.
     */
    public function getTaxClassForId($id) {
        $sql = "SELECT * FROM " . TABLE_TAX_CLASS . "
                WHERE tax_class_id = :taxClassId";
        return ZMRuntime::getDatabase()->querySingle($sql, array('taxClassId' => $id), 'tax_class', 'zenmagick\apps\store\model\TaxClass');
    }

    /**
     * Get all tax for the given parameter.
     *
     * <p>If neither <code>countryId</code> nor <code>zoneId</code> are specified, the customers default address
     * details will be used, or, if not available, the store defaults.</p>
     *
     * @param int taxClassId The tax class id.
     * @param int countryId Optional country id; default is <em>0</em>.
     * @param int zoneId Optional zoneId; default is <em>0</em>.
     * @return array List of <code>TaxRate</code> instances.
     */
    public function getTaxRatesForClassId($taxClassId, $countryId=0, $zoneId=0) {
        $settingsService = $this->container->get('settingsService');
        if (0 == $countryId && 0 == $zoneId) {
            $account = $this->container->get('request')->getAccount();
            if (null != $account && ZMAccount::ANONYMOUS == $account->getType()) {
                $defaultAddress = $this->container->get('addressService')->getAddressForId($account->getDefaultAddressId());
                if (null != $defaultAddress) {
                    $zoneId = $defaultAddress->getZoneId();
                    $countryId = $defaultAddress->getCountryId();
                } else {
                    $zoneId = $settingsService->get('storeZone');
                    $countryId = $settingsService->get('storeCountry');
                }
            } else {
                $zoneId = $settingsService->get('storeZone');
                $countryId = $settingsService->get('storeCountry');
            }
        }

        $sql = "SELECT tax_description, tax_rate, tax_priority
                  FROM (" . TABLE_TAX_RATES . " tr
                  LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
                  LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id) )
                  WHERE (za.zone_country_id IS NULL OR za.zone_country_id = 0
                    OR za.zone_country_id = :countryId)
                  AND (za.zone_id is null
                    OR za.zone_id = 0
                    OR za.zone_id = :zoneId)
                  AND tr.tax_class_id = :taxClassId
                  ORDER BY tr.tax_priority";
        $args = array('taxClassId' => $taxClassId, 'countryId' => $countryId, 'zoneId' => $zoneId);
        $results = ZMRuntime::getDatabase()->fetchAll($sql, $args, array('tax_rates', 'zones_to_geo_zones', 'geo_zones'));

        // calculate appropriate tax rates respecting priorities and compounding
        $taxRates = array();
        $aggregateRate = 1;
        $rateFactor = 1;
        $priorRate = 1;
        $priority = 0;
        foreach ($results as $ii => $result) {
            $result['priority'] = (int)$result['priority'];
            if ($result['priority'] > $tax_priority) {
                $priority = $result['priority'];
                $priorRate = $aggregateRate;
                $rateFactor = 1 + ($result['rate'] / 100);
                $rateFactor *= $aggregateRate;
                $aggregateRate = 1;
            } else {
                $rateFactor = $priorRate * ( 1 + ($result['rate'] / 100));
            }

            $taxRate = Beans::getBean('zenmagick\apps\store\model\TaxRate');
            $taxRate->setDescription($result['description']);
            $taxRate->setRate(100 * ($rateFactor - $priorRate));

            $taxRates[] = $taxRate;

            $aggregateRate += $rateFactor - 1;
        }

        return $taxRates;
    }

}
