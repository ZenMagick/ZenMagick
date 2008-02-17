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
 * A single coupon.
 *
 * <p><strong>NOTE:</strong> Depending on the coupon type, not all values might
 * be set.</p>
 * <p>For example, gift vouchers do only have a <em>code</em> and <em>amount</em>.</p>
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMCoupon extends ZMModel {
    var $id_;
    var $code_;
    var $type_;
    var $amount_;
    var $name_;
    var $description_;
    var $minimumOrder_;
    var $startDate_;
    var $expiryDate_;
    var $usesPerCoupon_;
    var $usesPerUser_;


    /**
     * Create new instance
     *
     * @param int id The coupon id.
     * @param string code The coupon code.
     * @param string type The coupon type; default is blank.
     */
    function ZMCoupon($id, $code, $type='') {
        parent::__construct();

		    $this->id_ = $id;
		    $this->code_ = $code;
		    $this->type_ = $type;
    }

    /**
     * Create new instance
     *
     * @param int id The coupon id.
     * @param string code The coupon code.
     * @param string type The coupon type; default is blank.
     */
    function __construct($id, $code, $type='') {
        $this->ZMCoupon($id, $code, $type);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the coupon id.
     *
     * @return int The coupon id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the coupon code.
     *
     * @return string The coupon code.
     */
    function getCode() { return $this->code_; }

    /**
     * Get the coupon type.
     *
     * @return string The coupon type.
     */
    function getType() { return $this->type_; }

    /**
     * Get the amount.
     *
     * @return float The coupon amount.
     */
    function getAmount() { return $this->amount_; }

    /**
     * Get the coupon name.
     *
     * @return string The coupon name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the coupon description.
     *
     * @return string The coupon description.
     */
    function getDescription() { return $this->description_; }

    /**
     * Get the minimum order value.
     *
     * @return float The minimum order value.
     */
    function getMinimumOrder() { return $this->minimumOrder_; }

    /**
     * Get the coupon start date.
     *
     * @return string The coupon start date.
     */
    function getStartDate() { return $this->startDate_; }

    /**
     * Get the coupon expiry date.
     *
     * @return string The coupon expiry date.
     */
    function getExpiryDate() { return $this->expiryDate_; }

    /**
     * Get the uses per coupon.
     *
     * @return int The uses per coupon.
     */
    function getUsesPerCoupon() { return $this->usesPerCoupon_; }

    /**
     * Get the uses per coupon.
     *
     * @return int The uses per coupon.
     */
    function getUsesPerUser() { return $this->usesPerUser_; }

    /**
     * Check if this coupon qualifies for free shipping.
     *
     * @return boolean <code>true</code> if this coupon qualifies for free shipping, <code>false</code> if not.
     */
    function isFreeShipping() { return 'S' == $this->type_; }

    /**
     * Check if this a fixed amount coupon.
     *
     * @return boolean <code>true</code> if this coupon has a fixed amount assigned, <code>false</code> if not.
     */
    function isFixedAmount() { return 'F' == $this->type_; }

    /**
     * Check if this a percentage amount coupon.
     *
     * @return boolean <code>true</code> if this coupon has a percentage amount assigned, <code>false</code> if not.
     */
    function isPercentage() { return 'P' == $this->type_; }

    /**
     * Get coupon restrictions.
     *
     * @return array An array of <code>ZMCouponRestriction</code> instances.
     */
    function getRestrictions() {
    global $zm_coupons;

        return $zm_coupons->_getRestrictionsForId($this->id_);
    }

}

?>
