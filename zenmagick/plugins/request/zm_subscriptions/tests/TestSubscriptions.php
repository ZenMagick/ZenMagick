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
     * Test update order.
     */
    public function _testUpdateOrder() {
        $order = ZMOrders::instance()->getOrderForId(78);
        $this->assertNotNull($order);
        $order->set('subscription', true);
        $order->set('nextOrder', date(ZM_DB_DATETIME_FORMAT));
        $order->set('schedule', '1m');
        ZMOrders::instance()->updateOrder($order);
        $updated = ZMOrders::instance()->getOrderForId(78);
        $this->assertNotNull($updated);

        $properties = array('status', 'subscription', 'nextOrder', 'schedule');
        foreach ($properties as $property) {
            $this->assertEqual($order->get($property), $updated->get($property));
        }
    }

    /**
     * Test update subscription order.
     */
    public function _testUpdateSubscriptionOrder() {
        $plugin = $this->getPlugin();

        // fake subscription checkout
        ZMRequest::getSession()->setValue('subscription_schedule', '1d');

        $args = array('orderId' => 157);
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

}

?>
