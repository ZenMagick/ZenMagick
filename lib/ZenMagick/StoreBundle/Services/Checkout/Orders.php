<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

namespace ZenMagick\StoreBundle\Services\Checkout;

use ZMRuntime;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Database\QueryDetails;
use ZenMagick\Base\Database\SqlAware;

/**
 * Orders.
 *
 * @author DerManoMann
 */
class Orders extends ZMObject implements SqlAware
{
    /**
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array())
    {
        $methods = array('getOrdersForAccountId', 'getOrdersForStatusId', 'getAllOrders', 'findOrdersForDateTimeRange');
        if (in_array($method, $methods)) {
            return call_user_func_array(array($this, $method.'QueryDetails'), $args);
        }

        return null;
    }

    /**
     * Get all orders.
     *
     * @param int languageId Language id.
     * @param int limit Optional limit; default is <code>0</code> for all.
     * @return ZenMagick\Base\Database\QueryDetails Query details.
     */
    protected function getAllOrdersQueryDetails($languageId, $limit=0)
    {
        $sql = "SELECT o.*, s.orders_status_name
                FROM %table.orders% o, %table.orders_total%  ot, %table.orders_status% s
                WHERE o.orders_id = ot.orders_id
                  AND ot.class = 'ot_total'
                  AND o.orders_status = s.orders_status_id
                  AND s.language_id = :languageId
                ORDER BY orders_id DESC";
        if (0 < $limit) {
            $sql .= " LIMIT ".$limit;
        }
        $args = array('languageId' => $languageId);

        return new QueryDetails(ZMRuntime::getDatabase(), $sql, $args, array('orders', 'orders_total', 'orders_status'), 'ZenMagick\StoreBundle\Entity\Order\Order', 'o.orders_id');
    }

    /**
     * Get all orders.
     *
     * @param int languageId Language id.
     * @param int limit Optional limit; default is <code>0</code> for all.
     * @return array List of <code>ZenMagick\StoreBundle\Entity\Order\Order</code> instances.
     */
    public function getAllOrders($languageId, $limit=0)
    {
        $details = $this->getAllOrdersQueryDetails($languageId, $limit);

        return $details->query();
    }

    /**
     * Get order for the given id.
     *
     * @param int id The order id.
     * @param int languageId Language id.
     * @return ZenMagick\StoreBundle\Entity\Order\Order A order or <code>null</code>.
     */
    public function getOrderForId($orderId, $languageId)
    {
        $sql = "SELECT o.*, s.orders_status_name
                FROM %table.orders% o, %table.orders_total%  ot, %table.orders_status% s
                WHERE o.orders_id = :orderId
                  AND o.orders_id = ot.orders_id
                  AND ot.class = 'ot_total'
                  AND o.orders_status = s.orders_status_id
                  AND s.language_id = :languageId";
        $args = array('orderId' => $orderId, 'languageId' => $languageId);
        $order = ZMRuntime::getDatabase()->querySingle($sql, $args, array('orders', 'orders_total', 'orders_status'), 'ZenMagick\StoreBundle\Entity\Order\Order');

        return $order;
    }

    /**
     * Get all orders for the given account id.
     *
     * @param int accountId The account id.
     * @param int languageId Language id.
     * @param int limit Optional result limit.
     * @return ZenMagick\Base\Database\QueryDetails Query details.
     */
    protected function getOrdersForAccountIdQueryDetails($accountId, $languageId, $limit=0)
    {
        // order only
        $sqlLimit = 0 != $limit ? " LIMIT ".$limit : "";
        $sql = "SELECT o.*, s.orders_status_name
                FROM %table.orders% o, %table.orders_total%  ot, %table.orders_status% s
                WHERE o.customers_id = :accountId
                  AND o.orders_id = ot.orders_id
                  AND ot.class = 'ot_total'
                  AND o.orders_status = s.orders_status_id
                  AND s.language_id = :languageId
                ORDER BY orders_id DESC".$sqlLimit;
        $args = array('accountId' => $accountId, 'languageId' => $languageId);

        return new QueryDetails(ZMRuntime::getDatabase(), $sql, $args, array('orders', 'orders_total', 'orders_status'), 'ZenMagick\StoreBundle\Entity\Order\Order', 'o.orders_id');
    }

    /**
     * Get all orders for the given account id.
     *
     getOrdersForAccountId* @param int accountId The account id.
     * @param int languageId Language id.
     * @param int limit Optional result limit.
     * @return array List of <code>ZenMagick\StoreBundle\Entity\Order\Order</code> instances.
     */
    public function getOrdersForAccountId($accountId, $languageId, $limit=0)
    {
        $details = $this->getOrdersForAccountIdQueryDetails($accountId, $languageId, $limit);

        return $details->query();
    }

    /**
     * Get all orders for a given order status.
     *
     * @param int statusId The order status.
     * @param int languageId Language id.
     * @return ZenMagick\Base\Database\QueryDetails Query details.
     */
    protected function getOrdersForStatusIdQueryDetails($statusId, $languageId)
    {
        // order only
        $sql = "SELECT o.*, s.orders_status_name
                FROM %table.orders% o, %table.orders_total%  ot, %table.orders_status% s
                WHERE o.orders_status = :orderStatusId
                  AND o.orders_id = ot.orders_id
                  AND ot.class = 'ot_total'
                  AND o.orders_status = s.orders_status_id
                  AND s.language_id = :languageId
                ORDER BY orders_id DESC";
        $args = array('orderStatusId' => $statusId, 'languageId' => $languageId);

        return new QueryDetails(ZMRuntime::getDatabase(), $sql, $args, array('orders', 'orders_total', 'orders_status'), 'ZenMagick\StoreBundle\Entity\Order\Order', 'o.orders_id');
    }

    /**
     * Get all orders for a given order status.
     *
     * @param int statusId The order status.
     * @param int languageId Language id.
     * @return array List of <code>ZenMagick\StoreBundle\Entity\Order\Order</code> instances.
     */
    public function getOrdersForStatusId($statusId, $languageId)
    {
        $details = $this->getOrdersForStatusIdQueryDetails($statusId, $languageId);

        return $details->query();
    }

    /**
     * Get order status history for order id.
     *
     * @param int orderId The order id.
     * @param int languageId Language id.
     * @return array List of <code>ZenMagick\StoreBundle\Entity\Order\OrderStatusHistory</code> instances.
     */
    public function getOrderStatusHistoryForId($orderId, $languageId)
    {
        $sql = "SELECT os.orders_status_name, osh.*
                FROM %table.orders_status% os, %table.orders_status_history% osh
                WHERE osh.orders_id = :orderId
                  AND osh.orders_status_id = os.orders_status_id
                  AND os.language_id = :languageId
                ORDER BY osh.date_added";
        $args = array('orderId' => $orderId, 'languageId' => $languageId);

        return ZMRuntime::getDatabase()->fetchAll($sql, $args, array('orders_status_history', 'orders_status'), 'ZenMagick\StoreBundle\Entity\Order\OrderStatusHistory');
    }

    /**
     * Create new order status history entry.
     *
     * @param ZenMagick\StoreBundle\Entity\Order\OrderStatusHistory orderStatus The new order status.
     * @return ZenMagick\StoreBundle\Entity\Order\OrderStatusHistory The created order status (incl id).
     */
    public function createOrderStatusHistory($orderStatus)
    {
        if (null == $orderStatus->getDateAdded()) {
            $orderStatus->setDateAdded(new \DateTime());
        }

        return ZMRuntime::getDatabase()->createModel('orders_status_history', $orderStatus);
    }

    /**
     * Get order items.
     *
     * @param int orderId The order id.
     * @return array List of <code>ZenMagick\StoreBundle\Entity\Order\OrderItem</code> instances.
     */
    public function getOrderItems($orderId)
    {
        $sql = "SELECT *
                FROM %table.orders_products%
                WHERE orders_id = :orderId
                ORDER BY orders_products_id";
        $items = array();
        $attributes = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array('orderId' => $orderId), 'orders_products', 'ZenMagick\StoreBundle\Entity\Order\OrderItem') as $item) {
            // lookup selected attributes as well
            $sql = "SELECT *
                    FROM %table.orders_products_attributes%
                    WHERE orders_id = :orderId
                      AND orders_products_id = :orderItemId";
            $args = array('orderId' => $orderId, 'orderItemId' => $item->getId());
            foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, 'orders_products_attributes', 'ZMAttributeValue') as $value) {
                if (!array_key_exists($value->getAttributeId(), $attributes)) {
                    $attribute = Beans::getBean("ZenMagick\StoreBundle\Entity\Catalog\Attribute");
                    $attribute->setName($value->getAttributeName());
                    $attributes[$value->getAttributeId()] = $attribute;
                }
                $attributes[$value->getAttributeId()]->addValue($value);
            }
            foreach ($attributes as $attribute) {
                $item->addAttribute($attribute);
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get order total lines.
     *
     * @param int orderId The order id.
     * @return array Map of <code>ZenMagick\StoreBundle\Entity\Order\OrderTotalLine</code> instances with the type as key.
     */
    public function getOrderTotalLines($orderId)
    {
        $sql = "SELECT * FROM %table.orders_total%
                WHERE orders_id = :orderId
                ORDER BY sort_order";
        $totals = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array('orderId' => $orderId), 'orders_total', 'ZenMagick\StoreBundle\Entity\Order\OrderTotalLine') as $total) {
            $totals[] = $total;
        }

        return $totals;
    }

    /**
     * Update an existing order.
     *
     * <p><strong>NOTE: Currently this will update the orders table only!</strong></p>
     *
     * @param ZenMagick\StoreBundle\Entity\Order\Order The order.
     * @return ZenMagick\StoreBundle\Entity\Order\Order The updated order.
     */
    public function updateOrder($order)
    {
        return ZMRuntime::getDatabase()->updateModel('orders', $order);
    }

    /**
     * Convert a numeric range definition into an array of single values.
     *
     * <p>A range might be a single value, a range; for example <em>3-8</em> or a list of both.</p>
     * <p>Valid examples of ranges are:</p>
     * <ul>
     *  <li>3</li>
     *  <li>3,4,8</li>
     *  <li>3,4-6,8</li>
     *  <li>1,3-5,9,13,100-302</li>
     * </ul>
     *
     * @param string range The range value.
     * @return array List of numeric (int) values.
     * @copyright the zen-cart developers.
     */
    public function parseRange($range)
    {
        $arr = array();
        foreach (explode(',', $range) as $token) {
            if (!empty($token)) {
                $elems = explode('-', $token);
                $size = count($elems);
                if (1 == $size && !empty($elems[0])) {
                    $elem = (int) $elems[0];
                    $arr[$elem] = $elem;
                } elseif (2 == $size && !empty($elems[0]) && !empty($elems[1])) {
                    for ($ii=(int) $elems[0]; $ii<=(int) $elems[1]; ++$ii) {
                        $arr[$ii] = $ii;
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * Get downloads for order.
     *
     * @param int orderId The order id.
     * @param array orderStatusList Optional array of order stati to check; default is null to use the configured range, (empty array will load all).
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Order\Download</code> instances.
     */
    public function getDownloadsForOrderId($orderId, $orderStatusList=null)
    {
        if (null === $orderStatusList) {
            // build default list
            $orderStatusList = $this->parseRange(Runtime::getSettings()->get('downloadOrderStatusRange'));
        }
        $sql = "SELECT o.date_purchased, o.orders_status, opd.*
                FROM %table.orders% o, %table.orders_products% op, %table.orders_products_download% opd
                WHERE o.orders_id = :orderId
                  AND o.orders_id = op.orders_id
                  AND op.orders_products_id = opd.orders_products_id
                  AND opd.orders_products_filename != ''";
        if (0 < count($orderStatusList)) {
            $sql .= ' AND o.orders_status in (:orderStatusId)';
        }

        $mapping = array('orders_products_download', 'orders_products', 'orders');

        return ZMRuntime::getDatabase()->fetchAll($sql, array('orderId' => $orderId, 'orderStatusId' => $orderStatusList), $mapping, 'ZenMagick\StoreBundle\Entity\Order\Download');
    }

    /**
     * Get a list of all order stati.
     *
     * @param int languageId Language id.
     * @return array List of <code>ZMObject</code> instances.
     */
    public function getOrderStatusList($languageId)
    {
        $sql = "SELECT orders_status_id, orders_status_name
                FROM %table.orders_status%
                WHERE language_id = :languageId
                ORDER BY orders_status_id";

        return ZMRuntime::getDatabase()->fetchAll($sql, array('languageId' => $languageId), 'orders_status', 'ZenMagick\StoreBundle\Entity\Order\OrderStatus');
    }

    /**
     * Re-stock products from a given order.
     *
     * @param int orderId The order to re-stock.
     */
    public function restockFromOrder($orderId)
    {
        $productService = $this->container->get('productService');
        foreach ($this->getOrderItems($orderId) as $item) {
            if (null != ($product = $productService->getProductForId($item->getProductId()))) {
                $product->setQuantity($product->getQuantity() + $item->getQuantity());
                $productService->updateProduct($product);
            }
        }
    }

    /**
     * Find orders for the given date/time range.
     *
     * @param DateTime from The from date/time (included).
     * @param DateTime to The to date/time (excluded).
     * @param int languageId Language id.
     * @return array A list of matching orders.
     */
    protected function findOrdersForDateTimeRangeQueryDetails($from, $to, $languageId)
    {
        $sql = "SELECT o.*, s.orders_status_name, ots.value as shippingValue
                FROM %table.orders% o, %table.orders_total%  ot, %table.orders_status% s, %table.orders_total%  ots
                WHERE date_purchased >= :1#orderDate AND date_purchased <= :2#orderDate
                  AND o.orders_id = ot.orders_id
                  AND ot.class = 'ot_total'
                  AND o.orders_id = ots.orders_id
                  AND ots.class = 'ot_shipping'
                  AND o.orders_status = s.orders_status_id
                  AND s.language_id = :languageId
                ORDER BY orders_id DESC";
        $args = array('languageId' => $languageId, '1#orderDate' => $from, '2#orderDate' => $to);

        return new QueryDetails(ZMRuntime::getDatabase(), $sql, $args, array('orders', 'orders_total', 'orders_status'), 'ZenMagick\StoreBundle\Entity\Order\Order', 'o.orders_id');
    }

    /**
     * Find orders for the given date/time range.
     *
     * @param DateTime from The from date/time (included).
     * @param DateTime to The to date/time (excluded).
     * @param int languageId Language id.
     * @return array A list of matching orders.
     */
    public function findOrdersForDateTimeRange($from, $to, $languageId)
    {
        $details = $this->findOrdersForDateTimeRangeQueryDetails($from, $to, $languageId);

        return $details->query();
    }

}
