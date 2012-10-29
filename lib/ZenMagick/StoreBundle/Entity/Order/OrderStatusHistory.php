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

namespace ZenMagick\StoreBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use ZenMagick\Base\ZMObject;

/**
 * Order status.
 *
 * @ORM\Table(name="orders_status_history",
 *  indexes={
 *      @ORM\Index(name="idx_orders_id_status_id_zen", columns={"orders_id", "orders_status_id"}),
 *  })
 * @ORM\Entity
 * @author DerManoMann
 */
class OrderStatusHistory extends ZMObject
{
    /**
     * @var integer $orderStatusHistoryId
     *
     * @ORM\Column(name="orders_status_history_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $orderStatusHistoryId;

    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="orders_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var integer $orderStatusId
     *
     * @ORM\Column(name="orders_status_id", type="integer", nullable=false)
     */
    private $orderStatusId;

    /**
     * @var \DateTime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;

    /**
     * @var integer $customerNotified
     *
     * @ORM\Column(name="customer_notified", type="boolean", nullable=true)
     */
    private $customerNotified;

    /**
     * @var string $comment
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comment;

    private $name;

    /**
     * Create new status.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId(0);
        $this->orderId = 0;
        $this->setName(null);
        $this->customerNotified = false;
        $this->comment = null;
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
    public function getId() { return $this->ordersStatusHistoryId; }

    /**
     * Get the order id.
     *
     * @return int The order id.
     */
    public function getOrderId() { return $this->orderId; }

    /**
     * Get the order status id.
     *
     * <p>This is the id corresponding with the name.
     *
     * @return int The order status id.
     */
    public function getOrderStatusId() { return $this->orderStatusId; }

    /**
     * Get the order status name.
     *
     * @return string The order status name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the date it was added.
     *
     * @return string The date the status was changed.
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Has the customer been notified about this change.
     *
     * @return boolean <code>true</code> if the customer has been notified, <code>false</code> if not.
     */
    public function getCustomerNotified() { return $this->customerNotified; }

    /**
     * Has the customer been notified about this change.
     *
     * @return boolean <code>true</code> if the customer has been notified, <code>false</code> if not.
     */
    public function isCustomerNotified() { return $this->customerNotified; }

    /**
     * Checks if a comment exists for this status.
     *
     * @return boolean </code>true</code> if a comment exist, <code>false</code> if not.
     */
    public function hasComment() { return !empty($this->comment); }

    /**
     * Get the comment.
     *
     * @return string The comment (might be empty).
     */
    public function getComment() { return $this->comment; }

    /**
     * Set the order status (history) id.
     *
     * @param int id The order status id.
     */
    public function setId($id) { $this->orderStatusHistoryId = $id; }

    /**
     * Set the order id.
     *
     * @param int orderId The order id.
     */
    public function setOrderId($orderId) { $this->orderId = $orderId; }

    /**
     * Set the order status id.
     *
     * @param int orderStatusId The order status id.
     */
    public function setOrderStatusId($orderStatusId) { $this->orderStatusId = $orderStatusId; }

    /**
     * Set the order status name.
     *
     * @param string name The order status name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the date it was added.
     *
     * @param string dateAdded The date the status was changed.
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set whether the customer been notified about this change.
     *
     * @param boolean customerNotified <code>true</code> if the customer has been notified, <code>false</code> if not.
     */
    public function setCustomerNotified($customerNotified) { $this->customerNotified = $customerNotified; }

    /**
     * Set the comment.
     *
     * @param string comment The comment.
     */
    public function setComment($comment) { $this->comment = $comment; }

}
