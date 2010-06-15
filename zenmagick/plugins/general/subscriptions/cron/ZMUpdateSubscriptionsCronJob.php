<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

/**
 * A cron job to create new subscription orders.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.subscriptions
 * @version $Id: ZMUpdateSubscriptionsCronJob.php 2764 2009-12-18 09:13:44Z dermanomann $
 */
class ZMUpdateSubscriptionsCronJob implements ZMCronJob {
    
    /**
     * {@inheritDoc}
     */
    public function execute() {
        if (!$plugin || !$plugin->isEnabled()) {
            return true;
        }
        $plugin = $this->getPlugin();
        $scheduledOrders = self::findScheduledOrders();
        $scheduleEmailTemplate = ZMSettings::get('plugins.subscriptions.email.templates.schedule', 'checkout');
        foreach ($scheduledOrders as $scheduledOrderId) {
            // 1) copy
            $newOrder = self::copyOrder($scheduledOrderId);
            // load the new order as proper ZMOrder instance for further use
            $order = ZMOrders::instance()->getOrderForId($newOrder->getOrderId(), ZMRequest::instance(->getSession()->getLanguageId());
            if (null === $order) {
                ZMLogging::instance()->log('copy order failed for scheduled order: '.$scheduledOrderId, ZMLogging::ERROR);
                continue;
            }

            // 2) update shipping/billing from account to avoid stale addresses
            if ('account' == $plugin->get('addressPolicy')) {
                $account = ZMAccounts::instance()->getAccountForId($order->getAccountId());
                if (null === $account) {
                    ZMLogging::instance()->log('invalid accountId on order: '.$order->getId(), ZMLogging::ERROR);
                    continue;
                }
                $defaultAddressId = $account->getDefaultAddressId();
                $defaultAddress = ZMAddresses::instance()->getAddressForId($defaultAddressId);
                $order->setShippingAddress($defaultAddress);
                ZMOrders::instance()->updateOrder($order);
            }

            // 3) update subscription specific data
            $order->set('subscriptionOrderId', $scheduledOrderId);
            $order->set('subscription', false);
            $order->setStatus($plugin->get('orderStatus'));
            ZMOrders::instance()->updateOrder($order);

            // 4) Create history entry if enabled
            if (ZMLangUtils::asBoolean($plugin->get('orderHistory'))) {
                $status = ZMLoader::make('OrderStatus');
                $status->setId($plugin->get('orderStatus'));
                $status->setOrderId($order->getId());
                $status->setOrderStatusId($order->getOrderStatusId());
                $status->setCustomerNotified(!ZMLangUtils::isEmpty($scheduleEmailTemplate));
                $status->setComment(sprintf(_zm('Scheduled order for subscription #%s'), $scheduledOrderId));
                ZMOrders::instance()->createOrderStatusHistory($status);
            }

            // 5) Update subscription order with next schedule date
            // calculate new subscription_next_order based on current subscription_next_order, as we might not run on the same day
            $sql = "UPDATE " . TABLE_ORDERS . "
                    SET subscription_next_order = DATE_ADD(subscription_next_order, INTERVAL " . zm_subscriptions::schedule2SQL($order->get('schedule')) . ")
                    WHERE orders_id = :orderId";
            $args = array('orderId' => $scheduledOrderId);
            Runtime::getDatabase()->update($sql, $args, TABLE_ORDERS);
            if (!ZMLangUtils::isEmpty($scheduleEmailTemplate)) {
                $this->sendOrderEmail($order, $scheduleEmailTemplate);
            }

            // event
            ZMEvents::instance()->fireEvent($this, Events::CREATE_ORDER, array('orderId' => $order->getId()));
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
        zm_mail(sprintf(_zm("%s: Order Subscription Notification"), ZMSettings::get('storeName')), $template, $context, 
            $account->getEmail(), ZMSettings::get('storeEmail'), null);
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('subscriptions');
    }

    /**
     * Copy order.
     *
     * @param int orderId The order to copy.
     * @param ZMObject The new order.
     */
    public static function copyOrder($orderId) {
        $tables = array(
            TABLE_ORDERS, // date_purchased, orders_status
            TABLE_ORDERS_PRODUCTS, // orders_id
            TABLE_ORDERS_PRODUCTS_ATTRIBUTES, // orders_id, orders_products_id
            TABLE_ORDERS_TOTAL // orders_id
        );

        $orderData = array();
        foreach ($tables as $table) {
            $sql = "SELECT * from ".$table." WHERE orders_id = :orderId";
            $orderData[$table] = Runtime::getDatabase()->query($sql, array('orderId' => $orderId), $table, 'ZMObject');
        }

        $orderData[TABLE_ORDERS][0]->setOrderDate(date(ZMDatabase::DATETIME_FORMAT));
        $orderData[TABLE_ORDERS][0]->setOrderStatusId(2);

        $newOrder = Runtime::getDatabase()->createModel(TABLE_ORDERS, $orderData[TABLE_ORDERS][0]);

        // do products by using zen-cart's order class to include all stock taking, etc
        // some requirements first..
        ZMTools::resolveZCClass('order');
        ZMTools::resolveZCClass('order_total');
        global $order_total_modules;
        $order_total_modules = new order_total();

        // load existing order
        $zcOrder = new order($orderId);
        // create new order products for the new order id
        $zcOrder->create_add_products($newOrder->getOrderId());

        /*
        // products
        foreach ($orderData[TABLE_ORDERS_PRODUCTS] as $orderItem) {
            $orderItem->setOrderId($newOrder->getOrderId());
            $orderProductId = $orderItem->getOrderProductId();

            // find attributes using old order product id
            $attributes = array();
            foreach ($orderData[TABLE_ORDERS_PRODUCTS_ATTRIBUTES] as $attribute) {
                if ($attribute->getOrderProductId() == $orderProductId) {
                    $attributes[] = $attribute;
                }
            }

            // create new product
            $orderItem = Runtime::getDatabase()->createModel(TABLE_ORDERS_PRODUCTS, $orderItem);

            foreach ($attributes as $attribute) {
                $attribute->setOrderId($newOrder->getOrderId());
                $attribute->setOrderProductId($orderItem->getOrderProductId());
                Runtime::getDatabase()->createModel(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $attribute);
            }
        }
        */

        foreach ($orderData[TABLE_ORDERS_TOTAL] as $orderTotal) {
            $orderTotal->setOrderId($newOrder->getOrderId());
            Runtime::getDatabase()->createModel(TABLE_ORDERS_TOTAL, $orderTotal);
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
        $sql = "SELECT orders_id, is_subscription_canceled FROM " . TABLE_ORDERS . "
                WHERE  is_subscription = :subscription
                  AND subscription_next_order <= DATE_ADD(now(), INTERVAL " . $plugin->get('scheduleOffset') . " DAY) 
                  AND NOT (subscription_next_order = '0001-01-01 00:00:00')";
        $results = Runtime::getDatabase()->query($sql, array('subscription' => true), TABLE_ORDERS);
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
