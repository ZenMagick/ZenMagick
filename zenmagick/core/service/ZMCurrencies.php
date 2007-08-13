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
 * Currencies.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMCurrencies extends ZMService {
    var $currencies_;


    /**
     * Default c'tor.
     */
    function ZMCurrencies() {
        parent::__construct();

        $this->currencies_ = array();
        $this->_load();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCurrencies();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Load all currencies.
     */
    function _load() {
        $db = $this->getDB();
        $sql = "select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value
                from " . TABLE_CURRENCIES;

        $db = $this->getDB();
        $results = $db->Execute($sql);

        while (!$results->EOF) {
            $currency = $this->_newCurrency($results->fields);
            $this->currencies_[$currency->getId()] = $currency;
            $results->MoveNext();
        }

    }

    /**
     * Get all currencies.
     *
     * @return array A list of <code>ZMCurrency</code> objects.
     */
    function getCurrencies() { return $this->currencies_; }

    /**
     * Get the currency for the given id.
     *
     * @param int id The currency id.
     * @return ZMCurrency A currency or <code>null</code>.
     */
    function &getCurrencyForId($id) { return isset($this->currencies_[$id]) ? $this->currencies_[$id] : null; }

    /**
     * Checks if a currency exists for the given id.
     *
     * @param int id The currency id.
     * @return boolean <code>true</code> if a currency exists for the given id, <code>false</code> if not.
     */
    function isValid($id) {
        return null !== $this->getCurrencyForId($id);
    }

    /**
     * Create new currency instance.
     */
    function &_newCurrency($fields) {
        $currency = $this->create("Currency", $fields['code'], $fields);
        return $currency;
    }

}

?>
