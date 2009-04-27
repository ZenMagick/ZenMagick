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
 * Checkout helper.
 *
 * @author DerManoMann
 * @package org.zenmagick.model.checkout
 * @version $Id$
 */
class ZMCheckoutHelper extends ZMObject {
    const CART_PRODUCT_STATUS = 'status';
    const CART_PRODUCT_QUANTITY = 'quantity';
    const CART_PRODUCT_UNITS = 'units';
    private $cart_;


    /**
     * Create new instance.
     *
     * @param ZMShoppingCart cart The cart.
     */
    function __construct($cart) {
        parent::__construct();
        $this->cart_ = $cart;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if there are only gift vouchers in the cart.
     *
     * @return boolean <code>true</code> if only vouchers are in the cart.
     */
    public function isGVOnly() { 
        foreach ($this->cart_->getItems() as $item) {
            $product = $item->getProduct();
            if (!preg_match('/^GIFT/', $product->getModel())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks for free products in the cart.
     *
     * @return int The number of free products in the cart.
     */
    public function freeProductsCount() {
        $count = 0;
        foreach ($this->cart_->getItems() as $item) {
            $product = $item->getProduct();
            if ($product->isFree()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Checks for virtual products in the cart.
     *
     * @return int The number of virtual products in the cart.
     */
    public function virtualProductsCount() {
        $count = 0;
        foreach ($this->cart_->getItems() as $item) {
            $product = $item->getProduct();
            if ($product->isVirtual()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Checks for free shipping.
     *
     * @return int The number of free shipping products in the cart.
     */
    public function freeShippingCount() {
        $count = 0;
        foreach ($this->cart_->getItems() as $item) {
            $product = $item->getProduct();
            if ($product->isAlwaysFreeShipping()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Check for virtual cart.
     *
     * <p><strong>NOTE:</strong> In contrast to Zen Cart, we treat the <em>always free shipping</em>
     * product attribute as <code>boolean</code>. That means currently there is no support for
     * the special case where virtual products <strong>do</strong> require a shipping address.</p>
     * 
     * @return boolean <code>true</code> if the cart is purely virtual.
     */
    public function isVirtual() {
        if (!ZMSettings::get('isUseCheckoutHelper', true)) {
        global $order;

            if (!isset($order)) {
                ZMTools::resolveZCClass('order');
                $order = new order();
            }

            return $order->content_type == 'virtual';
        }

        return $this->virtualProductsCount() == count($this->cart_->getItems());
    }

    /**
     * Check whether the cart is ready for checkout or not.
     *
     * <p>Possible return values:</p>
     * <ul>
     *  <li>status - one or more products are not availalable (product status)</li>
     *  <li>quantity - one or more products have unsatisfied quantity restrictions</li>
     *  <li>units - one or more products have unsatisfied unit restrictions</li>
     * </ul>
     *
     * @return array A map of errorCode -&gt; item pairs.
     */
    public function checkCartStatus() {
        $map = array();
        foreach ($this->cart_->getItems() as $item) {
            $product = $item->getProduct();

            // check product status
            if (!$product->getStatus()) {
                if (!isset($map[self::CART_PRODUCT_STATUS])) {
                    $map[self::CART_PRODUCT_STATUS] = array();
                }
                $map[self::CART_PRODUCT_STATUS][] = $item;
            }

            // check min qty
            $minQty = $product->getMinOrderQty();
            $qty = $item->getQuantity();
            if ($product->isQtyMixed()) {
                $tqty = 0;
                // make $qty the total over all attribute combinations (SKUs) in the cart
                foreach ($this->cart_->getItems() as $titem) {
                    if ($product->getId() == $titem->getProduct()->getId()) {
                        $tqty += $titem->getQuantity();
                    }
                }
                $qty = $tqty;
            }
            if ($qty < $minQty) {
                if (!isset($map[self::CART_PRODUCT_QUANTITY])) {
                    $map[self::CART_PRODUCT_QUANTITY] = array();
                }
                $map[self::CART_PRODUCT_QUANTITY][] = $item;
            }

            // check quantity units
            $units = $product->getQtyOrderUnits();
            if (ZMTools::fmod_round($qty, $units)) {
                if (!isset($map[self::CART_PRODUCT_UNITS])) {
                    $map[self::CART_PRODUCT_UNITS] = array();
                }
                $map[self::CART_PRODUCT_UNITS][] = $item;
            }
        }

        return $map;
    }

    /**
     * Check whether the cart is ready for checkout or not.
     *
     * <p><strong>NOTE:</strong> The main difference to the Zen Cart implementation of this method is that 
     * no error messages are generated. This is left to the controller to handle.</p>
     *
     * @return boolean <code>true</code> if the cart is ready or checkout, <code>false</code> if not.
     */
    public function readyForCheckout() {
        return 0 == count($this->checkCartStatus());
    }

}

?>
