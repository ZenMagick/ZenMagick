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
 * Single coupon restriction.
 *
 * @author DerManoMann
 * @package org.zenmagick.model.checkout.coupons
 * @version $Id: ZMProductCouponRestriction.php 954 2008-03-29 10:12:29Z DerManoMann $
 */
class ZMProductCouponRestriction extends ZMModel {
    var $allowed_;
    var $productId_;


    /**
     * Create new coupon restriction.
     *
     * @param boolean allowed The allowed flag.
     * @param int productId The product id this restriction applies to.
     */
    function __construct($allowed, $productId) {
        parent::__construct();
        $this->allowed_ = $allowed;
        $this->productId_ = $productId;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if this coupon restriction is allowed.
     *
     * @return boolean <code>true</code> if this coupon restriction is allowed, <code>false</code> if not.
     */
    function isAllowed() { return $this->allowed_; }

    /**
     * Returns the product.
     *
     * @return A <code>ZMProduct</code> instance.
     */
    function getProduct() { return ZMProducts::instance()->getProductForId($this->productId_); }

}

?>
