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

namespace zenmagick\plugins\subscriptions\cron;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\events\Event;

use zenmagick\plugins\cron\jobs\CronJobInterface;

/**
 * A cron job to create new subscription orders.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class UpdateSubscriptionsCronJob implements CronJobInterface {

    /**
     * {@inheritDoc}
     */
    public function execute() {
        if (!$plugin || !$plugin->isEnabled()) {
            return true;
        }
        $plugin = $this->getPlugin();
        $scheduledOrders = self::findScheduledOrders();
        $scheduleEmailTemplate = Runtime::getSettings()->get('plugins.subscriptions.email.templates.schedule', 'checkout');
        $orderService = $this->container->get('orderService');
        foreach ($scheduledOrders as $scheduledOrderId) {
            // 1) copy
            $newOrder = self::copyOrder($scheduledOrderId);
            // load the new order as proper ZMOrder instance for further use
            $order = $orderService->getOrderForId($newOrder->getOrderId(), $this->container->get('session')->getLanguageId());
            if (null === $order) {
                Runtime::getLogging()->error('copy order failed for scheduled order: '.$scheduledOrderId);
                continue;
            }

            // 2) update shipping/billing from account to avoid stale addresses
            if ('account' == $plugin->get('addressPolicy')) {
                $account = $this->container->get('accountService')->getAccountForId($order->getAccountId());
                if (null === $account) {
                    Runtime::getLogging()->warn('invalid accountId on order: '.$order->getId());
                    continue;
                }
                $defaultAddressId = $account->getDefaultAddressId();
                $defaultAddress = $this->container->get('addressService')->getAddressForId($defaultAddressId);
                $order->setShippingAddress($defaultAddress);
                $orderService->updateOrder($order);
            }

            // 3) update subscription specific data
            $order->set('subscriptionOrderId', $scheduledOrderId);
            $order->set('subscription', false);
            $order->setStatus($plugin->get('orderStatus'));
            $orderService->updateOrder($order);

            // 4) Create history entry if enabled
            if (Toolbox::asBoolean($plugin->get('orderHistory'))) {
                $status = Beans::getBean('ZMOrderStatus');
                $status->setId($plugin->get('orderStatus'));
                $status->setOrderId($order->getId());
                $status->setOrderStatusId($order->getOrderStatusId());
                $status->setCustomerNotified(!Toolbox::isEmpty($scheduleEmailTemplate));
                $status->setComment(sprintf(_zm('Scheduled order for subscription #%s'), $scheduledOrderId));
                $orderService->createOrderStatusHistory($status);
            }

            // 5) Update subscription order with next schedule date
            // calculate new subscription_next_order based on current subscription_next_order, as we might not run on the same day
            $sql = "UPDATE %table.orders%
                    SET subscription_next_order = DATE_ADD(subscription_next_order, INTERVAL " . zm_subscriptions::schedule2SQL($order->get('schedule')) . ")
                    WHERE orders_id = :orderId";
            $args = array('orderId' => $scheduledOrderId);
            ZMRuntime::getDatabase()->updateObj($sql, $args, 'orders');
            if (!Toolbox::isEmpty($scheduleEmailTemplate)) {
                $this->sendOrderEmail($order, $scheduleEmailTemplate);
            }

            // event
            $this->container->get('event_dispatcher')->dispatch('create_order', new Event($this, array('orderId' => $order->getId())));
        }

        return true;
    }

    /**
     * Email.
     *
     * @param ZMOrder order The order.
     */
    protected function sendOrderEmail($order, $template) {
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $paymentType = $order->getPaymentType();

        $context = array();
        $context['order'] = $order;
        $context['shippingAddress'] = $shippingAddress;
        $context['billingAddress'] = $billingAddress;
        $context['paymentType'] = $paymentType;
        // for comaptibility when using the checkout template
        $context['INTRO_ORDER_NUMBER'] = $order->getId();

        $account = $order->getAccount();

        $message = $this->container->get('messageBuilder')->createMessage($template, true, $request, $context);
        $message->setSubject(sprintf(_zm("%s: Order Subscription Notification"), Runtime::getSettings()->get('storeName')))->setTo($account->getEmail())->setFrom(Runtime::getSettings()->get('storeEmail'));
        $this->container->get('mailer')->send($message);
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return $this->container->get('pluginService')->getPluginForId('subscriptions');
    }

    /**
     * Copy order.
     *
     * @param int orderId The order to copy.
     * @param zenmagick\base\ZMObject The new order.
     */
    public static function copyOrder($orderId) {
        $tables = array(
            'orders', // date_purchased, orders_status
            'orders_products', // orders_id
            'orders_products_attributes', // orders_id, orders_products_id
            'orders_total' // orders_id
        );

        $orderData = array();
        foreach ($tables as $table) {
            $sql = "SELECT * from %table.".$table."% WHERE orders_id = :orderId";
            $orderData[$table] = ZMRuntime::getDatabase()->fetchAll($sql, array('orderId' => $orderId), $table, 'zenmagick\base\ZMObject');
        }

        $orderData['orders'][0]->setOrderDate(new \DateTime());
        $orderData['orders'][0]->setOrderStatusId(2);

        $newOrder = ZMRuntime::getDatabase()->createModel('orders', $orderData['orders'][0]);

        // do products by using zen-cart's order class to include all stock taking, etc
        // some requirements first..
        global $order_total_modules;
        $order_total_modules = new order_total();

        // load existing order
        $zcOrder = new order($orderId);
        // create new order products for the new order id
        $zcOrder->create_add_products($newOrder->getOrderId());

        /*
        // products
        foreach ($orderData['orders_products'] as $orderItem) {
            $orderItem->setOrderId($newOrder->getOrderId());
            $orderProductId = $orderItem->getOrderProductId();

            // find attributes using old order product id
            $attributes = array();
            foreach ($orderData['orders_products_attributes'] as $attribute) {
                if ($attribute->getOrderProductId() == $orderProductId) {
                    $attributes[] = $attribute;
                }
            }

            // create new product
            $orderItem = ZMRuntime::getDatabase()->createModel('orders_products', $orderItem);

            foreach ($attributes as $attribute) {
                $attribute->setOrderId($newOrder->getOrderId());
                $attribute->setOrderProductId($orderItem->getOrderProductId());
                ZMRuntime::getDatabase()->createModel('orders_products_attributes', $attribute);
            }
        }
        */

        foreach ($orderData['orders_total'] as $orderTotal) {
            $orderTotal->setOrderId($newOrder->getOrderId());
            ZMRuntime::getDatabase()->createModel('orders_total', $orderTotal);
        }

        return $newOrder;
    }

    /**
     * Find subscription orders to process.
     *
     * @return array List of order ids.
     */
    public function findScheduledOrders() {
        $plugin = $this->getPlugin();
        $sql = "SELECT orders_id, is_subscription_canceled FROM %table.orders%
                WHERE  is_subscription = :subscription
                  AND subscription_next_order <= DATE_ADD(now(), INTERVAL " . $plugin->get('scheduleOffset') . " DAY)
                  AND NOT (subscription_next_order = '0001-01-01 00:00:00')";
        $results = ZMRuntime::getDatabase()->fetchAll($sql, array('subscription' => true), 'orders');
        $tmp = array();
        foreach ($results as $row) {
            if ($row['subscriptionCanceled'] && $plugin->get('minOrders') <= count($plugin->getScheduledOrderIdsForSubscriptionOrderId($row['orderId']))) {
                continue;
            }
            $tmp[] = $row['orderId'];
        }
        return $tmp;
    }

}
