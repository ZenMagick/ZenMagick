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

namespace ZenMagick\plugins\subscriptions\tests;

use ZenMagick\Base\Beans;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test subscriptions.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestSubscriptions extends TestCase
{
    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin()
    {
        return $this->container->get('pluginService')->getPluginForId('subscriptions');
    }

    /**
     * Test update order.
     */
    public function testUpdateOrder()
    {
        $orderService = $this->container->get('orderService');
        $order = $orderService->getOrderForId(1, 1);
        $this->assertNotNull($order);
        if (null != $order) {
            $order->set('subscription', true);
            $order->set('nextOrder', new \DateTime());
            $order->set('schedule', '1m');
            $orderService->updateOrder($order);
            $updated = $orderService->getOrderForId(1, 1);
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
    public function testUpdateSubscriptionOrder()
    {
        $plugin = $this->getPlugin();

        // fake subscription checkout
        $this->getRequest()->getSession()->set('subscription_schedule', '1d');

        $args = array('orderId' => 1);
        $plugin->onZMCreateOrder($args);
    }

    /**
     * Test regular cron run.
     */
    public function testRegularCronRun()
    {
        if (!interface_exists('ZenMagick\plugins\cron\jobs\CronJobInterface')) {
            $this->skipIf(true, 'Cron not available');

            return;
        }

        $job = Beans::getBean('ZenMagick\plugins\subscriptions\cron\UpdateSubscriptionsCronJob');
        $this->assertNotNull($job);
        $status = $job->execute();
        $this->assertTrue($status);
    }

    /**
     * Test getScheduledOrderIdsForSubscriptionOrderId
     */
    public function testScheduledOrdersIds()
    {
        $orderIds = $this->getPlugin()->getScheduledOrderIdsForSubscriptionOrderId(1);
        $this->assertTrue(is_array($orderIds));
        $this->assertTrue(0 < count($orderIds));
    }

    /**
     * Test schedule2SQL.
     */
    public function testSchedule2SQL()
    {
        $simple_tests = array(
            // schedule, expected
            array('1d', '1 DAY'),
            array('1w', '1 WEEK'),
            array('1m', '1 MONTH'),
            array('1y', '1 YEAR'),
        );
        foreach ($simple_tests as $test) {
            $this->assertEqual($test[1], ZMSubscriptionsPlugin::schedule2SQL($test[0]));
        }

        $factor_tests = array(
            // schedule, expected, factor
            array('1d', '2 DAY', 2),
            array('1w', '3 WEEK', 3),
            array('1m', '-1 MONTH', -1),
            array('1y', '1 YEAR', 1),
        );
        foreach ($simple_tests as $test) {
            $this->assertEqual($test[1], ZMSubscriptionsPlugin::schedule2SQL($test[0], $test[2]));
        }
    }

    /**
     * Test getMinLastOrderDate
     */
    public function testGetMinLastOrderDate()
    {
        $date = $this->getPlugin()->getMinLastOrderDate(1);
        //echo $date;
    }

}
