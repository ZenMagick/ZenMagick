<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * A single coupon.
 *
 * <p><strong>NOTE:</strong> Depending on the coupon type, not all values might
 * be set.</p>
 * <p>For example, gift vouchers do only have a <em>code</em> and <em>amount</em>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model
 * @version $Id: ZMCoupon.php 2166 2009-04-17 03:26:23Z dermanomann $
 */
class ZMCoupon extends ZMObject {
    private $code_;
    private $type_;
    private $amount_;
    private $name_;
    private $description_;
    private $minOrderAmount_;
    private $startDate_;
    private $expiryDate_;
    private $usesPerCoupon_;
    private $usesPerUser_;
    private $active_;


    /**
     * Create new instance
     *
     * @param int id The coupon id; default is <em>0</em>.
     * @param string code The coupon code; default is <em>''</em>.
     * @param string type The coupon type; default is <em>''</em>.
     */
    function __construct($id=0, $code='', $type='') {
        parent::__construct();
        $this->setId($id);
        $this->code_ = $code;
        $this->type_ = $type;
        $this->active_ = 'Y';
        $this->minOrderAmount_ = 0;
        $this->usesPerCoupon_ = 1;
        $this->usesPerUser_ = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the coupon id.
     *
     * @return int The coupon id.
     */
    public function getId() { return $this->get('couponId'); }

    /**
     * Get the coupon code.
     *
     * @return string The coupon code.
     */
    public function getCode() { return $this->code_; }

    /**
     * Get the coupon type.
     *
     * @return string The coupon type.
     */
    public function getType() { return $this->type_; }

    /**
     * Get the amount.
     *
     * @return float The coupon amount.
     */
    public function getAmount() { return $this->amount_; }

    /**
     * Get the coupon name.
     *
     * @return string The coupon name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the coupon description.
     *
     * @return string The coupon description.
     */
    public function getDescription() { return $this->description_; }

    /**
     * Get the minimum order amount.
     *
     * @return float The minimum order amount.
     */
    public function getMinOrderAmount() { return $this->minOrderAmount_; }

    /**
     * Get the coupon start date.
     *
     * @return string The coupon start date.
     */
    public function getStartDate() { return $this->startDate_; }

    /**
     * Get the coupon expiry date.
     *
     * @return string The coupon expiry date.
     */
    public function getExpiryDate() { return $this->expiryDate_; }

    /**
     * Get the uses per coupon.
     *
     * @return int The uses per coupon.
     */
    public function getUsesPerCoupon() { return $this->usesPerCoupon_; }

    /**
     * Get the uses per coupon.
     *
     * @return int The uses per coupon.
     */
    public function getUsesPerUser() { return $this->usesPerUser_; }

    /**
     * Check if this coupon qualifies for free shipping.
     *
     * @return boolean <code>true</code> if this coupon qualifies for free shipping, <code>false</code> if not.
     */
    public function isFreeShipping() { return 'S' == $this->type_; }

    /**
     * Check if this coupon is active.
     *
     * @return boolean <code>true</code> if this coupon is active.
     */
    public function isActive() { return 'Y' == $this->active_; }

    /**
     * Check if this a fixed amount coupon.
     *
     * @return boolean <code>true</code> if this coupon has a fixed amount assigned, <code>false</code> if not.
     */
    public function isFixedAmount() { return 'F' == $this->type_; }

    /**
     * Check if this a percentage amount coupon.
     *
     * @return boolean <code>true</code> if this coupon has a percentage amount assigned, <code>false</code> if not.
     */
    public function isPercentage() { return 'P' == $this->type_; }

    /**
     * Get coupon restrictions.
     *
     * @return array An array of <code>ZMCouponRestriction</code> instances.
     */
    public function getRestrictions() {
        return ZMCoupons::instance()->getRestrictionsForCouponId($this->get('couponId'));
    }

    /**
     * Set the coupon id.
     *
     * @param int id The coupon id.
     */
    public function setId($id) { $this->set('couponId', $id); }

    /**
     * Set the coupon code.
     *
     * @param string code The coupon code.
     */
    public function setCode($code) { $this->code_ = $code; }

    /**
     * Set the coupon type.
     *
     * @param string type The coupon type.
     */
    public function setType($type) { $this->type_ = $type; }

    /**
     * Set the amount.
     *
     * @param float amount The coupon amount.
     */
    public function setAmount($amount) { $this->amount_ = $amount; }

    /**
     * Set the coupon name.
     *
     * @param string name The coupon name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the coupon description.
     *
     * @param string description The coupon description.
     */
    public function setDescription($description) { $this->description_ = $description; }

    /**
     * Set the minimum order amount.
     *
     * @param float amount The new minimum order amount.
     */
    public function setMinOrderAmount($amount) { $this->minOrderAmount_ = $amount; }

    /**
     * Set the coupon start date.
     *
     * @param string date The coupon start date.
     */
    public function setStartDate($date) { $this->startDate_ = $date; }

    /**
     * Set the coupon expiry date.
     *
     * @param string date The coupon expiry date.
     */
    public function setExpiryDate($date) { $this->expiryDate_ = $date; }

    /**
     * Set the uses per coupon.
     *
     * @param int uses The uses per coupon.
     */
    public function setUsesPerCoupon($uses) { $this->usesPerCoupon_ = $uses; }

    /**
     * Set the uses per user.
     *
     * @param int uses The uses per user.
     */
    public function setUsesPerUser($uses) { $this->usesPerUser_ = $uses; }

    /**
     * Set the active flag.
     *
     * @param string active The new flag.
     */
    public function setActive($active) { $this->active_ = $active; }

}

?>
