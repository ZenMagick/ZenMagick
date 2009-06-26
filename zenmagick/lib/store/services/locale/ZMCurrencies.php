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
 * Currencies.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.locale
 * @version $Id: ZMCurrencies.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
class ZMCurrencies extends ZMObject {
    private $currencies;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->load();
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
        return ZMObject::singleton('Currencies');
    }


    /**
     * Load all currencies.
     */
    private function load() {
        $sql = "SELECT * FROM " . TABLE_CURRENCIES;
        $this->currencies = array();
        foreach (Runtime::getDatabase()->query($sql, array(), TABLE_CURRENCIES, 'Currency') as $currency) {
            $this->currencies[$currency->getCode()] = $currency;
        }
    }

    /**
     * Get all currencies.
     *
     * @return array A list of <code>ZMCurrency</code> objects.
     */
    public function getCurrencies() { return $this->currencies; }

    /**
     * Get the currency for the given code.
     *
     * @param string code The currency code.
     * @return ZMCurrency A currency or <code>null</code>.
     */
    public function getCurrencyForCode($code) { return isset($this->currencies[$code]) ? $this->currencies[$code] : null; }

    /**
     * Checks if a currency exists for the given code.
     *
     * @param string code The currency code.
     * @return boolean <code>true</code> if a currency exists for the given code, <code>false</code> if not.
     */
    public function isValid($code) {
        return null !== $this->getCurrencyForId($code);
    }

}

?>
