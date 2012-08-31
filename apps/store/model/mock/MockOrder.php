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
namespace ZenMagick\apps\store\model\mock;


/**
 * Mock order.
 *
 * @author DerManoMann
 */
class MockOrder extends \ZMOrder {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->setId(3);
        $this->setOrderDate(time());
    }


    /**
     * {@inheritDoc}
     */
    public function getAccount() {
        return new MockAccount();
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingAddress() {
        return new MockAddress();
    }

    /**
     * {@inheritDoc}
     */
    public function getBillingAddress() {
        return new MockAddress();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderItems() {
        return array(new MockOrderItem(1), new MockOrderItem(2));
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderTotalLines() {
        return array(new \ZMOrderTotalLine('Tax', 9, '$9.00'), new \ZMOrderTotalLine('Subtotal', 119.20, '$119.20'), new \ZMOrderTotalLine('Total', 119.20, '$119.20'));
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentType() {
        return new MockPaymentType();
    }

}
