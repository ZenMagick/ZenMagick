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
        $this->assertEqual(4, $order->getStatus());
        $this->assertEqual('Update', $order->getStatusName());
        $order->setStatus(2);
        ZMOrders::instance()->updateOrder($order);
        $order = ZMOrders::instance()->getOrderForId(1);
        $this->assertEqual(2, $order->getStatus());
        $this->assertEqual('Processing', $order->getStatusName());
    }

}

?>
