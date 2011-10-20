<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Simple language class to replace ZenCart's currencies class
 */
class currencies {
    public $curencies;
    private $sessionCurrency;

    function __construct() {
        $this->currencies = array();
        $currencies = Runtime::getContainer()->get('currencyService')->getCurrencies();
        foreach ($currencies as $currency) {
            $this->currencies[$currency->getCode()] = array(
                'title' => $currency->getTitle(),
                'symbol_left' => $currency->getSymbolLeft(),
                'symbol_right' => $currency->getSymbolRight(),
                'decimal_point' => $currency->getDecimalPoint(),
                'thousands_point' => $currency->getThousandsPoint(),
                'decimal_places' => $currency->getDecimalPlaces(),
                'value' => $currency->getRate()
            );
        }
        $this->sessionCurrency = Runtime::getContainer()->get('session')->getValue('currency');
    }

    /**
     * Format.
     */
    function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {
        if (empty($currency_type)) $currency_type = $this->sessionCurrency;

        if ($calculate_currency_value == true) {
          $rate = (zen_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
          $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format(zen_round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
        } else {
          $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format(zen_round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
        }

        return $format_string;
    }

    /**
     * Adjust rate.
     */
    function rateAdjusted($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {

    if (empty($currency_type)) $currency_type = $_SESSION['currency'];

    if ($calculate_currency_value == true) {
      $rate = (zen_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
      $result = zen_round($number * $rate, $this->currencies[$currency_type]['decimal_places']);
    } else {
      $result = zen_round($number, $this->currencies[$currency_type]['decimal_places']);
    }
    return $result;
  }

    /**
     * Convert value.
     */
    function value($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {

    if (empty($currency_type)) $currency_type = $_SESSION['currency'];

    if ($calculate_currency_value == true) {
      if ($currency_type == DEFAULT_CURRENCY) {
        $rate = (zen_not_null($currency_value)) ? $currency_value : 1/$this->currencies[$_SESSION['currency']]['value'];
      } else {
        $rate = (zen_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
      }
      $currency_value = zen_round($number * $rate, $this->currencies[$currency_type]['decimal_places']);
    } else {
      $currency_value = zen_round($number, $this->currencies[$currency_type]['decimal_places']);
    }

    return $currency_value;
  }

    /**
     * Check for currency.
     */
    function is_set($code) {
        return (isset($this->currencies[$code]) && zen_not_null($this->currencies[$code]));
    }

    /**
     * Get value.
     */
    function get_value($code) {
        return $this->currencies[$code]['value'];
    }

    /**
     * Get decimal places.
     */
    function get_decimal_places($code) {
        return $this->currencies[$code]['decimal_places'];
    }

    /**
     * Format.
     */
    function display_price($products_price, $products_tax, $quantity=1) {
        return $this->format(zen_add_tax($products_price, $products_tax) * $quantity);
    }

}
