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

use ZenMagick\Base\Beans;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test order service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 * @todo create custom order and add specific asserts to test explicit data
 */
class TestZMOrders extends TestCase {

    /**
     * Test create product.
     */
    public function testUpdateOrderStatus() {
        $orderService = $this->container->get('orderService');
        $order = $orderService->getOrderForId(1, 1);
        if (null != $order) {
            $order->setOrderStatusId(4);
            $orderService->updateOrder($order);
            $order = $orderService->getOrderForId(1, 1);
            $this->assertEqual(4, $order->getOrderStatusId());
            $this->assertEqual('Update', $order->getStatusName());
            $order->setOrderStatusId(2);
            $orderService->updateOrder($order);
            $order = $orderService->getOrderForId(1, 1);
            $this->assertEqual(2, $order->getOrderStatusId());
            $this->assertEqual('Processing', $order->getStatusName());
        } else {
            $this->skip('no test order found');
        }
    }

    /**
     * Test get orders for status.
     */
    public function testGetOrdersForStatusId() {
        $orders = $this->container->get('orderService')->getOrdersForStatusId(2, 1);
        $this->assertNotNull($orders);
    }

    /**
     * Test order account.
     */
    public function testGetAccount() {
        $order = $this->container->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $account = $order->getAccount();
            $this->assertNotNull($account);
            $this->assertNotNull($account->getLastName());
            $this->assertNotNull($account->getEmail());
        } else {
            $this->skip('test order not found');
        }
    }

    /**
     * Test change address.
     */
    public function testChangeAddress() {
        $order = $this->container->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $address = Beans::getBean('ZMAddress');
            $address->setFirstName('foo');
            $address->setLastName('bar');
            $address->setCompanyName('dooh inc.');
            $address->setAddress('street 1');
            $address->setSuburb('sub');
            $address->setPostcode('12345');
            $address->setCity('Christchurch');
            $address->setState('Canterbury');
            $address->setCountryId(153);
            //address format is derived from country

            $order->setBillingAddress($address);

            $this->assertEqual('12345', $order->get('billing_postcode'));
        } else {
            $this->skip('test order not found');
        }
    }

    /**
     * Test downloads.
     */
    public function testDownloads() {
        $downloads = $this->container->get('orderService')->getDownloadsForOrderId(12);
        foreach ($downloads as $dl) {
            $this->assertTrue($dl->getorderDate() instanceof DateTime);
            echo 'id: '.$dl->getId()."<BR>";
            echo '* isDownloadable:'.$dl->isDownloadable()."<BR>";
            echo '* isLimited:'.$dl->isLimited()."<BR>";
            echo '* getFileSize:'.$dl->getFileSize()."<BR>";
            echo '* getOrderDate:'.$dl->getOrderDate()->format('Y-m-d')."<BR>";
            echo '* getMaxDays:'.$dl->getMaxDays()."<BR>";
            echo '* getExpiryDate:'.$dl->getExpiryDate()->format('Y-m-d')."<BR>";
        }
    }

    /**
     * Test get order status history.
     */
    public function testGetOrderStatusHistory() {
        $order = $this->container->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $orderStatusHistory = $order->getOrderStatusHistory(1);
            $this->assertNotNull($orderStatusHistory);
            $this->assertTrue(is_array($orderStatusHistory));
            if ($this->assertTrue(0 < count($orderStatusHistory))) {
                // check first entry
                $orderStatus = $orderStatusHistory[0];
                $this->assertEqual(1, $orderStatus->getId());
                $this->assertEqual(1, $orderStatus->getOrderId());
                $this->assertEqual('Pending', $orderStatus->getName());
                $this->assertEqual(true, $orderStatus->isCustomerNotified());
                $this->assertEqual(null, $orderStatus->getComment());
            }
        } else {
            $this->skip('test order not found');
        }
    }

    /**
     * Test create order status history.
     */
    public function testCreateOrderStatusHistory() {
        $orderService = $this->container->get('orderService');
        $order = $orderService->getOrderForId(1, 1);
        if (null != $order) {
            $orderStatusHistory = $order->getOrderStatusHistory(1);
            $this->assertNotNull($orderStatusHistory);
            $this->assertTrue(is_array($orderStatusHistory));
            $oldCount = count($orderStatusHistory);

            $newOrderStatus = Beans::getBean('ZMOrderStatus');
            $newOrderStatus->setOrderId(1);
            $newOrderStatus->setOrderStatusId(2);
            $newOrderStatus = $orderService->createOrderStatusHistory($newOrderStatus);
            // check for new primary key
            $this->assertTrue(0 != $newOrderStatus->getId());

            $orderStatusHistory = $order->getOrderStatusHistory(1);
            $this->assertNotNull($orderStatusHistory);
            $this->assertTrue(is_array($orderStatusHistory));
            $this->assertEqual($oldCount+1, count($orderStatusHistory));
            // check created entry
            $createdOrderStatus = array_pop($orderStatusHistory);
            // make sure this is set
            $this->assertEqual('Processing', $createdOrderStatus->getName());
            $this->assertNotNull($createdOrderStatus->getDateAdded());

            // clean up
            $sql = "DELETE FROM %table.orders_status_history% WHERE orders_status_history_id = :orderStatusHistoryId";
            ZMRuntime::getDatabase()->updateObj($sql, array('orderStatusHistoryId' => $newOrderStatus->getId()), 'orders_status_history');
        } else {
            $this->skip('test order not found');
        }
    }

    /**
     * Test get order total lines.
     */
    public function testGetOrderTotalLines() {
        $order = $this->container->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $orderTotalLines = $order->getOrderTotalLines();
            $this->assertNotNull($orderTotalLines);
            $this->assertTrue(is_array($orderTotalLines));
            $this->assertEqual(3, count($orderTotalLines));

            // find ot_total
            $total = null;
            foreach ($orderTotalLines as $orderTotalLine) {
                if ('ot_total' == $orderTotalLine->getType()) {
                    $total = $orderTotalLine;
                    break;
                }
            }
            if ($this->assertNotNull($total)) {
                // test total total
                $this->assertEqual('Total:', $total->getName());
                //$this->assertEqual('$42.49', $total->getValue());
                //$this->assertEqual(42.49, $total->getAmount());
                $this->assertEqual('ot_total', $total->getType());
            }
        } else {
            $this->skip('test order not found');
        }
    }

    /*
     * Test order items.
     */
    public function testOrderItems() {
        $order = $this->container->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $items = $order->getOrderItems();
            if ($this->assertTrue(0 < count($items))) {
                $item = $items[0];
                $this->assertNotNull($item);
                /*
                $this->assertEqual(12, $item->getProductId());
                $this->assertEqual(1, $item->getQuantity());
                $this->assertNotNull($item->getTaxRate());
                $this->assertEqual(0, $item->getTaxRate()->getRate());
                $this->assertEqual(0, count($item->getAttributes()));
                */
            }
        } else {
            $this->skip('test order not found');
        }
    }

    /*
     * Test order item attributes.
     */
    public function testOrderItemAttributes() {
        $order = $this->container->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $items = $order->getOrderItems();
            if ($this->assertTrue(0 < count($items))) {
                $item = $items[0];
                $attributes = $item->getAttributes();
                //if ($this->assertEqual(2, count($attributes))) {
                if (false) {
                    // expect productId 1 with model:premium and memory:16MB
                    $attribute = $attributes[0];
                    $this->assertEqual('Model', $attribute->getName());
                    $values = $attribute->getValues();
                    $this->assertEqual(1, count($values));
                    $value = $values[0];
                    $this->assertEqual('Premium', $value->getName());

                    $attribute = $attributes[1];
                    $this->assertEqual('Memory', $attribute->getName());
                    $values = $attribute->getValues();
                    $this->assertEqual(1, count($values));
                    $value = $values[0];
                    $this->assertEqual('16 mb', $value->getName());
                }
            }
        } else {
            $this->skip('test order not found');
        }
    }

    /**
     * Test order status list.
     */
    public function testOrderStatusList() {
        $list = $this->container->get('orderService')->getOrderStatusList(1);
        if ($this->assertNotNull($list)) {
            $this->assertTrue(0 < count($list));
        }
    }

}
