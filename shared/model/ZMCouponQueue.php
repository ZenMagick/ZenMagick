<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * A single coupon queue entry.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 * @Table(name="coupon_gv_queue")
 * @Entity
 */
class ZMCouponQueue extends ZMObject {
    /**
     * @var integer $id
     *
     * @Column(name="unique_id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var integer $accountId
     *
     * @Column(name="customer_id", type="integer", nullable=false)
     */
    private $accountId;
    /**
     * @var integer $orderId
     *
     * @Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;
    /**
     * @var decimal $amount
     *
     * @Column(name="amount", type="decimal", nullable=false)
     */
    private $amount;
    /**
     * @var datetime $dateCreated
     *
     * @Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;
    /**
     * @var string $ipAddr
     *
     * @Column(name="ipaddr", type="string", length=32, nullable=false)
     */
    private $ipAddr;
    /**
     * @var string $released
     *
     * @Column(name="release_flag", type="string", length=1, nullable=false)
     */
    private $released;

    /**
     * Create new instance
     */
    function __construct() {
        parent::__construct();
        $this->id = 0;
        $this->accountId = 0;
        $this->orderId = 0;
        $this->amount = 0;
        $this->released = 'N';
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get the coupon queue id.
     *
     * @return int $id The coupon queue id.
     */
    public function getId() { return $this->id; }

    /**
     * Get the account id.
     *
     * @return int $accountId The account id.
     */
    public function getAccountId() { return $this->accountId; }

    /**
     * Get the order id.
     *
     * @return int $orderId The order id.
     */
    public function getOrderId() { return $this->orderId; }

    /**
     * Get the amount.
     *
     * @return float $amount The coupon amount.
     */
    public function getAmount() { return $this->amount; }

    /**
     * Get the date the coupon was created
     *
     * @return datetime $dateCreated
     */
    public function getDateCreated() { return $this->dateCreated; }

    /**
     * Get ipAddr
     *
     * @return string $ipAddr
     */
    public function getIpAddr() { return $this->ipAddr; }

    /**
     * Get the release flag value.
     *
     * @return string $released The flag.
     */
    public function getReleased() { return $this->released; }

    /**
     * Check if this coupon has been released or not.
     *
     * @return boolean <code>true</code> if already released, <code>false</code> if not.
     */
    public function isReleased() { return 'Y' == $this->released; }

    /**
     * Set the coupon queue id.
     *
     * @param int id The coupon queue id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Set the account id.
     *
     * @param int $accountId The account id.
     */
    public function setAccountId($accountId) { $this->accountId = $accountId; }

    /**
     * Set the order id.
     *
     * @param int $orderId The order id.
     */
    public function setOrderId($orderId) { $this->orderId = $orderId; }

    /**
     * Set the amount.
     *
     * @param float $amount The coupon amount.
     */
    public function setAmount($amount) { $this->amount = $amount; }

    /**
     * Set dateCreated
     *
     * @param datetime $dateCreated
     */
    public function setDateCreated($dateCreated) { $this->dateCreated = $dateCreated; }

    /**
     * Set ipaddr
     *
     * @param string $ipAddr
     */
    public function setIpAddr($ipAddr) { $this->ipAddr = $ipAddr; }

    /**
     * Set the release flag value.
     *
     * @param string $value The flag.
     */
    public function setReleased($value) { $this->released = $value; }
}