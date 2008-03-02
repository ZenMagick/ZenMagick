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
 * Info for a single tax rate.
 *
 * <p>The tax rate id is build from the tax classId, countryId and zoneId to make it unique.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMTaxRate extends ZMModel {
    var $id_;
    var $classId_;
    var $countryId_;
    var $zoneId_;
    var $rate_;
    var $description_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->id_ = null;
        $this->rate_ = 0.00;
        $this->description_ = null;
        $this->classId_ = 0;
        $this->countryId_ = 0;
        $this->zoneId_ = 0;
    }

    /**
     * Create new instance.
     */
    function ZMCurrency() {
        $this->__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the tax rate idendtifier
     *
     * @return string The tax rate idendtifier.
     */
    function getId() { return $this->id_; }

    /**
     * Set the tax rate idendtifier
     *
     * @param string id The tax rate idendtifier.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Get the tax descrption.
     *
     * @return string The tax description.
     */
    function getDescription() { 
        if (null == $this->description_) {
            $this->description_ = ZMTaxRates::instance()->getTaxDescription($this->classId_, $this->countryId_, $this->zoneId_);
        }
        return $this->description_; 
    }

    /**
     * Get the tax rate.
     *
     * @return float The tax rate.
     */
    function getRate() { return $this->rate_; }

    /**
     * Set the tax description.
     *
     * @param string description The tax description.
     */
    function setDescription($description) { $this->description_ = $description; }

    /**
     * Set the tax rate.
     *
     * @param float rate The tax rate.
     */
    function setRate($rate) { $this->rate_ = $rate; }

    /**
     * Get the tax class id.
     *
     * @return int The tax class id or <em>0</em>.
     */
    function getClassId() { return $this->classId_; }

    /**
     * Set the tax class id.
     *
     * @param int classId The tax class id.
     */
    function setClassId($classId) { $this->classId_ = $classId; }

    /**
     * Get the country id.
     *
     * @return int The country id or <em>0</em>.
     */
    function getCountryId() { return $this->countryId_; }

    /**
     * Set the country id.
     *
     * @param int countryId The country id.
     */
    function setCountryId($countryId) { $this->countryId_ = $countryId; }

    /**
     * Get the zone id.
     *
     * @return int The zone id or <em>0</em>.
     */
    function getZoneId() { return $this->zoneId_; }

    /**
     * Set the zone id.
     *
     * @param int zoneId The zone id.
     */
    function setZoneId($zoneId) { $this->zoneId_ = $zoneId; }

    /**
     * Add tax to the given amount.
     *
     * @param double amount The amount.
     * @return double The amount incl. tax.
     */
    function addTax($amount) {
        $currency = $this->_getCurrency();
        if (zm_setting('isTaxInclusive') && 0 < $this->rate_) {
            return zen_round($amount, $currency->getDecimalPlaces()) + $this->calculateTax($amount);
        }

        return zen_round($amount, $currency->getDecimalPlaces());
    }

    /**
     * Caclulate tax for the given amount.
     *
     * @param double amount The amount.
     * @return double The tax value.
     */
    function calculateTax($amount) {
        $currency = $this->_getCurrency();
        return zen_round($amount * $this->rate_ / 100, $currency->getDecimalPlaces());
    }

    /**
     * Get the best matching currency.
     *
     * @return ZMCurrency A currency.
     */
    function _getCurrency() {
        $currency = ZMRuntime::getCurrency();
        if (null == $currency) {
            $this->log('no currency found - using default currency', ZM_LOG_WARN);
            $currency = ZMCurrencies::instance()->getCurrencyForCode(zm_setting('defaultCurrency'));
        }

        return $currency;
    }

}

?>
