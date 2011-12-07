<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Currencies.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.locale
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
        return Runtime::getContainer()->get('currencyService');
    }


    /**
     * Load all currencies.
     */
    private function load() {
        $sql = "SELECT * FROM " . TABLE_CURRENCIES;
        $this->currencies = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array(), TABLE_CURRENCIES, 'ZMCurrency') as $currency) {
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
