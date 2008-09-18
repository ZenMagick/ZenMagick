<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @package org.zenmagick.plugins.zm_subscriptions
 * @version $Id$
 */
class ZMUpdateSubscriptionsCronJob implements ZMCronJob {
    
    /**
     * {@inheritDoc}
     */
    public function execute() {
        $plugin = $this->getPlugin();
        $scheduledOrders = self::findScheduledOrders();
        $scheduleEmailTemplate = $plugin->get('scheduleEmailTemplate');
        foreach ($scheduledOrders as $scheduledOrderId) {
            // 1) copy
            $newOrder = self::copyOrder($scheduledOrderId);
            $order = ZMOrders::instance()->getOrderForId($newOrder->getOrderId());

            // 2) update shipping/billing from account to avoid stale addresses
            if ('account' == $plugin->get('addressPolicy')) {
                $account = ZMAccounts::instance()->getAccountForId($order->getAccountId());
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
            if (ZMTools::asBoolean($plugin->get('orderHistory'))) {
                $status = ZMLoader::make('OrderStatus');
                $status->setId($plugin->get('orderStatus'));
                $status->setOrderId($order->getId());
                $status->setCustomerNotified(!ZMTools::isEmpty($scheduleEmailTemplate));
                $status->setComment(zm_l10n_get('Scheduled order for subscription #%s', $scheduledOrderId));
                ZMOrders::instance()->createOrderStatusHistory($status);
            }

            // 5) Update subscription order with next schedule date
            $sql = "UPDATE " . TABLE_ORDERS . "
                    SET subscription_next_order = DATE_ADD(now(), INTERVAL " . zm_subscriptions::schedule2SQL($order->get('schedule')) . ")
                    WHERE orders_id = :orderId";
            $args = array('orderId' => $scheduledOrderId);
            ZMRuntime::getDatabase()->update($sql, $args, TABLE_ORDERS);
            if (!ZMTools::isEmpty($scheduleEmailTemplate)) {
                $this->sendOrderEmail($order, $scheduleEmailTemplate);
            }
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
        zm_mail(zm_l10n_get("%s: Order Subscription Notification", ZMSettings::get('storeName')), $template, $context, 
            ZMSettings::get('storeEmail'), null, $account->getEmail());
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_subscriptions');
    }

    /**
     * Copy order.
     *
     * @param int orderId The order to copy.
     * @param ZMModel The new order.
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
            $orderData[$table] = ZMRuntime::getDatabase()->query($sql, array('orderId' => $orderId), $table, 'Model');
        }

        $orderData[TABLE_ORDERS][0]->setOrderDate(date(ZM_DB_DATETIME_FORMAT));
        $orderData[TABLE_ORDERS][0]->setOrderStatusId(2);

        $newOrder = ZMRuntime::getDatabase()->createModel(TABLE_ORDERS, $orderData[TABLE_ORDERS][0]);

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
            $orderItem = ZMRuntime::getDatabase()->createModel(TABLE_ORDERS_PRODUCTS, $orderItem);

            foreach ($attributes as $attribute) {
                $attribute->setOrderId($newOrder->getOrderId());
                $attribute->setOrderProductId($orderItem->getOrderProductId());
                ZMRuntime::getDatabase()->createModel(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $attribute);
            }
        }

        foreach ($orderData[TABLE_ORDERS_TOTAL] as $orderTotal) {
            $orderTotal->setOrderId($newOrder->getOrderId());
            ZMRuntime::getDatabase()->createModel(TABLE_ORDERS_TOTAL, $orderTotal);
        }

        return $newOrder;
    }

    /**
     * Find subscription orders to process.
     *
     * @return array List of order ids.
     */
    public static function findScheduledOrders() {
        $sql = "SELECT orders_id FROM " . TABLE_ORDERS . "
                WHERE  is_subscription = :subscription
                  AND subscription_next_order <= now() AND NOT (subscription_next_order = '0001-01-01 00:00:00')";
        $results = ZMRuntime::getDatabase()->query($sql, array('subscription' => true), TABLE_ORDERS);
        $tmp = array();
        foreach ($results as $row) {
            $tmp[] = $row['orderId'];
        }
        return $tmp;
    }

}

?>
