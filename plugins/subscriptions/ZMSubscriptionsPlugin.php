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

use zenmagick\base\Beans;
use zenmagick\base\Toolbox;
use zenmagick\http\sacs\SacsManager;

/**
 * Subscriptions.
 *
 * @package org.zenmagick.plugins.subscriptions
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMSubscriptionsPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Subscriptions', 'Allow users to subscribe products/orders', '${plugin.version}');

        // the new prices and customer flag
        $customFields = array(
            'orders' => array(
                'is_subscription;boolean;subscription',
                'is_subscription_canceled;boolean;subscriptionCanceled',
                'subscription_next_order;datetime;nextOrder',
                'subscription_schedule;string;schedule',
                'subscription_order_id;integer;subscriptionOrderId'
            )
        );
        foreach ($customFields as $table => $fields) {
            foreach ($fields as $field) {
                ZMSettings::append('zenmagick.core.database.sql.'.$table.'.customFields', $field);
            }
        }
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."/sql/install.sql")), $this->messages_);

        $this->addConfigValue('Qualifying amount', 'minAmount', '0', 'The minimum amount to qualify for a subscription');
        $this->addConfigValue('Minimum orders', 'minOrders', '0', 'The minimum number of orders before the subscription can be canceled');
        $this->addConfigValue('Cancel dealline', 'cancelDeadline', '0', 'Days before the next order the user can cancel the subscription');
        $this->addConfigValue('Admin notification email address', 'adminEmail', ZMSettings::get('storeEmail'),
            'Email address for admin notifications (use store email if empty)');
        $this->addConfigValue('Subscription comment', 'subscriptionComment', true, 'Create subscription comment on original order',
            'widget@ZMBooleanFormWidget#name=subscriptionComment&default=true&label=Add comment');
        $this->addConfigValue('Order history', 'orderHistory', true, 'Create subscription order history on schedule',
            'widget@ZMBooleanFormWidget#name=orderHistory&default=true&label=Create schedule history');
        $this->addConfigValue('Shipping Address', 'addressPolicy', 'order', 'use either the original shipping addres, or the current default address',
            'widget@ZMSelectFormWidget#name=addressPolicy&default=order&options='.urlencode('order=Order Address&account=Account Address'));
        $this->addConfigValue('Order status', 'orderStatus', '2', 'Order status for subscription orders',
            'widget@ZMOrderStatusSelectFormWidget#name=orderStatus&default=2');
        $this->addConfigValue('Schedule offset', 'scheduleOffset', '0',
            'Optional offset (in days) to schedule subscription earlier that actually required');
        $this->addConfigValue('Customer cancel', 'customerCancel', false, 'Allow customers to cancel subscriptions directly',
            'widget@ZMBooleanFormWidget#name=customerCancen&default=false&label=Allow customer cancel');
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."/sql/uninstall.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        zenmagick\base\Runtime::getEventDispatcher()->listen($this);

        // set mappings and permissions of custom pages
        SacsManager::instance()->setMapping('cancel_subscription', ZMAccount::REGISTERED);
        ZMUrlManager::instance()->setMapping('cancel_subscription', array('view' => 'redirect#requestId=account'));

        SacsManager::instance()->setMapping('subscription_request', ZMAccount::REGISTERED);
        ZMUrlManager::instance()->setMapping('subscription_request', array('success' => array('view' => 'redirect#requestId=subscription_request')));

        // set up request form validation
        ZMValidator::instance()->addRules('subscription_request', array(
            array('ZMListRule', 'type', array_keys($this->getRequestTypes())),
            array('ZMRequiredRule', 'message', _zm("Please enter a message")),
        ));

        // add admin page
        $this->addMenuItem2(_zm('Subscriptions'), 'subscriptionAdmin');
        // set up view mapping
        ZMUrlManager::instance()->setMapping('subscriptionAdmin', array('success' => array('view' => 'redirect')));
    }

    /**
     * Event handler to pick up subscription checkout options.
     */
    public function onInitDone($event) {
        $request = $event->get('request');
        if ('checkout_shipping' == $request->getRequestId() && 'POST' == $request->getMethod()) {
            if (Toolbox::asBoolean($request->getParameter('subscription'))) {
                $request->getSession()->setValue('subscription_schedule', $request->getParameter('schedule'));
            } else {
                $request->getSession()->setValue('subscription_schedule');
            }
        }
        if ('checkout_success' == $request->getRequestId()) {
            $request->getSession()->setValue('subscription_schedule');
        }
    }

    /**
     * Check if the given cart can be used as subscription.
     *
     * @param ZMShoppingCart shoppingCart The cart.
     * @return boolean <code>true</code> if the cart qualifies for a subscription.
     */
    public function qualifies($shoppingCart) {
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
        $schedule = $this->container->get('session')->getValue('subscription_schedule');
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
        return ZMSettings::get('plugins.zm_subscriptions.request.types', $defaults);
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
        return ZMSettings::get('plugins.zm_subscriptions.schedules', $defaults);
    }

    /**
     * Order created event handler.
     */
    public function onCreateOrder($event) {
        if ($event->getSubject() instanceof ZMUpdateSubscriptionsCronJob) {
            // do not process orders created by our cron job
            return;
        }

        $request = $event->get('request');
        $orderId = $event->get('orderId');
        if (null != ($schedule = $this->getSelectedSchedule())) {
            $sql = "UPDATE " . TABLE_ORDERS . "
                    SET subscription_next_order = DATE_ADD(date_purchased, INTERVAL " . self::schedule2SQL($schedule) . "),
                      is_subscription = :subscription, is_subscription_canceled = :subscriptionCanceled, subscription_schedule = :schedule
                    WHERE orders_id = :orderId";
            $args = array('orderId' => $orderId, 'subscription' => true, 'subscriptionCanceled' => false, 'schedule' => $schedule);
            ZMRuntime::getDatabase()->update($sql, $args, TABLE_ORDERS);

            if (Toolbox::asBoolean($this->get('subscriptionComment'))) {
                if (null != ($order = $this->container->get('orderService')->getOrderForId($orderId, $request->getSession()->getLanguageId()))) {
                    $status = Beans::getBean('ZMOrderStatus');
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
                FROM " . TABLE_ORDERS . "
                WHERE subscription_order_id = :subscriptionOrderId";
        $results = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array('subscriptionOrderId' => $orderId), TABLE_ORDERS) as $result) {
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
                FROM " . TABLE_ORDERS . "
                WHERE orders_id = :orderId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('orderId' => $orderId), TABLE_ORDERS);

        return $result['nextOrder'];
    }

}
