<?php

/**
 * Test subscriptions.
 *
 * @package org.zenmagick.plugins.zm_subscriptions
 * @author DerManoMann
 * @version $Id$
 */
class TestSubscriptions extends UnitTestCase {

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_subscriptions');
    }

    /**
     * Test copy order.
     */
    public function testCopyOrder() {
    }

    /**
     * Test update order.
     */
    public function testUpdateOrder() {
        $order = ZMOrders::instance()->getOrderForId(78);
        $this->assertNotNull($order);
        $order->set('subscription', true);
        $order->set('lastOrder', date(ZM_DB_DATETIME_FORMAT));
        $order->set('schedule', '1m');
        ZMOrders::instance()->updateOrder($order);
        $updated = ZMOrders::instance()->getOrderForId(78);
        $this->assertNotNull($updated);

        $properties = array('status', 'subscription', 'lastOrder', 'schedule');
        foreach ($properties as $property) {
            $this->assertEqual($order->get($property), $updated->get($property));
        }
    }

}

?>
