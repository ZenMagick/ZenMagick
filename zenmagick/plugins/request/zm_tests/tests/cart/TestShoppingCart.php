<?php

/**
 * Test shopping cart.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestShoppingCart extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
        $shoppingCart = ZMRequest::getShoppingCart();
        foreach ($shoppingCart->getItems() as $item) {
            $shoppingCart->removeProduct($item->getId());
        }
    }


    /**
     * Compare values for the given productIds.
     */
    protected function compareValues($ids) {
        $shoppingCart = ZMRequest::getShoppingCart();
        $qty = 1;
        foreach ($ids as $id) {
            $shoppingCart->addProduct($id, $qty);
            $qty = 1 == $qty ? 2: 1;
        }

        $itemMap = $shoppingCart->getItems();
        ZMTools::resolveZCClass('order');
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
    }

    /**
     * Test a range of product ids.
     *
     * @param int from The from value.
     * @param int to The to value.
     */
    protected function compareRange($from, $to) {
        $range = array();
        for (; $from <= $to; ++$from) {
            $range[] = $from;
        }
        $this->compareValues($range);
    }

    /**
     * Test products.
     */
    public function testProducts_1_50() {
        $this->compareRange(1, 50);
    }

    /**
     * Test products.
     */
    public function testProducts_51_100() {
        $this->compareRange(51, 100);
    }

    /**
     * Test products.
     */
    public function testProducts_101_151() {
        $this->compareRange(101, 151);
    }

    /**
     * Test products.
     */
    public function testProducts_151_201() {
        $this->compareRange(151, 201);
    }

}

?>
