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
 * A single coupon.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMCoupon {
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


    // create new instance
    function ZMCoupon($id, $code, $type) {
		    $this->id_ = $id;
		    $this->code_ = $code;
		    $this->type_ = $type;
    }

    // create new instance
    function __construct($id, $code, $type) {
        $this->ZMCoupon($id, $code, $type);
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getCode() { return $this->code_; }
    function getType() { return $this->type_; }
    function getAmount() { return $this->amount_; }
    function getName() { return $this->name_; }
    function getDescription() { return $this->description_; }
    function getMinimumOrder() { return $this->minimumOrder_; }
    function getStartDate() { return $this->startDate_; }
    function getExpiryDate() { return $this->expiryDate_; }
    function getUsesPerCoupon() { return $this->usesPerCoupon_; }
    function getUsesPerUser() { return $this->usesPerUser_; }
    function isFreeShipping() { return 'S' == $this->type_; }
    function isFixedAmount() { return 'F' == $this->type_; }
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
