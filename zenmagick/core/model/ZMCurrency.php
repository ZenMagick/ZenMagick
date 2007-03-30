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
    var $id_;
    var $name_;
    var $symbolLeft_;
    var $symbolRight_;
    var $decimalPoint_;
    var $thousandsPoint_;
    var $decimalPlaces_;
    var $value_;


    /**
     * Default c'tor.
     */
    function ZMCurrency($id, $arr) {
        parent::__construct();

        $this->id_ = $id;
        $this->name_ = $arr['title'];
        $this->symbolLeft_ = $arr['symbol_left'];
        $this->symbolRight_ = $arr['symbol_right'];
        $this->decimalPoint_ = $arr['decimal_point'];
        $this->thousandsPoint_ = $arr['thousands_point'];
        $this->decimalPlaces_ = $arr['decimal_places'];
        $this->value_ = $arr['value'];
    }

    /**
     * Default c'tor.
     */
    function __construct($id, $arr) {
        $this->ZMCurrency($id, $arr);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the currency id.
     *
     * @return int The currency id.
     */
    function getId() { return $this->id_; }

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
     * Get the currency value.
     *
     * @return double The currency value.
     */
    function getValue() { return $this->value_; }

}

?>
