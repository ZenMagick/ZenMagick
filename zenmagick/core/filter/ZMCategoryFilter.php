<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Protions Copyright (c) 2003 The zen-cart developers
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
 * Filter products by category.
 *
 * @author mano
 * @package net.radebatz.zenmagick.filter
 * @version $Id$
 */
class ZMCategoryFilter { //extends ZMFilter {
    var $categoryId_;
    var $productIds_;


    // create new instance
    function ZMCategoryFilter() {
    global $zm_request;
        //parent::__construct();

        $this->categoryId_ = null;
        if (null == $zm_request->getCategoryId()) {
            $this->categoryId_ = $zm_request->getFilterId();
        }
    }

    // create new instance
    function __construct() {
        $this->ZMCategoryFilter();
    }

    function __destruct() {
    }


    // lazy load
    function _getProductIds() {
    global $zm_products;
        if (null == $this->productIds_) {
            $this->productIds_ = $zm_products->getProductIdsForCategoryId($this->categoryId_);
        }
        return $this->productIds_;
    }


    // is filter active for this request
    function isActive() { return null != $this->categoryId_; }
    // check for valid product
    function isValid($product) {
        $productIds = $this->_getProductIds();
        return array_key_exists($product->getId(), $productIds);
    }

}

?>
