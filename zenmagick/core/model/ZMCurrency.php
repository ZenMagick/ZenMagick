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
     * Format the given value according to this currency's rate(value) and formatting rules.
     *
     * @param float value The value.
     * @return string The formatted value.
     */
    function format($value) {
        $ratedValue = zen_round($value * $this->rate_, $this->decimalPlaces_);
        $formattedValue = number_format($ratedValue, $this->decimalPlaces_, $this->decimalPoint_, $this->thousandsPoint_);
        return $this->symbolLeft_ .  $formattedValue . $this->symbolRight_;
    }

}

?>
