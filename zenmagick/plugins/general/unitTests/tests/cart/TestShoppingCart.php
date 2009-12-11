<?php

/**
 * Test shopping cart.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestShoppingCart extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
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
        $qty = 5;
        foreach ($ids as $id) {
            $referenceCart->addProduct($id, $qty);
            $qty = 5 == $qty ? 3: 5;
        }
        // load again from DB
        $serviceShoppingCart = ZMShoppingCarts::instance()->loadCartForAccountId(17);
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
        $qty = 5;
        foreach ($ids as $id) {
            $referenceCart->addProduct($id, $qty);
            $qty = 5 == $qty ? 3: 5;
        }

        // load again from DB
        $serviceShoppingCart = ZMShoppingCarts::instance()->loadCartForAccountId(17);
        $itemMap = $serviceShoppingCart->getItems();

        // get product data from order
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
    public function testProductsWS() {
        $this->compareRange(1, 200, 'compareValues_Wrapper_Service');
    }

    /**
     * Test products comparing service and order data.
     */
    public function testProductsSO() {
        $this->compareRange(1, 200, 'compareValues_Service_Order');
    }

}

?>
