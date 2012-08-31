<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 Edwin Bekaert (edwin@ednique.com)
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
namespace ZenMagick\apps\storefront\utils;

use ZenMagick\base\Runtime;
use ZenMagick\base\ZMObject;

/**
 * Shipping estimator.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @deprecated use ZMShippingProviders::getShippingProvidersForAddress instead
 */
class ShippingEstimator extends ZMObject {

    function getRequest() {
        return Runtime::getContainer()->get('request');
    }

    /**
     * Get the postcode.
     *
     * @return string The postcode for the current calculations.
     */
    function getPostcode() {
        $request = $this->getRequest();
        $zip_code = (isset($_SESSION['cart_zip_code'])) ? $_SESSION['cart_zip_code'] : '';
        $zip_code = $request->request->has('zip_code') ? strip_tags(addslashes($request->request->get('zip_code'))) : $zip_code;
        return $zip_code;
    }

    /**
     * Get the state id.
     *
     * @return int The state id for the current shipping calculation.
     */
    function getStateId() {
        $request = $this->getRequest();
        $state_zone_id = (isset($_SESSION['cart_zone'])) ? (int)$_SESSION['cart_zone'] : '';
        $state_zone_id = $request->request->has('state') ? $request->request->get('state') : $state_zone_id;
        return $state_zone_id;
    }

    /**
     * Get the country id.
     *
     * @return int The country id for the current address.
     */
    function getCountryId() {
        $countryId = Runtime::getSettings()->get('storeCountry');
        $request = $this->getRequest();
        if ($request->request->has('country_id')) {
            $countryId = $request->request->get('country_id');
        } else if ($_SESSION['cart_country_id']) {
            $countryId = $_SESSION['cart_country_id'];
        }
        return $countryId;
    }

    /**
     * Get address id.
     *
     * @return int The address id for the current address (if any); default is <code>0</code>.
     */
    function _getAddressId() {
        $addressId = 0;
        $request = $this->getRequest();
        if ($request->request->get('address_id')) {
            $addressId = $request->request->get('address_id');
        } elseif ($_SESSION['cart_address_id']) {
            $addressId = $_SESSION['cart_address_id'];
        } else {
            $addressId = $_SESSION['customer_default_address_id'];
        }

        return $addressId;
    }

    /**
     * Get a zen-cart style address (array).
     *
     * @return array Array containing a zen-cart style address.
     */
    function getZCAddress() {
    global $order, $country_info;
        $address = null;
        $countryService = Runtime::getContainer()->get('countryService');
        $request = $this->getRequest();
        $countryId = $request->request->get('country_id');
        if (null != $countryId) {
            // country is selected
            $country_info = $_SESSION['country_info'];
            $address = array('postcode' => $this->getPostcode(),
                'country' => array('id' => $countryId, 'title' => $country_info['countries_name'],
                'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                'country_id' => $countryId,
                //add state zone_id
                'zone_id' => $this->getStateId(),
                'format_id' => $countryService->getCountryForId($countryId)->getAddressFormatId());
            $_SESSION['cart_country_id'] = $countryId;
            //add state zone_id
            $_SESSION['cart_zone'] = $this->getStateId();
            $_SESSION['cart_zip_code'] = $this->getPostcode();
        } elseif ($_SESSION['cart_country_id']){
            // session is available
            $country_info = $_SESSION['country_info'];
            // fix here - check for error on $cart_country_id
            $address = array('postcode' => $_SESSION['cart_zip_code'],
                'country' => array('id' => $_SESSION['cart_country_id'], 'title' => $country_info['countries_name'],
                'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                'country_id' => $_SESSION['cart_country_id'],
                'format_id' => $countryService->getCountryForId($_SESSION['cart_country_id'])->getAddressFormatId());
        } else {
            // first timer
            $_SESSION['cart_country_id'] = Runtime::getSettings()->get('storeCountry');
            $country_info = $_SESSION['country_info'];
            $address = array(//'postcode' => '',
                'country' => array('id' => Runtime::getSettings()->get('storeCountry'), 'title' => $country_info['countries_name'],
                'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
                'country_id' => Runtime::getSettings()->get('storeCountry'),
                'format_id' => $countryService->getCountryForId($_SESSION['cart_country_id'])->getAddressFormatId());
        }

        return $address;
    }

    /**
     * Prepare .
     *
     * <p>This method will set up everything to be able to run shipping calculations.
     * Needs to be called before any results can be displayed.</p>
     */
    function prepare() {
    global $db, $order;
        $request = Runtime::getContainer()->get('request');
        // Only do when something is in the cart
        if (!$request->getShoppingCart()->isEmpty()) {
          $session = $request->getSession();
          if ($session->isRegistered()) {
              $sendto = $this->_getAddressId();
              $_SESSION['sendto'] = $sendto;
              // set session now
              $_SESSION['cart_address_id'] = $sendto;
              // set shipping to null ! multipickup changes address to store address...
              global $shipping;
              $shipping='';
              $order = new order;
          } else {
              $order->delivery = $this->getZCAddress();
              $order->info = array('total' => $_SESSION['cart']->show_total(), // TAX ????
                  'currency' => $currency,
                  'currency_value'=> $currencies->currencies[$currency]['value']);
          }

          // weight and count needed for shipping !
          global $total_weight, $total_count, $shipping_weight;
          $total_weight = $_SESSION['cart']->show_weight();
          $total_count = $_SESSION['cart']->count_contents();
        }
    }

    /**
     * Returns <code>true</code> if anything to calculate.
     */
    function isCartEmpty() { return Runtime::getContainer()->get('request')->getShoppingCart()->isEmpty(); }

    /**
     * Get current address (if any)
     *
     * @return ZMAddress The curretn address or <code>null</code>.
     */
    function getAddress() {
        return Runtime::getContainer()->get('addressService')->getAddressForId($this->_getAddressId());
    }

}
