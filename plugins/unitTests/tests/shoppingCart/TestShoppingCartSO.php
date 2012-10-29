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


/**
 * Test shopping cart via service order.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestShoppingCartSO extends ShoppingCartTestCaseBase {

    /**
     * Compare values for the given productIds using the service and order class data.
     */
    protected function compareValues_Service_Order($ids) {
        $referenceCart = $this->getReferenceCart($ids);

        // load again from DB
        $serviceShoppingCart = $this->container->get('shoppingCartService')->loadCartForAccountId($this->getRequest()->getSession()->getAccountId());
        $itemMap = $serviceShoppingCart->getItems();

        // get product data from order
        $er = error_reporting(0);
        $order = new order();
        foreach ($order->products as $product) {
            if ($this->assertTrue(array_key_exists($product['id'], $itemMap), "%s: productId: ".$product['id'])) {
                // compare
                $item = $itemMap[$product['id']];
                $this->assertEqual($product['qty'], $item->getQuantity(), "%s: productId: ".$product['id']);
                // no tax
                $this->assertEqual($product['final_price'], $item->getItemPrice(false), "%s: productId: ".$product['id']);
            }
        }
        error_reporting($er);
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO_001_050() {
        $this->compareRange(1, 50, 'compareValues_Service_Order');
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO_051_100() {
        $this->compareRange(51, 100, 'compareValues_Service_Order');
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO_101_150() {
        $this->compareRange(101, 150, 'compareValues_Service_Order');
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO_151_200() {
        $this->compareRange(151, 200, 'compareValues_Service_Order');
    }

}
