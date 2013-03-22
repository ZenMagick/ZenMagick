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
namespace ZenMagick\StoreBundle\Model\Checkout;

use ZenMagick\Base\ZMObject;

/**
 * A single shopping cart item.
 *
 * <p>This class can either be populated using the c'tor argument (zen-cart cart info) or
 * implicit as done by the shopping cart service (<code>ShoppingCart</code>)</p>
 *
 * @author DerManoMann
 */
class ShoppingCartItem extends ZMObject
{
    private $shoppingCart;
    private $id;
    private $quantity;
    private $itemPrice;
    private $oneTimeCharge;
    private $attributes;


    /**
     * Create new shopping cart item
     *
     * @param ShoppingCart shoppingCart The cart this item belongs to.
     * @todo store upload id in attribute value id
     */
    public function __construct(ShoppingCart $shoppingCart=null)
    {
        parent::__construct();
        $this->shoppingCart = $shoppingCart;
        $this->id = null;
        $this->quantity = 0;
        $this->itemPrice = 0;
        $this->oneTimeCharge = 0;
    }


    /**
     * Set the shopping cart this items belongs to.
     *
     * @param ShoppingCart shoppingCart The cart this item belongs to.
     */
    public function setShoppingCart(ShoppingCart $shoppingCart)
    {
        $this->shoppingCart = $shoppingCart;
    }

    /**
     * Populate attributes for this cart item.
     *
     * @param array attributeData item attribute data.
     */
    public function populateAttributes($attributeData)
    {
        if (!isset($attributeData['attributes']) || !is_array($attributeData['attributes'])) {
            $this->setAttributes(array());

            return;
        }

        // build attribute => value list map
        $attrMap = array();
        foreach ($attributeData['attributes'] as $option => $value) {
            if (!empty($value)) {
                $tmp = explode('_', $option);
                $attributeId = $tmp[0];
                if (!array_key_exists($attributeId, $attrMap)) {
                    $attrMap[$attributeId] = array();
                }
                if (is_array($value)) {
                    $attrMap[$attributeId] = $value;
                } else {
                    $attrMap[$attributeId][$value] = $value;
                }
            }
        }
        // also add values
        foreach ($attributeData['attributes_values'] as $option => $valueId) {
            if (!empty($valueId)) {
                $tmp = explode('_', $option);
                $attributeId = $tmp[0];
                if (!array_key_exists($attributeId, $attrMap)) {
                    $attrMap[$attributeId] = array();
                }
                // text/upload only ever have one value with id 0 and value as [text]/[{uploadId}. filename]
                $attrMap[$attributeId][0] = $valueId;
            }
        }

        // now get all attributes and strip the not selected stuff
        $product = $this->getProduct();
        if (null != $product) {
            $productAttributes = $product->getAttributes();
            $attributes = array();
            foreach ($productAttributes as $productAttribute) {
                // drop optional attributes and unselected values
                if (array_key_exists($productAttribute->getId(), $attrMap)) {
                    $attribute = clone $productAttribute;
                    $attribute->clearValues();
                    $valueIds = $attrMap[$productAttribute->getId()];
                    foreach ($productAttribute->getValues() as $value) {
                        if (array_key_exists($value->getId(), $valueIds)) {
                            $tmp = clone $value;
                            if (in_array($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_TEXT, PRODUCTS_OPTIONS_TYPE_FILE))) {
                                // these should have only a single value
                                $tmp->setName($valueIds[0]);
                            }
                            $attribute->addValue($tmp);
                        }
                    }
                    $attributes[] = $attribute;
                }
            }
        }

        // keep copy
        $this->attributes = $attributes;

        return $attributes;
    }


    /**
     * Get the tax rate for this item.
     *
     * @param ZenMagick\StoreBundle\Entity\Address address Optional tax address; default is <code>null</code> to default to the shopping cart tax address.
     * @return TaxRate The tax rate or <code>null</code>.
     */
    public function getTaxRate($address=null)
    {
        $address = null != $address ? $address : $this->shoppingCart->getTaxAddress();
        $countryId = null != $address ? $address->getCountryId() : 0;
        $zoneId = null != $address ? $address->getZoneId() : 0;

        return $this->container->get('taxService')->getTaxRateForClassId($this->getProduct()->getTaxClassId(), $countryId, $zoneId);
    }

    /**
     * Get all tax rates for this item.
     *
     * @param ZenMagick\StoreBundle\Entity\Address address Optional tax address; default is <code>null</code> to default to the shopping cart tax address.
     * @return array List of <code>TaxRate</code> instances.
     */
    public function getTaxRates($address=null)
    {
        $address = null != $address ? $address : $this->shoppingCart->getTaxAddress();
        $countryId = null != $address ? $address->getCountryId() : 0;
        $zoneId = null != $address ? $address->getZoneId() : 0;

        return $this->container->get('taxService')->getTaxRatesForClassId($this->getProduct()->getTaxClassId(), $countryId, $zoneId);
    }

    /**
     * Get the cart item id (the sku).
     *
     * <p>This will differ from the product id if attributes are attached to the cart item.</p>
     *
     * @return string The product/sku id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the cart item id.
     *
     * @param string id The product/sku id.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the plain product id of this item.
     *
     * <p>For items without attributes this is going to be the same as <code>getId()</code>. If attributes are present,
      to use the store address.l return something like a skuId while this method will always return the plain
     * product id without any attribute hash.</p>
     *
     * @return int The product id.
     */
    public function getProductId()
    {
        return ShoppingCart::getBaseProductIdFor($this->getId());
    }

    /**
     * Get the number of items in the cart.
     *
     * @return int The cart quantity.
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set the quantity for this item.
     *
     * @param int quantity The cart quantity.
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Get the product this item is associated with.
     *
     * @return ZenMagick\StoreBundle\Entity\Catalog\Product The product.
     */
    public function getProduct()
    {
        return $this->container->get('productService')->getProductForId($this->getProductId());
    }

    /**
     * Get the weight for this item.
     *
     * @return float The full weight.
     */
    public function getWeight()
    {
        $weight = (float) $this->getProduct()->getWeight();
        foreach ($this->getAttributes() as $attribute) {
            foreach ($attribute->getValues() as $value) {
                $multi = '-' == $value->getWeightPrefix() ? -1 : 1;
                $weight += $multi * $value->getWeight();
            }
        }

        return $weight;
    }

    /**
     * Check if this cart item has attributes or not.
     *
     * @return boolean <code>true</code> if there are attributes (values) available,
     *  <code>false</code> if not.
     */
    public function hasAttributes()
    {
        return 0 != $this->getAttributes();
    }

    /**
     * Get the item/line total.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @return float The price for a single item.
     */
    public function getItemTotal($tax=true)
    {
        $total = $this->getItemPrice(false) * $this->getQuantity();

        return $tax ? $this->getTaxRate()->addTax($total) : $total;
    }

    /**
     * Check stock availability for the current quantity.
     *
     * @return boolean <code>true</code> if sufficient stock is available, <code>false</code> if not.
     */
    public function isStockAvailable()
    {
        return $this->container->get('productService')->isQuantityAvailable($this->getId(), $this->getQuantity());
    }

    /**
     * Get the item price.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @return float The price for a single item.
     */
    public function getItemPrice($tax=true)
    {
        return $tax ? $this->getTaxRate()->addTax($this->itemPrice) : $this->itemPrice;
    }

    /**
     * Set the item price.
     *
     * @param float itemPrice The price for a single item (excl. tax).
     */
    public function setItemPrice($itemPrice)
    {
        $this->itemPrice = $itemPrice;
    }

    /**
     * Check if this item has a one time charge attached.
     *
     * @return boolean <code>true</code> if a one time charge is set.
     */
    public function hasOneTimeCharge()
    {
        // don't need tax for this
        return 0 != $this->getOneTimeCharge(false);
    }

    /**
     * Get optional one time charges for this item.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @return float The amount.
     */
    public function getOneTimeCharge($tax=true)
    {
        return $tax ? $this->getTaxRate()->addTax($this->oneTimeCharge) : $this->oneTimeCharge;
    }

    /**
     * Set optional one time charges for this item.
     *
     * @param float amount The amount.
     */
    public function setOneTimeCharge($amount)
    {
        $this->oneTimeCharge = $amount;
    }

    /**
     * Set selected attributes for this cart item.
     *
     * @param array attributes List of product attributes.
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get selected attributes for this cart item.
     *
     * @return array List of product attributes.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get the (discounted) attribute price.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @param int quantity Optional quantity; default is <code>null</code> to use the quantity set on the item.
     * @return float The attributes value.
     */
    public function getAttributesPrice($tax=true, $quantity=null)
    {
        $price = 0;
        $quantity = null === $quantity ? $this->quantity : $quantity;
        $productIsFree = $this->getProduct()->isFree();
        foreach ($this->attributes as $attribute) {
            foreach ($attribute->getValues() as $value) {
                if ($productIsFree && $value->isFree()) {
                    // value is only free if product is free
                    continue;
                }
                // for text attributes, the name is the text entered by the customer
                $valueValue = PRODUCTS_OPTIONS_TYPE_TEXT == $attribute->getType() ? $value->getName() : null;
                $price += $value->getPrice($tax, $quantity, $valueValue);
            }
        }

        return $price;
    }

    /**
     * Get the attribute one time price.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @param int quantity Optional quantity; default is <code>null</code> to use the quantity set on the item.
     * @return float The attributes value.
     */
    public function getAttributesOneTimePrice($tax=true, $quantity=null)
    {
        $price = 0;
        $quantity = null === $quantity ? $this->quantity : $quantity;
        foreach ($this->attributes as $attribute) {
            foreach ($attribute->getValues() as $value) {
                if ($value->isFree()) { continue; }
                $price += $value->getOneTimePrice($tax, $quantity);
            }
        }

        return $price;
    }

}
