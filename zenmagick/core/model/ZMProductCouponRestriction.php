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
 * Single coupon restriction.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMProductCouponRestriction {
    var $allowed_;
    var $productId_;


    // create new instance
    function ZMProductCouponRestriction($allowed, $productId) {
		    $this->allowed_ = $allowed;
		    $this->productId_ = $productId;
    }

    // create new instance
    function __construct($allowed, $productId) {
        $this->ZMProductCouponRestriction($allowed, $productId);
    }

    function __destruct() {
    }



    // getter/setter
    function isAllowed() { return $this->allowed_; }

    /**
     * Returns the category.
     *
     * @return A <code>ZMProduct</code> instance.
     */
    function getProduct() { global $zm_products; return $zm_products->getProductForId($this->productId_); }

}

?>
