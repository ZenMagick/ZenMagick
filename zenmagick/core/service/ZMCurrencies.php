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
 * Currencies.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMCurrencies extends ZMObject {
    public static $CURRENCIES_MAPPING = null;
    private $currencies_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->currencies_ = array();
        if (null == ZMCurrencies::$CURRENCIES_MAPPING) {
            ZMCurrencies::$CURRENCIES_MAPPING = array(
              'id' => 'column=currencies_id;type=integer;key=true;primary=true',
              'title' => 'column=title;type=string',
              'code' => 'column=code;type=string',
              'symbolLeft' => 'column=symbol_left;type=string',
              'symbolRight' => 'column=symbol_right;type=string',
              'decimalPoint' => 'column=decimal_point;type=string',
              'thousandsPoint' => 'column=thousands_point;type=string',
              'decimalPlaces' => 'column=decimal_places;type=integer',
              'rate' => 'column=value;type=float'/*,
              'lastUpdate' => 'column=last_updated;type=date'*/
            );
            ZMCurrencies::$CURRENCIES_MAPPING = ZMDbUtils::addCustomFields(ZMCurrencies::$CURRENCIES_MAPPING, TABLE_CURRENCIES);
        }
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
        $sql = "SELECT currencies_id, code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value
                 ".ZMDbUtils::getCustomFieldsSQL(TABLE_CURRENCIES)."
                FROM " . TABLE_CURRENCIES;

        foreach (ZMRuntime::getDatabase()->query($sql, array(), ZMCurrencies::$CURRENCIES_MAPPING, 'Currency') as $currency) {
            $this->currencies_[$currency->getCode()] = $currency;
        }
    }

    /**
     * Get all currencies.
     *
     * @return array A list of <code>ZMCurrency</code> objects.
     */
    public function getCurrencies() { return $this->currencies_; }

    /**
     * Get the currency for the given code.
     *
     * @param string code The currency code.
     * @return ZMCurrency A currency or <code>null</code>.
     */
    public function getCurrencyForCode($code) { return isset($this->currencies_[$code]) ? $this->currencies_[$code] : null; }

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
