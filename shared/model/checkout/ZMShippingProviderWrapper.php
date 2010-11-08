<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Shipping provider wrapper for zen cart shipping modules.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
class ZMShippingProviderWrapper extends ZMObject implements ZMShippingProvider {
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
     * {@inheritDoc}
     */
    public function getId() { return $this->zenModule_->code; }

    /**
     * {@inheritDoc}
     */
    public function getName() { return $this->zenModule_->title; }

    /**
     * {@inheritDoc}
     */
    public function hasIcon() { return !ZMLangUtils::isEmpty($this->zenModule_->icon); }

    /**
     * {@inheritDoc}
     */
    public function getIcon() { return $this->hasIcon() ? $this->zenModule_->icon : null; }

    /**
     * {@inheritDoc}
     */
    public function isInstalled() { return $this->zenModule_->check(); }

    /**
     * {@inheritDoc}
     */
    public function hasErrors() { return 0 < count($this->errors_); }

    /**
     * {@inheritDoc}
     */
    public function getErrors() { return $this->errors_; }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethodForId($id, $shoppingCart, $address=null) { 
        $methods = $this->getShippingMethods($shoppingCart, $address);
        return (array_key_exists($id, $methods) ? $methods[$id] : null);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethods($shoppingCart, $address=null) { 
        if (null == $address) {
            // now we just want the shipping method, but we need an address right now...
            $address = $shoppingCart->getShippingAddress();
        }

        $this->errors_ = array();

        // TODO: setup globals, etc with address information, similar to shipping estimator...
        global $order, $shipping_weight, $shipping_quoted, $shipping_num_boxes, $total_count;

        $order = new stdClass();
        $order->delivery = array();
        $order->delivery['country'] = array();

        $order->delivery['country']['id'] = $address->getCountryId();
        $order->delivery['country']['iso_code_2'] = $address->getCountry()->getIsoCode2();
        $order->delivery['zone_id'] = $address->getZoneId();

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = new shoppingCart();
        }

        // get total number of products, not line items...
        $total_count = 0;
        foreach ($shoppingCart->getItems() as $item) {
            $total_count += $item->getQuantity();
        }

        // START: adjust boxes, weight and tare
        $shipping_quoted = '';
        $shipping_num_boxes = 1;
        $shipping_weight = $shoppingCart->getWeight();

        $za_tare_array = preg_split("/[:,]/" , SHIPPING_BOX_WEIGHT);
        $zc_tare_percent= $za_tare_array[0];
        $zc_tare_weight= $za_tare_array[1];

        $za_large_array = preg_split("/[:,]/" , SHIPPING_BOX_PADDING);
        $zc_large_percent= $za_large_array[0];
        $zc_large_weight= $za_large_array[1];

        switch (true) {
          // large box add padding
          case(SHIPPING_MAX_WEIGHT <= $shipping_weight):
            $shipping_weight = $shipping_weight + ($shipping_weight*($zc_large_percent/100)) + $zc_large_weight;
            break;
          default:
          // add tare weight < large
            $shipping_weight = $shipping_weight + ($shipping_weight*($zc_tare_percent/100)) + $zc_tare_weight;
            break;
        }

        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
          $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }
        // END: adjust boxes, weight and tare


        // create new instance for each quote!
        // this is required as most modules do stuff in the c'tor (for example zone checks)
        $clazzName = get_class($this->zenModule_);
        $module = new $clazzName();

        if (!$module->enabled) {
            return array();
        }

        $quotes = $module->quote();

        // capture error(s)
        if (is_array($quotes) && array_key_exists('error', $quotes)) {
            $this->errors_ = array($quotes['error']);
            return array();
        }

        // capture tax
        $taxRate = ZMBeanUtils::getBean("TaxRate"); 
        $taxRate->setRate(isset($quotes['tax']) ? $quotes['tax'] : 0);

        $methods = array();
        if (is_array($quotes) && array_key_exists('methods', $quotes)) {
            foreach ($quotes['methods'] as $method) {
                $shippingMethod = ZMLoader::make("ShippingMethod", $this, $method);
                $shippingMethod->setProvider($this);
                $shippingMethod->setTaxRate($taxRate);
                $methods[$shippingMethod->getId()] = $shippingMethod;
            }
        }

        return $methods;
    }

}
