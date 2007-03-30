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
 * Ajax controller for JSON shopping cart.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.ajax.controller
 * @version $Id$
 */
class ZMAjaxShoppingCartController extends ZMAjaxController {

    /**
     * Default c'tor.
     */
    function ZMAjaxShoppingCartController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAjaxShoppingCartController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Estimate shipping.
     */
    function estimateShippingJSON() {
    global $zm_cart;

        $shippingEstimator = new ZMShippingEstimator();
        $shippingEstimator->prepare();
        $response = array();

        $address = $shippingEstimator->getAddress();
        if (null != $address) {
            $response['address'] = $this->flattenObject($address, array('firstName', 'lastName', 'address', 'suburb', 'postcode', 'city', 'state', 'country'));
        }

        $methods = array();
        if (!$shippingEstimator->isCartEmpty()) {
            $shipping = new ZMShipping();
            if (!$shipping->isFreeShipping()) {
                foreach ($shipping->getShippingProvider() as $provider) {
                    if ($provider->hasError()) 
                        continue;

                    foreach ($provider->getShippingMethods() as $method) {
                        $id = 'ship_'.$method->getId();
                        $ma = array();
                        $ma['id'] = $id;
                        $ma['name'] = $provider->getName() . " " . $method->getName();
                        $ma['cost'] = zm_format_currency($method->getCost(), false);
                        array_push($methods, $ma);
                    }
                }
            }
        }
        $response['methods'] = $methods;

        $flatObj = $this->flattenObject($response);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get cart content.
     */
    function getItemsJSON() {
    global $zm_cart;

        $cart = array();
        $items = array();
        $formatter = create_function('$obj,$name,$value', 'return $name=="itemTotal" ? zm_format_currency($value, false) : $value;');
        foreach ($zm_cart->getItems() as $item) {
            array_push($items, $this->flattenObject($item, array('id', 'name', 'qty', 'itemTotal'), $formatter));
        }
        $cart['items'] = $items;
        $cart['total'] = zm_format_currency($zm_cart->getTotal(), false);

        $flatObj = $this->flattenObject($cart);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}

?>
