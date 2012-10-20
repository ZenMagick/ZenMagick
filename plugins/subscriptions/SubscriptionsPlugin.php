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
namespace ZenMagick\plugins\subscriptions;

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Toolbox;
use ZenMagick\Http\Sacs\SacsManager;
use ZenMagick\StoreBundle\Model\Checkout\ShoppingCart;
use ZenMagick\StoreBundle\Entity\Account\Account;
use ZenMagick\plugins\subscriptions\cron\UpdateSubscriptionsCronJob;

/**
 * Subscriptions.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SubscriptionsPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct(array $config) {
        parent::__construct($config);

        // the new prices and customer flag
        $customFields = array(
            'orders' => array(
                'subscription' => array('column' => 'is_subscription', 'type' => 'boolean'),
                'subscriptionCanceled' => array('column' => 'is_subscription_canceled', 'type' => 'boolean'),
                'nextOrder' => array('column' => 'subscription_next_order', 'type' => 'datetime'),
                'schedule' => array('column' => 'subscription_schedule', 'type' => 'string'),
                'subscriptionOrderId' => array('column' => 'subscription_order_id', 'type' => 'integer'),
            )
        );
        foreach ($customFields as $table => $fields) {
            foreach ($fields as $field => $info) {
                \ZMRuntime::getDatabase()->getMapper()->addPropertyForTable($table, $field, $info);
            }
        }
    }


    /**
     * Event handler to pick up subscription checkout options.
     */
    public function onContainerReady($event) {
        $sacsManager = $this->container->get('sacsManager');

        // set mappings and permissions of custom pages
        $sacsManager->setMapping('cancel_subscription', Account::REGISTERED);
        $sacsManager->setMapping('subscription_request', Account::REGISTERED);

        // set up request form validation
        $this->container->get('zmvalidator')->addRules('subscription_request', array(
            array('ZMListRule', 'type', array_keys($this->getRequestTypes())),
            array('ZMRequiredRule', 'message', _zm("Please enter a message")),
        ));

        $request = $event->getArgument('request');
        if ('checkout_shipping' == $request->getRequestId() && 'POST' == $request->getMethod()) {
            if (Toolbox::asBoolean($request->request->get('subscription'))) {
                $request->getSession()->set('subscription_schedule', $request->request->get('schedule'));
            } else {
                $request->getSession()->set('subscription_schedule');
            }
        }
        if ('checkout_success' == $request->getRequestId()) {
            $request->getSession()->set('subscription_schedule');
        }
    }

    /**
     * Check if the given cart can be used as subscription.
     *
     * @param ShoppingCart shoppingCart The cart.
     * @return boolean <code>true</code> if the cart qualifies for a subscription.
     */
    public function qualifies(ShoppingCart $shoppingCart) {
        return $this->get('minAmount') <= $shoppingCart->getTotal();
    }

    /**
     * Check if customer can cancel subscriptions directly.
     *
     * @return boolean <code>true</code> if direct cancel is allowed.
     */
    public function isCustomerCancel() {
        return $this->get('customerCancel');
    }

    /**
     * Check if currently subscription is selected.
     *
     * @return string The subscription schedule key or <code>null</code>.
     */
    public function getSelectedSchedule() {
        $schedule = $this->container->get('session')->get('subscription_schedule');
        return empty($schedule) ? null : $schedule;
    }

    /**
     * Get all available request types as map.
     *
     * <p>This can be configured/changed via the setting <em>plugins.zm_subscriptions.request.types</em>.</p>
     *
     * @return array Hash map of schedule key => name.
     */
    public function getRequestTypes() {
        $defaults = array(
                'cancel' => _zm("Cancel Subscription"),
                'enquire' => _zm("Enquire order status"),
                'other' => _zm("Other"),
        );
        return $this->container->get('settingsService')->get('plugins.zm_subscriptions.request.types', $defaults);
    }

    /**
     * Get all available schedules as map.
     *
     * <p>This can be configured/changed via the setting <em>plugins.zm_subscriptions.schedules</em>.</p>
     *
     * @return array Hash map of schedule key => name.
     */
    public function getSchedules() {
        $defaults = array(
            '1w' => array('name' => 'Weekly', 'active' => true),
            '10d' => array('name' => 'Every 10 days', 'active' => true),
            '4w' => array('name' => 'Every four weeks', 'active' => true),
            '1m' => array('name' => 'Once a month', 'active' => true)
        );
        return $this->container->get('settingsService')->get('plugins.zm_subscriptions.schedules', $defaults);
    }

    /**
     * Order created event handler.
     */
    public function onCreateOrder($event) {
        if ($event->getSubject() instanceof UpdateSubscriptionsCronJob) {
            // do not process orders created by our cron job
            return;
        }

        $request = $event->getArgument('request');
        $orderId = $event->getArgument('orderId');
        if (null != ($schedule = $this->getSelectedSchedule())) {
            $sql = "UPDATE %table.orders%
                    SET subscription_next_order = DATE_ADD(date_purchased, INTERVAL " . self::schedule2SQL($schedule) . "),
                      is_subscription = :subscription, is_subscription_canceled = :subscriptionCanceled, subscription_schedule = :schedule
                    WHERE orders_id = :orderId";
            $args = array('orderId' => $orderId, 'subscription' => true, 'subscriptionCanceled' => false, 'schedule' => $schedule);
            \ZMRuntime::getDatabase()->updateObj($sql, $args, 'orders');

            if (Toolbox::asBoolean($this->get('subscriptionComment'))) {
                if (null != ($order = $this->container->get('orderService')->getOrderForId($orderId, $request->getSession()->getLanguageId()))) {
                    $status = Beans::getBean('ZenMagick\StoreBundle\Entity\Order\OrderStatusHistory');
                    $status->setOrderStatusId($order->getOrderStatusId());
                    $status->setOrderId($order->getId());
                    $status->setCustomerNotified(false);
                    $schedules = $this->getSchedules();
                    $status->setComment(sprintf(_zm('Subscription: %s'), $schedules[$schedule]['name']));
                    $this->container->get('orderService')->createOrderStatusHistory($status);
                }
            }
        }
    }

    /**
     * Convert UI schedule value into something useful for SQL.
     *
     * @param string schedule The schedule value.
     * @param int factor Optional factor to get multiple of the single interval; default is <em>1</em>.
     * @return string A string that can be used in SQL <em>DATE_ADD</em>.
     */
    public static function schedule2SQL($schedule, $factor=1) {
        $schedule = preg_replace('/[^0-9dwmy]/', '', $schedule);
        $schedule = str_replace(array('d', 'w', 'm', 'y'), array(' DAY', ' WEEK', ' MONTH', ' YEAR'), $schedule);
        if (1 < $factor) {
            // multiply
            $bits = explode(' ', $schedule);
            $schedule = ($factor * (int)$bits[0]) . ' ' . $bits[1];

        }
        return $schedule;
    }

    /**
     * Get the scheduled orderIds for a given subscription order id.
     *
     * @param int orderId The original subscription order.
     * @return array List of order ids.
     */
    public function getScheduledOrderIdsForSubscriptionOrderId($orderId) {
        $sql = "SELECT orders_id
                FROM %table.orders%
                WHERE subscription_order_id = :subscriptionOrderId";
        $results = array();
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array('subscriptionOrderId' => $orderId), 'orders') as $result) {
            $results[] = $result['orderId'];
        }

        return $results;
    }

    /**
     * Get the minimum last order date for a subscription (the earliest cancel date).
     *
     * @param int orderId The original subscription order id.
     * @return string The date or <code>null</code> (if not canceled).
     */
    public function getMinLastOrderDate($orderId) {
        $order = $this->container->get('orderService')->getOrderForId($orderId, $this->container->get('session')->getLanguageId());

        // let's find out how many more orders need to be shipped to pass the minOrders restriction
        $scheduledOrderIds = $this->getScheduledOrderIdsForSubscriptionOrderId($orderId);
        $missing = $this->get('minOrders') - count($scheduledOrderIds);
        if (0 >= $missing) {
            // in the case of old orders this should be the date of the last actually shipped order
            return $order->getNextOrder();
        }
        // multiply the schedule by missing
        $distance = self::schedule2SQL($order->getSchedule(), $missing);

        // use SQL to calculate the last date
        $sql = "SELECT DATE_ADD(subscription_next_order, INTERVAL " . $distance . ") as subscription_next_order
                FROM %table.orders%
                WHERE orders_id = :orderId";
        $result = \ZMRuntime::getDatabase()->querySingle($sql, array('orderId' => $orderId), 'orders');

        return $result['nextOrder'];
    }

}
