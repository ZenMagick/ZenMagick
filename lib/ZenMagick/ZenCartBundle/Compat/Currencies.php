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

namespace ZenMagick\ZenCartBundle\Compat;

use ZenMagick\StoreBundle\Entity\Currency;
use ZenMagick\StoreBundle\Services\Locale\CurrencyService;

/**
 * Class to handle currencies
 *
 * CHANGES:
 * loads data via  ZenMagick's StoreBundle CurrencyService
 * removed unused is_set, rateAdjusted methods
 * works with both admin and storefront
 */
class Currencies extends Base
{
    /**
     * @var array list of currency values often accessed directly!
     */
    public $currencies = array();

    private $hidePrices;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencies = array();
        foreach ((array) $currencyService->getCurrencies() as $currency) {
            $this->currencies[$currency->getCode()] = $this->toArray($currency);
        }
    }

    /**
     * Create an array that matches the currencies table structure
     *
     * @param Currency
     * @return array
     */
    protected function toArray(Currency $currency)
    {
        return array(
                'title' => $currency->getTitle(),
                'symbol_left' => $currency->getSymbolLeft(),
                'symbol_right' => $currency->getSymbolRight(),
                'decimal_point' => $currency->getDecimalPoint(),
                'thousands_point' => $currency->getThousandsPoint(),
                'decimal_places' => $currency->getDecimalPlaces(),
                'value' => $currency->getRate()
        );
    }

    /**
     * Should the prices be hidden?
     *
     * @todo get the DFM data from ZenMagick
     */
    protected function pricesHidden()
    {
        if (null != $this->hidePrices) {
            return $this->hidePrices;
        }

        $this->hidePrices = false;
        if (!IS_ADMIN_FLAG &&
            (DOWN_FOR_MAINTENANCE=='true' &&
             DOWN_FOR_MAINTENANCE_PRICES_OFF=='true') &&
            (!strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $this->getRequest()->getClientIp()))) {
            $this->hidePrices = true;
        }

        return $this->hidePrices;
    }

    /**
     * Toggle price hiding in storefront
     *
     * @param bool $hidePrices
     */
    public function setHidePrices($hidePrices)
    {
        $this->hidePrices = $hidePrices;
    }

    /**
     * Display a price in a currency
     *
     * @todo use <code>Currency::format()</code>
     */
    public function format($number, $calculate_value = true, $currency_type = '', $currency_value = 0)
    {
        if ($this->pricesHidden()) {
            return '';
        }

        // ZenCart only does this in storefront
        if (empty($currency_type) && $this->getSessionVar('currency')) {
            $currency_type = $this->getSessionVar('currency');
        }

        if (empty($currency_type)) { // ZenCart only does this in admin
            $currency_type = DEFAULT_CURRENCY;
        }

        extract($this->currencies[$currency_type]);
        if ($calculate_value) {
            $rate = $currency_value ?: $value;
            $amount = $number * $rate;
        } else {
            $amount = $number;
        }
        if (!IS_ADMIN_FLAG) {
            $amount = zen_round($amount, $decimal_places);
        }
        $formatted = number_format($amount, $decimal_places, $decimal_point, $thousands_point);

        return $symbol_left.$formatted.$symbol_right;

    }

    /**
     * Only exists in storefront
     *
     * @copyright Copyright 2003-2010 Zen Cart Development Team
     * @copyright Portions Copyright 2003 osCommerce
     * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
     */
    public function value($number, $calculate_value = true, $currency_type = '', $currency_value = '')
    {
        if (empty($currency_type)) $currency_type = $this->getSessionVar('currency');

        if ($calculate_value) {
            if ($currency_type == DEFAULT_CURRENCY) {
                $rate = $currency_value ?: 1/$this->currencies[$this->getSessionVar('currency')]['value'];
            } else {
                $rate = $currency_value ?: $this->currencies[$currency_type]['value'];
            }
            $currency_value = zen_round($number * $rate, $this->currencies[$currency_type]['decimal_places']);
        } else {
            $currency_value = zen_round($number, $this->currencies[$currency_type]['decimal_places']);
        }

        return $currency_value;
    }

    public function get_value($code)
    {
        return $this->currencies[$code]['value'];
    }

    public function get_decimal_places($code)
    {
        return $this->currencies[$code]['decimal_places'];
    }

    public function display_price($price, $tax, $quantity = 1)
    {
        return $this->format(zen_add_tax($price, $tax) * $quantity);
    }
}
