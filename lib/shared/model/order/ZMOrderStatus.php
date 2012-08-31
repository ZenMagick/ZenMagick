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

use ZenMagick\Base\ZMObject;

/**
 * Order status.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.order
 */
class ZMOrderStatus extends ZMObject {
    private $orderId_;
    private $name_;
    private $dateAdded_;
    private $customerNotified_;
    private $comment_;


    /**
     * Create new status.
     */
    public function __construct() {
        parent::__construct();
        $this->setId(0);
        $this->orderId_ = 0;
        $this->setName(null);
        $this->customerNotified_ = false;
        $this->comment_ = null;
        $this->setDateAdded(null);
        $this->setLastModified(null);
    }


    /**
     * Get the order status (history) id.
     *
     * <p>This is the primary key id of the <em>order_status_history</em> table.</p>
     *
     * @return int The order status id.
     */
    public function getId() { return $this->get('orderStatusHistoryId'); }

    /**
     * Get the order id.
     *
     * @return int The order id.
     */
    public function getOrderId() { return $this->orderId_; }

    /**
     * Get the order status id.
     *
     * <p>This is the id corresponding with the name.
     *
     * @return int The order status id.
     */
    public function getOrderStatusId() { return $this->get('orderStatusId'); }

    /**
     * Get the order status name.
     *
     * @return string The order status name.
     */
    public function getName() { return $this->get('statusName'); }

    /**
     * Get the date it was added.
     *
     * @return string The date the status was changed.
     */
    public function getDateAdded() { return $this->dateAdded_; }

    /**
     * Has the customer been notified about this change.
     *
     * @return boolean <code>true</code> if the customer has been notified, <code>false</code> if not.
     */
    public function isCustomerNotified() { return $this->customerNotified_; }

    /**
     * Checks if a comment exists for this status.
     *
     * @return boolean </code>true</code> if a comment exist, <code>false</code> if not.
     */
    public function hasComment() { return !empty($this->comment_); }

    /**
     * Get the comment.
     *
     * @return string The comment (might be empty).
     */
    public function getComment() { return $this->comment_; }

    /**
     * Set the order status (history) id.
     *
     * @param int id The order status id.
     */
    public function setId($id) { $this->set('orderStatusHistoryId', $id); }

    /**
     * Set the order id.
     *
     * @param int orderId The order id.
     */
    public function setOrderId($orderId) { $this->orderId_ = $orderId; }

    /**
     * Set the order status id.
     *
     * @param int orderStatusId The order status id.
     */
    public function setOrderStatusId($orderStatusId) { $this->set('orderStatusId', $orderStatusId); }

    /**
     * Set the order status name.
     *
     * @param string name The order status name.
     */
    public function setName($name) { $this->set('statusName', $name); }

    /**
     * Set the date it was added.
     *
     * @param string dateAdded The date the status was changed.
     */
    public function setDateAdded($dateAdded) { $this->dateAdded_ = $dateAdded; }

    /**
     * Set whether the customer been notified about this change.
     *
     * @param boolean customerNotified <code>true</code> if the customer has been notified, <code>false</code> if not.
     */
    public function setCustomerNotified($customerNotified) { $this->customerNotified_ = $customerNotified; }

    /**
     * Set the comment.
     *
     * @param string comment The comment.
     */
    public function setComment($comment) { $this->comment_ = $comment; }

}
