<?php

/**
 * Test order service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMOrders extends UnitTestCase {

    /**
     * Test create product.
     */
    public function testUpdateOrderStatus() {
        $order = ZMOrders::instance()->getOrderForId(1);
        $order->setStatus(4);
        ZMOrders::instance()->updateOrder($order);
        $order = ZMOrders::instance()->getOrderForId(1);
        $this->assertEqual('Update', $order->getStatus());
        $order->setStatus(2);
        ZMOrders::instance()->updateOrder($order);
        $order = ZMOrders::instance()->getOrderForId(1);
        $this->assertEqual('Processing', $order->getStatus());
    }

}

?>
