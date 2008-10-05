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
 */
?>
<?php


/**
 * Orders.
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMOrders extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Orders');
    }


    /**
     * Get order for the given id.
     *
     * @param int id The order id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMOrder A order or <code>null</code>.
     */
    public function getOrderForId($orderId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }
        
        $sql = "SELECT o.*, s.orders_status_name
                FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . "  ot, " . TABLE_ORDERS_STATUS . " s
                WHERE o.orders_id = :orderId
                  AND o.orders_id = ot.orders_id
                  AND ot.class = 'ot_total'
                  AND o.orders_status = s.orders_status_id
                  AND s.language_id = :languageId";
        $args = array('orderId' => $orderId, 'languageId' => $languageId);
        $order = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_ORDERS, TABLE_ORDERS_TOTAL, TABLE_ORDERS_STATUS), 'Order');

        return $order;
    }

    /**
     * Get all orders for the given account id.
     *
     * @param int accountId The account id.
     * @param int limit Optional result limit.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array List of <code>ZMOrder</code> instances.
     */
    public function getOrdersForAccountId($accountId, $limit=0, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }
        
        // order only
        $sqlLimit = 0 != $limit ? " LIMIT ".$limit : "";
        $sql = "SELECT o.*, s.orders_status_name
                FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . "  ot, " . TABLE_ORDERS_STATUS . " s
                WHERE o.customers_id = :accountId
                  AND o.orders_id = ot.orders_id
                  AND ot.class = 'ot_total'
                  AND o.orders_status = s.orders_status_id
                  AND s.language_id = :languageId
                  ORDER BY orders_id desc".$sqlLimit;
        $args = array('accountId' => $accountId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_ORDERS, TABLE_ORDERS_TOTAL, TABLE_ORDERS_STATUS), 'Order');
    }

    /**
     * Get all orders for a given order status.
     *
     * @param int statusId The order status.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array List of <code>ZMOrder</code> instances.
     */
    public function getOrdersForStatusId($statusId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }
        
        // order only
        $sql = "SELECT o.*, s.orders_status_name
                FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . "  ot, " . TABLE_ORDERS_STATUS . " s
                WHERE o.orders_status = :orderStatusId
                  AND o.orders_id = ot.orders_id
                  AND ot.class = 'ot_total'
                  AND o.orders_status = s.orders_status_id
                  AND s.language_id = :languageId
                  ORDER BY orders_id desc";
        $args = array('orderStatusId' => $statusId, 'languageId' => $languageId);
        $orders = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_ORDERS, TABLE_ORDERS_TOTAL, TABLE_ORDERS_STATUS), 'Order');

        return $orders;
    }

    /**
     * Get order status history for order id.
     *
     * @param int orderId The order id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array List of <code>ZMOrderStatus</code> instances.
     */
    public function getOrderStatusHistoryForId($orderId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT os.orders_status_name, osh.*
                FROM " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh
                WHERE osh.orders_id = :orderId
                  AND osh.orders_status_id = os.orders_status_id
                  AND os.language_id = :languageId
                  ORDER BY osh.date_added";
        $args = array('orderId' => $orderId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_ORDERS_STATUS_HISTORY, TABLE_ORDERS_STATUS), 'OrderStatus');
    }

    /**
     * Create new order status history entry.
     *
     * @param ZMOrderStatus orderStatus The new order status.
     * @return ZMOrderStatus The created order status (incl id).
     */
    public function createOrderStatusHistory($orderStatus) {
        if (null == $orderStatus->getDateAdded()) {
            $orderStatus->setDateAdded(date(ZM_DB_DATETIME_FORMAT));
        }
        return ZMRuntime::getDatabase()->createModel(TABLE_ORDERS_STATUS_HISTORY, $orderStatus);
    }

    /**
     * Get order items
     */
    function _getOrderItems($order) {
        $orderItems = array();

        ZMLoader::resolveZCClass('order');
        $zenOrder = new order($order->getId());
        // keep ref for further use
        $order->zenOrder_ = $zenOrder;
        foreach ($zenOrder->products as $zenItem) {
            $orderItem = $this->_newOrderItem($zenItem);
            array_push($orderItems, $orderItem);
        }

        return $orderItems;
    }

    /**
     * Create new order item instance.
     */
    function _newOrderItem($zenItem) {
        // keep reference of used variables
        $attributesLookup = array();

        if (isset($zenItem['attributes']) && 0 < sizeof($zenItem['attributes'])) {
            foreach ($zenItem['attributes'] as $zenAttribute) {
                $name = $zenAttribute['option'];
                if (array_key_exists($name, $attributesLookup)) {
                    $atname = $attributesLookup[$name];
                } else {
                    $atname = str_replace(' ', '', $name);
                    $$atname = ZMLoader::make("Attribute", 0, $name, null);
                    $attributesLookup[$name] = $atname;
                }
                $attributeValue = ZMLoader::make("AttributeValue", 0, $zenAttribute['value']);
                $attributeValue->setPricePrefix($zenAttribute['prefix']);
                $attributeValue->setPrice($zenAttribute['price']);
                $$atname->addValue($attributeValue);
            }
        }

        $orderItem = ZMLoader::make("OrderItem");
        $orderItem->productId_ = $zenItem['id'];
        $orderItem->qty_ = $zenItem['qty'];
        $orderItem->name_ = $zenItem['name'];
        $orderItem->model_ = $zenItem['model'];
        $taxRate = ZMLoader::make("TaxRate");
        $taxRate->setRate($zenItem['tax']);
        $orderItem->taxRate_ = $taxRate;
        $taxRate = ZMLoader::make("TaxRate");
        $taxRate->setRate($zenItem['tax']);
        $orderItem->calculatedPrice_ = $taxRate->addTax($zenItem['final_price']);
        foreach ($attributesLookup as $name => $atname) {
            array_push($orderItem->attributes_, $$atname);
        }

        return $orderItem;
    }

    /**
     * Get order totals.
     *
     * @param int orderId The order id.
     * @return array Map of <code>ZMOrderItem</code> instances with the type as key.
     */
    public function getOrderTotals($orderId) {
        $sql = "SELECT * FROM " . TABLE_ORDERS_TOTAL . "
                WHERE orders_id = :orderId
                ORDER BY sort_order";
        $totals = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array('orderId' => $orderId), TABLE_ORDERS_TOTAL, 'OrderTotal') as $total) {
            $totals[$total->getType()] = $total;
        }

        return $totals;
    }

    /**
     * Update an existing order.
     *
     * <p><strong>NOTE: Currently this will update the orders table only!</strong></p>
     *
     * @param ZMOrder The order.
     * @return ZMOrder The updated order.
     */
    public function updateOrder($order) {
        return ZMRuntime::getDatabase()->updateModel(TABLE_ORDERS, $order);
    }

    /**
     * Get downloads for order.
     *
     * @param int orderId The order id.
     * @param array orderStatusList Optional array of order stati to check; default is null to use the configured range, (empty array will load all).
     * @return array A list of <code>ZMDownload</code> instances.
     */
    public function getDownloadsForOrderId($orderId, $orderStatusList=null) {
        if (null === $orderStatusList) {
            $orderStatusList = ZMSettings::get('downloadOrderStatusList');
        }
        $sql = "SELECT o.date_purchased, o.orders_status, opd.*
                FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                WHERE o.orders_id = :orderId
                  AND o.orders_id = op.orders_id
                  AND op.orders_products_id = opd.orders_products_id
                  AND opd.orders_products_filename != ''";
        if (0 < count($orderStatusList)) {
            $sql .= ' AND o.orders_status in (:orderStatusId)';
        }

        $mapping = array(TABLE_ORDERS_PRODUCTS_DOWNLOAD, TABLE_ORDERS_PRODUCTS, TABLE_ORDERS);
        return ZMRuntime::getDatabase()->query($sql, array('orderId' => $orderId, 'orderStatusId' => $orderStatusList), $mapping, 'Download');
    }

}

?>
