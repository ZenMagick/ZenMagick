<?php

/**
 * Test order service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMOrders extends ZMTestCase {

    /**
     * Test create product.
     */
    public function testUpdateOrderStatus() {
        $order = ZMOrders::instance()->getOrderForId(1);
        $order->setOrderStatusId(4);
        ZMOrders::instance()->updateOrder($order);
        $order = ZMOrders::instance()->getOrderForId(1);
        $this->assertEqual(4, $order->getOrderStatusId());
        $this->assertEqual('Update', $order->getStatusName());
        $order->setOrderStatusId(2);
        ZMOrders::instance()->updateOrder($order);
        $order = ZMOrders::instance()->getOrderForId(1);
        $this->assertEqual(2, $order->getOrderStatusId());
        $this->assertEqual('Processing', $order->getStatusName());
    }

    /**
     * Test get orders for status.
     */
    public function testGetOrdersForStatusId() {
        $orders = ZMOrders::instance()->getOrdersForStatusId(2);
        $this->assertNotNull($orders);
        $this->assertTrue(0 < count($orders));
    }

    /**
     * Test order account.
     */
    public function testGetAccount() {
        $order = ZMOrders::instance()->getOrderForId(1);
        $account = $order->getAccount();
        $this->assertNotNull($account);
        $this->assertNotNull($account->getLastName());
        $this->assertNotNull($account->getEmail());
    }

    /**
     * Test change address.
     */
    public function testChangeAddress() {
        $order = ZMOrders::instance()->getOrderForId(1);
        $this->assertNotNull($order);

        $address = ZMLoader::make('Address');
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
    }

    /**
     * Test downloads.
     */
    public function testDownloads() {
        $downloads = ZMOrders::instance()->getDownloadsForOrderId(62, array(1));
        foreach ($downloads as $dl) {
            echo $dl->getId().': isd:'.$dl->isDownloadable()."<BR>";
            echo $dl->getId().': isl:'.$dl->isLimited()."<BR>";
            echo $dl->getId().': size:'.$dl->getFileSize()."<BR>";
        }
    }

}

?>
