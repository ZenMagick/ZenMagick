<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 *
 * @version $Id$
 */
?>
<?php


/**
 * Copy order.
 *
 * @package org.zenmagick.plugins.zm_subscriptions
 * @param int orderId The order to copy.
 * @param int The new order id.
 */
public function copyOrder($orderId) {
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
        //var_dump($orderData[$table]);
    }

    $orderData[TABLE_ORDERS][0]->setOrderDate(date(ZM_DB_DATETIME_FORMAT));
    $orderData[TABLE_ORDERS][0]->setStatus(2);

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

    return $newOrder->getOrderId();
}

/**
 * Find subscription orders to process.
 *
 * @package org.zenmagick.plugins.zm_subscriptions
 * @return array List of order ids.
 */
public function findScheduledOrders() {
    //$schedule = str_replace(array('d', 'w', 'm', array(' DAY', ' WEEK', ' MONTH'), $schedule);
    $sql = "SELECT * FROM " . TABLE_ORDERS . "
            WHERE (DATE_ADD(last_order, INTERVAL 1 MONTH) >= now() OR last_order = '0001-01-01 00:00:00') AND subscription = 1";
}

?>
