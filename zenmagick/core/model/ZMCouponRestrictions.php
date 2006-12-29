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
 * Collection of coupon restrictions.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMCouponRestrictions {
    var $categories_;
    var $products_;


    /**
     * Default c'tor.
     */
    function ZMCouponRestrictions($categories, $products) {
		    $this->categories_ = $categories;
		    $this->products_ = $products;
    }

    // create new instance
    function __construct($categories, $products) {
        $this->ZMCouponRestrictions($categories, $products);
    }

    function __destruct() {
    }


    // getter/setter
    function hasRestrictions() { return 0 != count($this->categories_) || 0 < count($this->products_); }
    function hasCategories() { return 0 != count($this->categories_); }
    function hasProducts() { return 0 < count($this->products_); }

    /**
     * Returns the category restrictions.
     *
     * @return array An array of <code>ZMCouponRestricton</code> instances.
     */
    function getCategories() { return $this->categories_; }

    /**
     * Returns the product restrictions.
     *
     * @return array An array of <code>ZMCouponRestricton</code> instances.
     */
    function getProducts() { return $this->products_; }

}

?>
