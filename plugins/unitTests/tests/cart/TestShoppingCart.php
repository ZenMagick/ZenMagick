<?php
/*
 * ZenMagick - Smart e-commerce
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
?>
<?php

/**
 * Test shopping cart.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestShoppingCart extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();

        // some vales zencart's order class wants...
        $_SESSION['sendto'] = '1';
        $_SESSION['billto'] = '1';
        $_SESSION['payment'] = 'moneyorder';
        $_SESSION['shipping'] = 'flat';
        $GLOBALS['moneyorder'] = new ZMObject();
        $GLOBALS['moneyorder']->title = 'moneyorder';
        $GLOBALS['moneyorder']->code = 'moneyorder';

        // cart checks for user...
        $account = $this->container->get('accountService')->getAccountForId(1);
        $this->getRequest()->getSession()->setAccount($account);

        // clear session and database
        $_SESSION['cart']->reset(true);
        $_SESSION['cart']->restore_contents();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown() {
        parent::tearDown();
        // clear session and database
        $_SESSION['cart']->reset(true);
        $_SESSION['cart']->restore_contents();
    }


    /**
     * Compare values for the given productIds using the wrapper and service.
     */
    protected function compareValues_Wrapper_Service($ids) {
        $referenceCart = new ZMShoppingCart();
        $referenceCart->setContainer($this->container);
        $qty = 5;
        foreach ($ids as $id) {
            $referenceCart->addProduct($id, $qty);
            $qty = 5 == $qty ? 3: 5;
        }
        // load again from DB
        $serviceShoppingCart = $this->container->get('shoppingCartService')->loadCartForAccountId($this->getRequest()->getSession()->getAccountId());
        $itemMap = $serviceShoppingCart->getItems();

        foreach ($referenceCart->getItems() as $item) {
            if ($this->assertTrue(array_key_exists($item->getId(), $itemMap), "%s: productId: ".$item->getId())) {
                // compare
                $serviceItem = $itemMap[$item->getId()];
                $this->assertEqual($item->getQuantity(), $serviceItem->getQuantity(), "%s: productId: ".$item->getId());
                // no tax
                $this->assertEqual($item->getItemPrice(false), $serviceItem->getItemPrice(false), "%s: productId: ".$item->getId());
                $this->assertEqual($item->getOneTimeCharge(false), $serviceItem->getOneTimeCharge(false), "%s: productId: ".$item->getId());
            }
        }
    }

    /**
     * Compare values for the given productIds using the service and order class data.
     */
    protected function compareValues_Service_Order($ids) {
        // use to add products
        $referenceCart = new ZMShoppingCart();
        $referenceCart->setContainer($this->container);
        $qty = 5;
        foreach ($ids as $id) {
            $referenceCart->addProduct($id, $qty);
            $qty = 5 == $qty ? 3: 5;
        }

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
     * Test a range of product ids.
     *
     * @param int from The from value.
     * @param int to The to value.
     * @param sting method The compare method.
     */
    protected function compareRange($from, $to, $method) {
        $range = array();
        for (; $from <= $to; ++$from) {
            $range[] = $from;
        }
        $this->$method($range);
    }

    /**
     * Test products comparing wrapper and service data.
     */
    public function testProductsWS1() {
        $this->compareRange(1, 50, 'compareValues_Wrapper_Service');
    }

    /**
     * Test products comparing wrapper and service data.
     */
    public function testProductsWS2() {
        $this->compareRange(51, 100, 'compareValues_Wrapper_Service');
    }

    /**
     * Test products comparing wrapper and service data.
     */
    public function testProductsWS3() {
        $this->compareRange(101, 150, 'compareValues_Wrapper_Service');
    }

    /**
     * Test products comparing wrapper and service data.
     */
    public function testProductsWS4() {
        $this->compareRange(151, 200, 'compareValues_Wrapper_Service');
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO1() {
        $this->compareRange(1, 50, 'compareValues_Service_Order');
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO2() {
        $this->compareRange(51, 100, 'compareValues_Service_Order');
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO3() {
        $this->compareRange(101, 150, 'compareValues_Service_Order');
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO4() {
        $this->compareRange(151, 200, 'compareValues_Service_Order');
    }

    /**
     * Test change quantity.
     */
    public function testChangeQty() {
        $shoppingCart = new ZMShoppingCart();
        $shoppingCart->setContainer($this->container);
        $shoppingCart->addProduct(12, 3);

        $items = $shoppingCart->getItems();
        $this->assertEqual(1, count($items));

        $item = array_pop($items);
        $this->assertEqual(3, $item->getQuantity());

        // add again
        $shoppingCart->addProduct(12, 1);

        $items = $shoppingCart->getItems();
        $this->assertEqual(1, count($items));

        $item = array_pop($items);
        $this->assertEqual(4, $item->getQuantity());
    }

}
