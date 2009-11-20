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
 * Shipping provider.
 *
 * <p>A shipping provider may offer 1-n shipping methods, depending on the
 * address, etc.</p>
 *
 * <p>This is eventually going to be a replacement for the current <code>ZMShippingProvider</code> class,
 * in combination with the new <code>ZMShippingProviders</code> service.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model.checkout
 * @version $Id$
 */
class ZMShippingProviderWrapper extends ZMObject {
    private $zenModule_;
    private $errors_;


    /**
     * Create a new shipping provider.
     *
     * @param mixed zenModule A zen-cart shipping module instance.
     */
    function __construct($zenModule) {
        parent::__construct();

        $this->zenModule_ = $zenModule;
        $this->errors_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the shipping provider id.
     *
     * @return int The shipping provider id.
     */
    public function getId() { return $this->zenModule_->code; }

    /**
     * Get the shipping provider name.
     *
     * @return string The shipping provider name.
     */
    public function getName() { return $this->zenModule_->title; }

    /**
     * Checks if an icon exists for this provider.
     *
     * @return boolean <code>true</code> if an icon, <code>false</code> if not.
     */
    public function hasIcon() { return !ZMTools::isEmpty($this->zenModule_->icon); }

    /**
     * Get the icon.
     *
     * @return string The icon.
     */
    public function getIcon() { return $this->hasIcon() ? $this->zenModule_->icon : null; }

    /**
     * Flags whether this shipping provider is installed or not.
     *
     * @return boolean <code>true</code> if installed, <code>false</code> if not.
     */
    public function isInstalled() { return $this->zenModule_->check(); }

    /**
     * Checks if errors are logged for this provider.
     *
     * @return boolean <code>true</code> if errors exist, <code>false</code> if not.
     */
    public function hasErrors() { return 0 < count($this->errors_); }

    /**
     * Get the errors.
     *
     * @return array List of error messages.
     */
    public function getErrors() { return $this->errors_; }

    /**
     * Get available shipping methods for the given address.
     *
     * <p><strong>NOTE:</strong> There is currently no way to specify individual items. Basis for calculations
     * is the current shopping cart.</p>
     *
     * @param ZMAddress address The shipping address.
     * @return array A list of <code>ZMShippingMethod</code> instances.
     */
    public function getShippingMethods($address) { 
        $this->errors_ = array();

        // TODO: setup globals, etc with address information, similar to shipping estimator...
        global $order, $shipping_weight, $shipping_num_boxes, $total_count;

        $order = new _zm_order();

        $order->delivery['country']['id'] = $address->getCountryId();
        $order->delivery['country']['iso_code_2'] = $address->getCountry()->getIsoCode2();
        $order->delivery['zone_id'] = $address->getZoneId();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = new shoppingCart();
        }
        $cart = ZMRequest::instance()->getShoppingCart();
        $total_count = $cart->getSize();
        $shipping_weight = $cart->getWeight();

        $shipping_num_boxes = 1;
        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
            $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
            $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }


        // create new instance for each quote!
        // this is required as most modules do stuff in the c'tor (for example zone checks)
        $clazzName = get_class($this->zenModule_);
        $module = new $clazzName();

        if (!$module->enabled) {
            return array();
        }

        $quotes = $module->quote();

        // capture errors
        $this->errors_ = isset($quotes['errors']) ? $quotes['errors'] : array();

        // capture tax
        $taxRate = ZMLoader::make("TaxRate"); 
        $taxRate->setRate(isset($quotes['tax']) ? $quotes['tax'] : 0);

        $methods = array();
        if (array_key_exists('methods', $quotes)) {
            foreach ($quotes['methods'] as $method) {
                $shippingMethod = ZMLoader::make("ShippingMethod", $this, $method);
                $shippingMethod->setTaxRate($taxRate);
                $methods[$shippingMethod->getId()] = $shippingMethod;
            }
        }

        return $methods;
    }

}

    /**
     * Dummie classes used for faking a zen-cart environment where required.
     */

    class _zm_order {
        function __construct() {
            $this->delivery = array();
            $this->delivery['country'] = array();
        }
    }

?>
