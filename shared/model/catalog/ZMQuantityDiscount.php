<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * A quantity discount.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.catalog
 * @ORM\Table(name="products_discount_quantity")
 * @ORM\Entity
 */
class ZMQuantityDiscount extends ZMObject {
    /**
     * @var integer $productId
     *
     * @ORM\Column(name="products_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $productId;
    /**
     * @var integer $quantityDiscountId
     *
     * @ORM\Column(name="discount_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $quantityDiscountId;
    /**
     * @var float $quantity
     *
     * @ORM\Column(name="discount_qty", type="float", nullable=false)
     */
    private $quantity;
    /**
     * @var decimal $value
     *
     * @ORM\Column(name="discount_price", type="decimal", nullable=false)
     */
    private $value;
    private $price;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->setId(0);
        $this->productId = 0;
        $this->quantity = 0;
        $this->value = 0;
        $this->price = 0;
    }


    /**
     * Get the discount id.
     *
     * @todo doctrine this id is NOT UNIQUE
     * @return int $quantityDiscountIdThe discount id.
     */
    public function getId() { return $this->quantityDiscountId; }

    /**
     * Get the product id.
     *
     * @return integer $productId The product id.
     */
    public function getProductId() { return $this->productId; }

    /**
    * Get the upper quantity [excl.].
     *
     * @return integer $quantity The upper quantity.
     */
    public function getQuantity() { return $this->quantity; }

    /**
     * Get the discount value (amount, percent, etc).
     *
     * @return float The discount value.
     */
    public function getValue() { return $this->value; }

    /**
     * Get the calculated discount price.
     *
     * @return float $price The discounted price.
     */
    public function getPrice() { return $this->price; }

    /**
     * Set the discount id.
     *
     * @param int id The discount id.
     */
    public function setId($id) { return $this->quantityDiscountId = $id; }

    /**
     * Set the product id.
     *
     * @param integer $productId The product id.
     */
    public function setProductId($productId) { $this->productId = $productId; }

    /**
     * Set the upper quantity [excl.].
     *
     * @param float $quantity The upper quantity.
     */
    public function setQuantity($quantity) { $this->quantity = $quantity; }

    /**
     * Set the discount value (amount, percent, etc).
     *
     * @param float value The discount value.
     */
    public function setValue($value) { $this->value = $value; }

    /**
     * Set the calculated discount price.
     *
     * @param float $price The discounted price.
     */
    public function setPrice($price) { $this->price = $price; }
}
