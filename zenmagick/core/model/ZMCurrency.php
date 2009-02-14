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
 * A single currency.
 *
 * @author DerManoMann
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMCurrency extends ZMModel {
    private $code_;
    private $name_;
    private $symbolLeft_;
    private $symbolRight_;
    private $decimalPoint_;
    private $thousandsPoint_;
    private $decimalPlaces_;
    private $rate_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->setId(0);
        $this->code_ = '';
        $this->name_ = '';
        $this->decimalPlaces_ = 2;
        $this->thousandsPoint_ = '';
        $this->rate_ = 1;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the currency id.
     *
     * @return int The currency id.
     */
    public function getId() { return $this->get('currencyId'); }

    /**
     * Get the currency code.
     *
     * @return string The currency code.
     */
    public function getCode() { return $this->code_; }

    /**
     * Get the currency name.
     *
     * @return string The currency name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the currency symbox (left).
     *
     * @return string The currency symbol (left).
     */
    public function getSymbolLeft() { return $this->symbolLeft_; }

    /**
     * Get the currency symbox (right).
     *
     * @return string The currency symbol (right).
     */
    public function getSymbolRight() { return $this->symbolRight_; }

    /**
     * Get the currency decimal point.
     *
     * @return string The currency decimal point.
     */
    public function getDecimalPoint() { return $this->decimalPoint_; }

    /**
     * Get the currency thousands point.
     *
     * @return string The currency thousands point.
     */
    public function getThousandsPoint() { return $this->thousandsPoint_; }

    /**
     * Get the currency decimal places.
     *
     * @return int The currency decimal places.
     */
    public function getDecimalPlaces() { return $this->decimalPlaces_; }

    /**
     * Get the currency rate.
     *
     * <p>This is the rate in relation to the default currency.</p>
     *
     * @return double The currency rate.
     */
    public function getRate() { return $this->rate_; }

    /**
     * Set the currency id.
     *
     * @param int id The currency id.
     */
    public function setId($id) { $this->set('currencyId', $id); }

    /**
     * Set the currency code.
     *
     * @param string code The currency code.
     */
    public function setCode($code) { $this->code_ = $code; }

    /**
     * Set the currency name.
     *
     * @param string name The currency name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the currency symbox (left).
     *
     * @param string symbol The currency symbol (left).
     */
    public function setSymbolLeft($symbol) { $this->symbolLeft_ = $symbol; }

    /**
     * Set the currency symbox (right).
     *
     * @param string symbol The currency symbol (right).
     */
    public function setSymbolRight($symbol) { return $this->symbolRight_ = $symbol; }

    /**
     * Set the currency decimal point.
     *
     * @param string point The currency decimal point.
     */
    public function setDecimalPoint($point) { $this->decimalPoint_ = $point; }

    /**
     * Set the currency thousands point.
     *
     * @param string point The currency thousands point.
     */
    public function setThousandsPoint($point) { $this->thousandsPoint_ = $point; }

    /**
     * Set the currency decimal places.
     *
     * @param int decimals The currency decimal places.
     */
    public function setDecimalPlaces($decimals) { $this->decimalPlaces_ = $decimals; }

    /**
     * Set the currency rate.
     *
     * <p>This is the rate in relation to the default currency.</p>
     *
     * @param double rate The currency rate.
     */
    public function setRate($rate) { $this->rate_ = $rate; }

    /**
     * Format the given amount according to this currency's rate and formatting rules.
     *
     * @param float amount The amount.
     * @param boolean convert If <code>true</code>, consider <code>$amount</code> to be in default currency and
     *  convert before formatting.
     * @return string The formatted amount.
     */
    function format($amount, $convert=true) {
        $ratedValue = $convert ? $this->convertTo($amount) : $amount;
        $formattedAmount = number_format($ratedValue, $this->decimalPlaces_, $this->decimalPoint_, $this->thousandsPoint_);
        return $this->symbolLeft_ .  $formattedAmount . $this->symbolRight_;
    }

    /**
     * Convert from default currency into this currency.
     *
     * @param float amount The amount in the default currency.
     * @return float The converted amount.
     */
    function convertTo($amount) {
        return round($amount * $this->rate_, $this->decimalPlaces_);
    }

    /**
     * Convert from this currency into default currency.
     *
     * @param float amount The amount in this currency.
     * @return float The converted amount.
     */
    function convertFrom($amount) {
        return round($amount * (1/$this->rate_), $this->decimalPlaces_);
    }

    /**
     * Parse a formatted currency amount.
     *
     * @param string value The formatted currency value.
     * @return float The amount.
     */
    function parse($value) {
        $value = preg_replace('/[^0-9\\'.$this->decimalPoint_.']/', '', $value);
        $value = str_replace($this->decimalPoint_, '.', $value);

        if (0 != preg_match('[^0-9\.]', $value)) {
            return null;
        }

        return (float)$value;
    }

}

?>
