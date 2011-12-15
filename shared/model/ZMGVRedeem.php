<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\ZMObject;

/**
 * Gift voucher redeem info.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMGVRedeem extends ZMObject {
    private $couponCode_;
    private $amount_;
    private $redeemed_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->couponCode_ = null;
        $this->amount_ = 0;
        $this->redeemed_ = false;
    }


    /**
     * Get the coupon code.
     *
     * @return string The coupon code.
     */
    public function getCouponCode() { return $this->couponCode_; }

    /**
     * Get the amount.
     *
     * @return float The amount.
     */
    public function getAmount() { return $this->amount_; }

    /**
     * Check if the coupon was redeemed succsessfully.
     *
     * @return boolean <code>true</code> if the coupon was redeemed succsessfully, <code>false</code> if not.
     */
    public function isRedeemed() { return $this->redeemed_; }

    /**
     * Set the coupon code.
     *
     * @param string couponCode The coupon code.
     */
    public function setCouponCode($couponCode) { $this->couponCode_ = $couponCode; }

    /**
     * Set the amount.
     *
     * @param float amount The amount.
     */
    public function setAmount($amount) { $this->amount_ = $amount; }

    /**
     * Set the redeem flag.
     *
     * @param boolean redeemed <code>true</code> if the coupon was redeemed succsessfully, <code>false</code> if not.
     */
    public function setRedeemed($redeemed) { $this->redeemed_ = $redeemed; }

}
