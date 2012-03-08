<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\bundles\ZenCartBundle\Mock;

use zenmagick\base\Runtime;
use zenmagick\apps\store\bundles\ZenCartBundle\mock\ZenCartCheckoutOrder;
use zenmagick\apps\store\bundles\ZenCartBundle\mock\ZenCartOrderTotal;

/**
 * ZenCart mock tools.
 *
 * @author DerManoMann
 */
class ZenCartMock {
    // keep track of mock
    private static $mock = 0;


    /**
     * Start mocking around.
     *
     * @param ZMShoppingCart shoppingCart The current shopping cart.
     * @param ZMAddress shippingAddress Optional shipping address; default is <code>null</code>.
     */
    public static function startMock($shoppingCart, $shippingAddress=null) {
    global $order, $shipping_weight, $shipping_quoted, $shipping_num_boxes, $total_count, $order_total_modules;
    global $_order, $_shipping_weight, $_shipping_quoted, $_shipping_num_boxes, $_total_count, $_order_total_modules;

        if (self::$mock++) {
            // already mocking
            return;
        }

        // save originals
        $_order = $order;
        $_shipping_weight = $shipping_weight;
        $_shipping_quoted = $shipping_quoted;
        $_shipping_num_boxes = $shipping_num_boxes;
        $_total_count = $total_count;
        $_order_total_modules = $order_total_modules;

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = new shoppingCart();
        }

        // get total number of products, not line items...
        $total_count = 0;
        foreach ($shoppingCart->getItems() as $item) {
            $total_count += $item->getQuantity();
        }

        //$order_total_modules = new ZenCartOrderTotal();

        if (null == $order || !($order instanceof ZenCartCheckoutOrder)) {
            $mockOrder = new ZenCartCheckoutOrder();
            $mockOrder->setContainer(Runtime::getContainer());
            $mockOrder->setShoppingCart($shoppingCart);
            if (null != $shippingAddress) {
                $mockOrder->setShippingAddress($shippingAddress);
            }
            $order = $mockOrder;
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
    }

    /**
     * Cleanup mocking.
     *
     */
    public static function cleanupMock() {
    global $order, $shipping_weight, $shipping_quoted, $shipping_num_boxes, $total_count, $order_total_modules;
    global $_order, $_shipping_weight, $_shipping_quoted, $_shipping_num_boxes, $_total_count, $_order_total_modules;

        if (--self::$mock) {
            // still mocking somewhere
            return;
        }

        // restore originals
        $order = $_order;
        $shipping_weight = $_shipping_weight;
        $shipping_quoted = $_shipping_quoted;
        $shipping_num_boxes = $_shipping_num_boxes;
        $total_count = $_total_count;
        $order_total_modules = $_order_total_modules;
    }

}
