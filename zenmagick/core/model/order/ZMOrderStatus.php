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
 * Order status.
 *
 * @author mano
 * @package org.zenmagick.model.order
 * @version $Id$
 */
class ZMOrderStatus extends ZMModel {
    var $id_;
    var $orderId_;
    var $name_;
    var $dateAdded_;
    var $customerNotified_;
    var $comment_;


    /**
     * Create new status.
     */
    function __construct() {
        parent::__construct();
        $this->id_ = 0;
        $this->orderId_ = 0;
        $this->name_ = '';
        $this->dateAdded_ = null;
        $this->customerNotified_ = false;
        $this->comment_ = '';
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the order status id.
     *
     * @return int The order status id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the order id.
     *
     * @return int The order id.
     */
    function getOrderId() { return $this->orderId_; }

    /**
     * Get the order status name.
     *
     * @return string The order status name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the date it was added.
     *
     * @return string The date the status was changed.
     */
    function getDateAdded() { return $this->dateAdded_; }

    /**
     * Has the customer been notified about this change.
     *
     * @return boolean <code>true</code> if the customer has been notified, <code>false</code> if not.
     */
    function isCustomerNotified() { return $this->customerNotified_; }

    /**
     * Checks if a comment exists for this status.
     *
     * @return boolean </code>true</code> if a comment exist, <code>false</code> if not.
     */
    function hasComment() { return !empty($this->comment_); }

    /**
     * Get the comment.
     *
     * @return string The comment (might be empty).
     */
    function getComment() { return $this->comment_; }

    /**
     * Set the order status id.
     *
     * @param int id The order status id.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Set the order id.
     *
     * @param int orderId The order id.
     */
    function setOrderId($orderId) { $this->orderId_ = $orderId; }

    /**
     * Set the order status name.
     *
     * @param string name The order status name.
     */
    function setName($name) { $this->name_ = $name; }

    /**
     * Set the date it was added.
     *
     * @param string dateAdded The date the status was changed.
     */
    function setDateAdded($dateAdded) { $this->dateAdded_ = $dateAdded; }

    /**
     * Set whether the customer been notified about this change.
     *
     * @param boolean customerNotified <code>true</code> if the customer has been notified, <code>false</code> if not.
     */
    function setCustomerNotified($customerNotified) { $this->customerNotified_ = $customerNotified; }

    /**
     * Set the comment.
     *
     * @param string comment The comment.
     */
    function setComment($comment) { $this->comment_ = $comment; }
}

?>
