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
 * A single currency.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMCurrency extends ZMModel {
    var $code_;
    var $name_;
    var $symbolLeft_;
    var $symbolRight_;
    var $decimalPoint_;
    var $thousandsPoint_;
    var $decimalPlaces_;
    var $rate_;


    /**
     * Default c'tor.
     */
    function ZMCurrency() {
        parent::__construct();

        $this->code_ = '';
        $this->name_ = '';
        $this->decimalPlaces_ = 2;
        $this->thousandsPoint_ = '';
        $this->rate_ = 1;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCurrency();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the currency code.
     *
     * @return string The currency code.
     */
    function getId() { return $this->code_; }

    /**
     * Get the currency name.
     *
     * @return string The currency name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the currency symbox (left).
     *
     * @return string The currency symbol (left).
     */
    function getSymbolLeft() { return $this->symbolLeft_; }

    /**
     * Get the currency symbox (right).
     *
     * @return string The currency symbol (right).
     */
    function getSymbolRight() { return $this->symbolRight_; }

    /**
     * Get the currency decimal point.
     *
     * @return string The currency decimal point.
     */
    function getDecimalPoint() { return $this->decimalPoint_; }

    /**
     * Get the currency thousands point.
     *
     * @return string The currency thousands point.
     */
    function getThousandsPoint() { return $this->thousandsPoint_; }

    /**
     * Get the currency decimal places.
     *
     * @return int The currency decimal places.
     */
    function getDecimalPlaces() { return $this->decimalPlaces_; }

    /**
     * Get the currency rate.
     *
     * <p>This is the rate in relation to the default currency.</p>
     *
     * @return double The currency rate.
     */
    function getRate() { return $this->rate_; }

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
        return zen_round($amount * $this->rate_, $this->decimalPlaces_);
    }

    /**
     * Convert from this currency into default currency.
     *
     * @param float amount The amount in this currency.
     * @return float The converted amount.
     */
    function convertFrom($amount) {
        return zen_round($amount * (1/$this->rate_), $this->decimalPlaces_);
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
