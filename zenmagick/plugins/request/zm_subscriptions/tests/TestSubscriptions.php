<?php

/**
 * Test subscriptions.
 *
 * @package org.zenmagick.plugins.zm_subscriptions
 * @author DerManoMann
 * @version $Id$
 */
class TestSubscriptions extends ZMTestCase {

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_subscriptions');
    }

    /**
     * Test update order.
     */
    public function testUpdateOrder() {
        $order = ZMOrders::instance()->getOrderForId(1);
        $this->assertNotNull($order);
        if (null != $order) {
            $order->set('subscription', true);
            $order->set('nextOrder', date(ZM_DB_DATETIME_FORMAT));
            $order->set('schedule', '1m');
            ZMOrders::instance()->updateOrder($order);
            $updated = ZMOrders::instance()->getOrderForId(1);
            $this->assertNotNull($updated);

            $properties = array('status', 'subscription', 'nextOrder', 'schedule');
            foreach ($properties as $property) {
                $this->assertEqual($order->get($property), $updated->get($property));
            }
        } else {
            $this->fail('no order to update');
        }
    }

    /**
     * Test update subscription order.
     */
    public function testUpdateSubscriptionOrder() {
        $plugin = $this->getPlugin();

        // fake subscription checkout
        ZMRequest::getSession()->setValue('subscription_schedule', '1d');

        $args = array('orderId' => 1);
        $plugin->onZMCreateOrder($args);
    }

    /**
     * Test regular cron run.
     */
    public function testRegularCronRun() {
        if (null == ZMLoader::resolve('ZMCronJob')) {
            $this->skipIf(true, 'Cron not available');
            return;
        }

        $job = ZMLoader::make('UpdateSubscriptionsCronJob');
        $this->assertNotNull($job);
        $status = $job->execute();
        $this->assertTrue($status);
    }

    /**
     * Test getScheduledOrderIdsForSubscriptionOrderId
     */
    public function testGetScheduledOrderIdsForSubscriptionOrderId() {
        $orderIds = $this->getPlugin()->getScheduledOrderIdsForSubscriptionOrderId(1);
        $this->assertTrue(is_array($orderIds));
        $this->assertTrue(0 < count($orderIds));
    }

    /**
     * Test schedule2SQL.
     */
    public function testSchedule2SQL() {
        $simple_tests = array(
            // schedule, expected
            array('1d', '1 DAY'),
            array('1w', '1 WEEK'),
            array('1m', '1 MONTH'),
            array('1y', '1 YEAR'),
        );
        foreach ($simple_tests as $test) {
            $this->assertEqual($test[1], zm_subscriptions::schedule2SQL($test[0]));
        }

        $factor_tests = array(
            // schedule, expected, factor
            array('1d', '2 DAY', 2),
            array('1w', '3 WEEK', 3),
            array('1m', '-1 MONTH', -1),
            array('1y', '1 YEAR', 1),
        );
        foreach ($simple_tests as $test) {
            $this->assertEqual($test[1], zm_subscriptions::schedule2SQL($test[0], $test[2]));
        }
    }

    /**
     * Test getMinLastOrderDate
     */
    public function testGetMinLastOrderDate() {
        $date = $this->getPlugin()->getMinLastOrderDate(1);
        //echo $date;
    }

}

?>
