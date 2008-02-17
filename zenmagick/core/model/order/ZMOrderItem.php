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
 * A single order item
 *
 * @author mano
 * @package org.zenmagick.model.order
 * @version $Id$
 */
class ZMOrderItem extends ZMModel {
    var $productId_;
    var $qty_;
    var $name_;
    var $model_;
    var $taxRate_;
    var $calculatedPrice_;
    var $attributes_;


    /**
     * Default c'tor.
     */
    function ZMOrderItem() {
        parent::__construct();

        $this->attributes_ = array();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMOrderItem();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the order item id.
     *
     * @return int The order item id.
     */
    function getProductId() { return $this->productId_; }

    /**
     * Get the product this item is associated to.
     *
     * @return ZMProduct The product.
     */
    function getProduct() {
    global $zm_products;

        return $zm_products->getProductForId($this->getProductId());
    }

    /**
     * Get the quantity.
     *
     * @return int The quantity for this item.
     */
    function getQty() { return $this->qty_; }

    /**
     * Get the item name.
     *
     * @return string The item name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the model.
     *
     * @return string The item model.
     */
    function getModel() { return $this->model_; }

    /**
     * Get the tax rate.
     *
     * @return float The tax rate.
     */
    function getTaxRate() { return $this->taxRate_; }

    /**
     * Get the calculated price.
     *
     * @return float The calculated price.
     */
    function getCalculatedPrice() { return $this->calculatedPrice_; }

    /**
     * Checks if the item has associated attributes.
     *
     * @return boolean </code>true</code> if attributes exist, <code>false</code> if not.
     */
    function hasAttributes() { return 0 < count($this->attributes_); }

    /**
     * Get the item attributes.
     *
     * @return array A list of <code>ZMAttribute</code> instances.
     */
    function getAttributes() { return $this->attributes_; }

}

?>
