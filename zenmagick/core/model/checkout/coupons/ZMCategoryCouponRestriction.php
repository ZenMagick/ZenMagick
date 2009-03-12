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
 * @version $Id: ZMCategoryCouponRestriction.php 954 2008-03-29 10:12:29Z DerManoMann $
 */
class ZMCategoryCouponRestriction extends ZMObject {
    var $allowed_;
    var $categoryId_;


    /**
     * Create new instance.
     */
    function __construct($allowed, $categoryId) {
        parent::__construct();
        $this->allowed_ = $allowed;
        $this->categoryId_ = $categoryId;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if allowed.
     *
     * @return boolean <code>true</code> if allowed, <code>false</code> if not.
     */
    function isAllowed() { return $this->allowed_; }

    /**
     * Returns the category.
     *
     * @return A <code>ZMCategory</code> instance.
     */
    function getCategory() { 
        return ZMCategories::instance()->getCategoryForId($this->categoryId_);
    }

}

?>
