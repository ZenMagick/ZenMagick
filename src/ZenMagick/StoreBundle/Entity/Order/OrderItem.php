<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

namespace ZenMagick\StoreBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;

/**
 * A single order item
 *
 * @ORM\Table(name="orders_products",
 *  indexes={
 *      @ORM\Index(name="idx_orders_id_prod_id_zen", columns={"orders_id", "products_id"}),
 *      @ORM\Index(name="idx_prod_id_orders_id_zen", columns={"products_id", "orders_id"}),
 *  })
 * @ORM\Entity
 * @author DerManoMann
 */
class OrderItem extends ZMObject
{
    /**
     * @var integer $orderItemId
     *
     * @ORM\Column(name="orders_products_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $orderItemId;

    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="orders_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var integer $productId
     *
     * @ORM\Column(name="products_id", type="integer", nullable=false)
     */
    private $productId;

    /**
     * @var string $model
     *
     * @ORM\Column(name="products_model", type="string", length=32, nullable=true)
     */
    private $model;

    /**
     * @var string $name
     *
     * @ORM\Column(name="products_name", type="string", length=64, nullable=false)
     */
    private $name;

    /**
     * @var float $productPrice
     *
     * @ORM\Column(name="products_price", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $productPrice;

    /**
     * @var float $calculatedPrice
     *
     * @ORM\Column(name="final_price", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $calculatedPrice;

    /**
     * @var float $taxValue
     *
     * @ORM\Column(name="products_tax", type="decimal", precision=7, scale=4, nullable=false)
     */
    private $taxValue;

    /**
     * @var float $qty
     *
     * @ORM\Column(name="products_quantity", type="float", nullable=false)
     */
    private $qty;

   /**
     * @var float $oneTimeCharges
     *
     * @ORM\Column(name="onetime_charges", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $oneTimeCharges;

    /**
     * @var boolean $pricedByAttribute
     *
     * @ORM\Column(name="products_priced_by_attribute", type="boolean", nullable=false)
     */
    private $pricedByAttribute;

    /**
     * @var boolean $free
     *
     * @ORM\Column(name="product_is_free", type="boolean", nullable=false)
     */
    private $free;

    /**
     * @var boolean $discountType
     *
     * @ORM\Column(name="products_discount_type", type="smallint", nullable=false)
     */
    private $discountType;

    /**
     * @var boolean $discountTypeFrom
     *
     * @ORM\Column(name="products_discount_type_from", type="smallint", nullable=false)
     */
    private $discountTypeFrom;

    /**
     * @var string $sku
     *
     * @ORM\Column(name="products_prid", type="text", nullable=false)
     */
    private $sku;

    private $taxRate;
    private $attributes;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->taxRate = null;
        $this->attributes = array();
    }

    /**
     * Get the order item id.
     *
     * @return int The order item id.
     */
    public function getId()
    {
        return $this->orderItemId;
    }

    /**
     * Get the order item product id.
     *
     * @return int The order item product id.
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Get the product this item is associated to.
     *
     * @return ZenMagick\StoreBundle\Entity\Product The product.
     */
    public function getProduct()
    {
        return $this->container->get('productService')->getProductForId($this->getProductId());
    }

    /**
     * Get the quantity.
     *
     * @return int The quantity for this item.
     */
    public function getQuantity()
    {
        return $this->qty;
    }

    /**
     * Get the item name.
     *
     * @return string The item name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the model.
     *
     * @return string The item model.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the tax rate.
     *
     * @return float The tax rate.
     */
    public function getTaxRate()
    {
        if (null == $this->taxRate) {
            $this->taxRate = Beans::getBean('ZenMagick\StoreBundle\Entity\TaxRate');
            $this->taxRate->setRate($this->get('taxValue'));
        }

        return $this->taxRate;
    }

    /**
     * Get the calculated price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return float The calculated price.
     */
    public function getCalculatedPrice($tax=true)
    {
        return $tax ? $this->getTaxRate()->addTax($this->calculatedPrice) : $this->calculatedPrice;
    }

    /**
     * Checks if the item has associated attributes.
     *
     * @return boolean </code>true</code> if attributes exist, <code>false</code> if not.
     */
    public function hasAttributes()
    {
        return 0 < count($this->attributes);
    }

    /**
     * Get the item attributes.
     *
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Attribute</code> instances.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the order item id.
     *
     * @param int id The order item id.
     */
    public function setId($id)
    {
        $this->orderItemId = $id;

        return $this;
    }

    /**
     * Set the order item product id.
     *
     * @param int productId The order item product id.
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Set the quantity.
     *
     * @param int qty The quantity for this item.
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Set the item name.
     *
     * @param string name The item name.
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the model.
     *
     * @param string model The item model.
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the tax rate.
     *
     * @param float taxRate The tax rate.
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Set the calculated price.
     *
     * @param float price The calculated price.
     */
    public function setCalculatedPrice($price)
    {
        $this->calculatedPrice = $price;

        return $this;
    }

    /**
     * Add an item attribute.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Attribute attribute A <code>ZenMagick\StoreBundle\Entity\Catalog\Attribute</code>.
     */
    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
    }

    /**
     * Set item attributes.
     *
     * @param array attributes A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Attribute</code> instances.
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

}
