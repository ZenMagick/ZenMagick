<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * A single order.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMOrder {
    var $id_;
    var $accountId_;
    var $status_;
    var $orderDate_;
    var $totalValue_;
    var $account_;
    var $shippingAddress_;
    var $billingAddress_;
    var $total_;

    // ref to zen order
    var $zenOrder_;
    // ref to ZMOrders
    var $zmOrders_;


    /**
     * Create order.
     *
     * @param int id The order id.
     */
    function ZMOrder($id) {
        $this->id_ = $id;
        $this->zenOrder_ = null;
        $this->zmOrders_ = null;
    }

    // create new instance
    function __construct($id) {
        $this->ZMOrder($id);
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getAccountId() { return $this->accountId_; }
    function getStatus() { return $this->status_; }
    function getOrderDate() { return $this->orderDate_; }
    function getAccount() { return $this->account_; }
    function getShippingAddress() { return $this->shippingAddress_; }
    function getBillingAddress() { return $this->billingAddress_; }
    function hasShippingAddress() {
        return !(empty($this->shippingAddress_->lastName_) && empty($this->shippingAddress_->address_));
    }

    function getOrderItems() { return $this->zmOrders_->_getOrderItems($this); }
    function getOrderStati() { return $this->zmOrders_->_getOrderStatiForId($this->id_); }
    function getTotal() { return $this->total_; }
    function getOrderTotals() { return $this->zmOrders_->_getOrderTotals($this); }

}

?>
