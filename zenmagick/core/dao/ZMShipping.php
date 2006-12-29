<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Access class for shipping options.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMShipping extends ZMDao {

    /**
     * Default c'tor.
     */
    function ZMShipping() {
    global $shipping_modules;

        parent::__construct();

        $this->provider_ = array();
        if (!class_exists('shipping')) {
            zm_resolve_zc_class('shipping');
            $zenShipping =& new shipping();
        } else {
            $zenShipping =& $shipping_modules;
        }
        $quotes = $zenShipping->quote();
        foreach ($quotes as $quote) {
            array_push($this->provider_, $this->create("ShippingProvider", $quote));
        }

    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMShipping();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    function isFreeShipping() {
    global $order;
        if (zm_setting('isOrderTotalFreeShipping')) {
            $pass = false;
            switch (zm_setting('freeShippingDestination')) {
              case 'national':
                  if ($order->delivery['country_id'] == zm_setting('storeCountry')) {
                      $pass = true;
                  }
                  break;
              case 'international':
                  if ($order->delivery['country_id'] != zm_setting('storeCountry')) {
                      $pass = true;
                  }
                  break;
              case 'both':
                  $pass = true;
                  break;
            }

            if (($pass == true) && ($_SESSION['cart']->show_total() >= zm_setting('freeShippingOrderThreshold'))) {
                return true;
            }
        }

        return false;
    }


    // shipping available
    function hasShippingProvider() { return 0 < count($this->provider_); }

    // number of shipping providers
    function getShippingProvider() { return $this->provider_; }

    function getShippingMethodCount() {
        $count = 0;
        foreach ($this->provider_ as $provider) {
            $count += count($provider->getShippingMethods());
        }

        return $count;
    }


    // find a particular shipping method
    function getShippingMethodForId($id) {
    }

}

?>
