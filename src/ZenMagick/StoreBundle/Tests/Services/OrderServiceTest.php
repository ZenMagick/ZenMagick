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
namespace ZenMagick\StoreBundle\Tests\Services;

use ZenMagick\Base\Beans;
use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test order service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo create custom order and add specific asserts to test explicit data
 */
class OrderServiceTest extends BaseTestCase
{
    /**
     * Test create product.
     */
    public function testUpdateOrderStatus()
    {
        $orderService = $this->get('orderService');
        $order = $orderService->getOrderForId(1, 1);
        if (null != $order) {
            $order->setOrderStatusId(4);
            $orderService->updateOrder($order);
            $order = $orderService->getOrderForId(1, 1);
            $this->assertEquals(4, $order->getOrderStatusId());
            $this->assertEquals('Update', $order->getStatusName());
            $order->setOrderStatusId(2);
            $orderService->updateOrder($order);
            $order = $orderService->getOrderForId(1, 1);
            $this->assertEquals(2, $order->getOrderStatusId());
            $this->assertEquals('Processing', $order->getStatusName());
        } else {
            $this->markTestIncomplete('no test order found');
        }
    }

    /**
     * Test get orders for status.
     */
    public function testGetOrdersForStatusId()
    {
        $orders = $this->get('orderService')->getOrdersForStatusId(2, 1);
        $this->assertNotNull($orders);
    }

    /**
     * Test order account.
     */
    public function testGetAccount()
    {
        $order = $this->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $account = $order->getAccount();
            $this->assertNotNull($account);
            $this->assertNotNull($account->getLastName());
            $this->assertNotNull($account->getEmail());
        } else {
            $this->markTestIncomplete('test order not found');
        }
    }

    /**
     * Test change address.
     */
    public function testChangeAddress()
    {
        $order = $this->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $address = Beans::getBean('ZenMagick\StoreBundle\Entity\Address');
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

            $this->assertEquals('12345', $order->get('billing_postcode'));
        } else {
            $this->markTestIncomplete('test order not found');
        }
    }

    /**
     * Test downloads.
     */
    public function testDownloads()
    {
        $downloads = $this->get('orderService')->getDownloadsForOrderId(12);
        foreach ($downloads as $dl) {
            $this->assertTrue($dl->getorderDate() instanceof \DateTime);
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
    public function testGetOrderStatusHistory()
    {
        $order = $this->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $orderStatusHistory = $order->getOrderStatusHistory(1);
            $this->assertNotNull($orderStatusHistory);
            $this->assertTrue(is_array($orderStatusHistory));
            if ($this->assertTrue(0 < count($orderStatusHistory))) {
                // check first entry
                $orderStatus = $orderStatusHistory[0];
                $this->assertEquals(1, $orderStatus->getId());
                $this->assertEquals(1, $orderStatus->getOrderId());
                $this->assertEquals('Pending', $orderStatus->getName());
                $this->assertEquals(true, $orderStatus->isCustomerNotified());
                $this->assertEquals(null, $orderStatus->getComment());
            }
        } else {
            $this->markTestIncomplete('test order not found');
        }
    }

    /**
     * Test create order status history.
     */
    public function testCreateOrderStatusHistory()
    {
        $orderService = $this->get('orderService');
        $order = $orderService->getOrderForId(1, 1);
        if (null != $order) {
            $orderStatusHistory = $order->getOrderStatusHistory(1);
            $this->assertNotNull($orderStatusHistory);
            $this->assertTrue(is_array($orderStatusHistory));
            $oldCount = count($orderStatusHistory);

            $newOrderStatus = Beans::getBean('ZenMagick\StoreBundle\Entity\Order\OrderStatusHistory');
            $newOrderStatus->setOrderId(1);
            $newOrderStatus->setOrderStatusId(2);
            $newOrderStatus = $orderService->createOrderStatusHistory($newOrderStatus);
            // check for new primary key
            $this->assertTrue(0 != $newOrderStatus->getId());

            $orderStatusHistory = $order->getOrderStatusHistory(1);
            $this->assertNotNull($orderStatusHistory);
            $this->assertTrue(is_array($orderStatusHistory));
            $this->assertEquals($oldCount+1, count($orderStatusHistory));
            // check created entry
            $createdOrderStatus = array_pop($orderStatusHistory);
            // make sure this is set
            $this->assertEquals('Processing', $createdOrderStatus->getName());
            $this->assertNotNull($createdOrderStatus->getDateAdded());

            // clean up
            $sql = "DELETE FROM %table.orders_status_history% WHERE orders_status_history_id = :orderStatusHistoryId";
            \ZMRuntime::getDatabase()->updateObj($sql, array('orderStatusHistoryId' => $newOrderStatus->getId()), 'orders_status_history');
        } else {
            $this->markTestIncomplete('test order not found');
        }
    }

    /**
     * Test get order total lines.
     */
    public function testGetOrderTotalLines()
    {
        $order = $this->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $orderTotalLines = $order->getOrderTotalLines();
            $this->assertNotNull($orderTotalLines);
            $this->assertTrue(is_array($orderTotalLines));
            $this->assertEquals(3, count($orderTotalLines));

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
                $this->assertEquals('Total:', $total->getName());
                //$this->assertEquals('$42.49', $total->getValue());
                //$this->assertEquals(42.49, $total->getAmount());
                $this->assertEquals('ot_total', $total->getType());
            }
        } else {
            $this->markTestIncomplete('test order not found');
        }
    }

    /*
     * Test order items.
     */
    public function testOrderItems()
    {
        $order = $this->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $items = $order->getOrderItems();
            if ($this->assertTrue(0 < count($items))) {
                $item = $items[0];
                $this->assertNotNull($item);
                /*
                $this->assertEquals(12, $item->getProductId());
                $this->assertEquals(1, $item->getQuantity());
                $this->assertNotNull($item->getTaxRate());
                $this->assertEquals(0, $item->getTaxRate()->getRate());
                $this->assertEquals(0, count($item->getAttributes()));
                */
            }
        } else {
            $this->markTestIncomplete('test order not found');
        }
    }

    /*
     * Test order item attributes.
     */
    public function testOrderItemAttributes()
    {
        $order = $this->get('orderService')->getOrderForId(1, 1);
        if (null != $order) {
            $items = $order->getOrderItems();
            if ($this->assertTrue(0 < count($items))) {
                $item = $items[0];
                $attributes = $item->getAttributes();
                //if ($this->assertEquals(2, count($attributes))) {
                if (false) {
                    // expect productId 1 with model:premium and memory:16MB
                    $attribute = $attributes[0];
                    $this->assertEquals('Model', $attribute->getName());
                    $values = $attribute->getValues();
                    $this->assertEquals(1, count($values));
                    $value = $values[0];
                    $this->assertEquals('Premium', $value->getName());

                    $attribute = $attributes[1];
                    $this->assertEquals('Memory', $attribute->getName());
                    $values = $attribute->getValues();
                    $this->assertEquals(1, count($values));
                    $value = $values[0];
                    $this->assertEquals('16 mb', $value->getName());
                }
            }
        } else {
            $this->markTestIncomplete('test order not found');
        }
    }

    /**
     * Test order status list.
     */
    public function testOrderStatusList()
    {
        $list = $this->get('orderService')->getOrderStatusList(1);
        if ($this->assertNotNull($list)) {
            $this->assertTrue(0 < count($list));
        }
    }

}
