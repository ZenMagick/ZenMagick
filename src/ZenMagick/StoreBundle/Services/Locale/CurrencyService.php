<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\StoreBundle\Services\Locale;

use ZenMagick\Base\ZMObject;

/**
 * Currency service.
 *
 * @author DerManoMann
 */
class CurrencyService extends ZMObject
{
    private $currencies;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load();
    }

    /**
     * Load all currencies.
     */
    private function load()
    {
        $sql = "SELECT * FROM %table.currencies%";
        $this->currencies = array();
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array(), 'currencies', 'ZenMagick\StoreBundle\Entity\Currency') as $currency) {
            $this->currencies[$currency->getCode()] = $currency;
        }
    }

    /**
     * Get all currencies.
     *
     * @return array A list of <code>Currency</code> objects.
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * Get the currency for the given code.
     *
     * @param string code The currency code.
     * @return Currency A <code>Currency</code> or <code>null</code>.
     */
    public function getCurrencyForCode($code)
    {
        return isset($this->currencies[$code]) ? $this->currencies[$code] : null;
    }

    /**
     * Checks if a currency exists for the given code.
     *
     * @param string code The currency code.
     * @return boolean <code>true</code> if a currency exists for the given code, <code>false</code> if not.
     */
    public function isValid($code)
    {
        return null !== $this->getCurrencyForCode($code);
    }

}
