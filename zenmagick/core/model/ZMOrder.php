<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
class ZMOrder extends ZMModel {
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
        parent::__construct();

        $this->id_ = $id;
        $this->zenOrder_ = null;
        $this->zmOrders_ = null;
    }

    /**
     * Create order.
     *
     * @param int id The order id.
     */
    function __construct($id) {
        $this->ZMOrder($id);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the order id.
     *
     * @return int The order id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    function getAccountId() { return $this->accountId_; }

    /**
     * Get the order status.
     *
     * @return string The order status.
     */
    function getStatus() { return $this->status_; }

    /**
     * Get the order date.
     *
     * @return string The order date.
     */
    function getOrderDate() { return $this->orderDate_; }

    /**
     * Get the account for this order.
     *
     * @return ZMAccount The account.
     */
    function getAccount() { return $this->account_; }

    /**
     * Get the shipping address.
     *
     * @return ZMAddress The shipping address or <code>null</code>.
     */
    function getShippingAddress() { return $this->shippingAddress_; }

    /**
     * Get the billing address.
     *
     * @return ZMAddress The billing address or <code>null</code>.
     */
    function getBillingAddress() { return $this->billingAddress_; }

    /**
     * Checks if the order has a shipping address.
     *
     * @return bool <code>true</code> if a shipping address exists, <code>false</code> if not.
     */
    function hasShippingAddress() {
        return !(empty($this->shippingAddress_->lastName_) && empty($this->shippingAddress_->address_));
    }

    /**
     * Get the order items.
     *
     * @return array A list of <code>ZMOrderItem<code> instances.
     */
    function getOrderItems() { return $this->zmOrders_->_getOrderItems($this); }

    /**
     * Get the order status history.
     *
     * @return array A list of previous order stati.
     */
    function getOrderStati() { return $this->zmOrders_->_getOrderStatiForId($this->id_); }

    /**
     * Get the order total.
     *
     * @return float The order total.
     */
    function getTotal() { return $this->total_; }

    /**
     * Get all order totals.
     *
     * @return array A list of <code>ZMOrderTotal</code> instances.
     */
    function getOrderTotals() { return $this->zmOrders_->_getOrderTotals($this); }

    /**
     * Check if the order it pickup.
     *
     * @return bool <code>true</code> if the order is store pickup, <code>false</code> if not.
     */
    function isStorePickup() {
        $totals = $this->getOrderTotals();
        foreach ($totals as $total) {
            // AAAAAAAAAAAAAAAAAAAAAAAAAAARRRRRRRRRRRRRRRRRHHHHHHHHHHH
            if ('Store Pickup (Walk In):' == $total->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the payment type.
     *
     * @return ZMPaymentType A payment type or <code>null</code> if N/A.
     */
    function getPaymentType() {
        $payments =& $this->create("Payments");
        return $payments->getSelectedPaymentType();
    }

}

?>
