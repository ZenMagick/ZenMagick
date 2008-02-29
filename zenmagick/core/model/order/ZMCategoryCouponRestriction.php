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
 * Single coupon restriction.
 *
 * @author mano
 * @package org.zenmagick.model.order
 * @version $Id$
 */
class ZMCategoryCouponRestriction extends ZMModel {
    var $allowed_;
    var $categoryId_;


    /**
     * Create new instance.
     */
    function ZMCategoryCouponRestriction($allowed, $categoryId) {
        parent::__construct();

		    $this->allowed_ = $allowed;
		    $this->categoryId_ = $categoryId;
    }

    /**
     * Create new instance.
     */
    function __construct($allowed, $categoryId) {
        $this->ZMCategoryCouponRestriction($allowed, $categoryId);
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
    function getCategory() { global $zm_categories; return $zm_categories->getCategoryForId($this->categoryId_); }

}

?>
