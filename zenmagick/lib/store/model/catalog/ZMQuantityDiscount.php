<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * A quantity discount.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model.catalog
 * @version $Id$
 */
class ZMQuantityDiscount extends ZMObject {
    private $productId_;
    private $quantity_;
    private $value_;
    private $price_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->setId(0);
        $this->productId_ = 0;
        $this->quantity_ = 0;
        $this->value_ = 0;
        $this->price_ = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the discount id.
     *
     * @return int The discount id.
     */
    public function getId() { return $this->get('quantityDiscountId'); }

    /**
     * Get the product id.
     *
     * @return int The product id.
     */
    public function getProductId() { return $this->productId_; }

    /**
     * Get the upper quantity [excl.].
     *
     * @return int The upper quantity.
     */
    public function getQuantity() { return $this->quantity_; }

    /**
     * Get the discount value (amount, percent, etc).
     *
     * @return float The discount value.
     */
    public function getValue() { return $this->value_; }

    /**
     * Get the calculated discount price.
     *
     * @return float The discounted price.
     */
    public function getPrice() { return $this->price_; }

    /**
     * Set the discount id.
     *
     * @param int id The discount id.
     */
    public function setId($id) { return $this->set('quantityDiscountId', $id); }

    /**
     * Set the product id.
     *
     * @param int productId The product id.
     */
    public function setProductId($productId) { $this->productId_ = $productId; }

    /**
     * Set the upper quantity [excl.].
     *
     * @param int quantity The upper quantity.
     */
    public function setQuantity($quantity) { $this->quantity_ = $quantity; }

    /**
     * Set the discount value (amount, percent, etc).
     *
     * @param float value The discount value.
     */
    public function setValue($value) { $this->value_ = $value; }

    /**
     * Set the calculated discount price.
     *
     * @param float price The discounted price.
     */
    public function setPrice($price) { $this->price_ = $price; }

}

?>
