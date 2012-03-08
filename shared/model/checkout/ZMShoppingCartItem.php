<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A single shopping cart item.
 *
 * <p>This class can either be populated using the c'tor argument (zen-cart cart info) or
 * implicit as done by the shopping cart service (<code>ZMShoppingCart</code>)</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 * @todo remove deprecated code and zenItem references
 */
class ZMShoppingCartItem extends ZMObject {
    private $shoppingCart;
    private $id_;
    private $quantity_;
    private $itemPrice_;
    private $oneTimeCharge_;
    private $attributes_;


    /**
     * Create new shopping cart item
     *
     * @param ZMShoppingCart shoppingCart The cart this item belongs to.
     * @param array zenItem The zen-cart shopping item infos.
     */
    public function __construct(ZMShoppingCart $shoppingCart=null, $zenItem=null) {
        parent::__construct();
        $this->shoppingCart = $shoppingCart;
        $this->id_ = null;
        $this->quantity_ = 0;
        $this->itemPrice_ = 0;
        $this->oneTimeCharge_ = 0;
        $this->setContainer(Runtime::getContainer());
        if (null !== $zenItem) {
            $this->setId($zenItem['id']);
            $this->setQuantity($zenItem['quantity']);
            $this->setItemPrice($zenItem['final_price']);
            $this->setOneTimeCharge($zenItem['onetime_charges']);
            $this->populateAttributes($zenItem);
        }
    }


    /**
     * Set the shopping cart this items belongs to.
     *
     * @param ZMShoppingCart shoppingCart The cart this item belongs to.
     */
    public function setShoppingCart(ZMShoppingCart $shoppingCart) {
        $this->shoppingCart = $shoppingCart;
    }

    // @deprecated
    function getName() { return $this->getProduct()->getName(); }
    // @deprecated
    function getImage() { return $this->getProduct()->getImage(); }
    // @deprecated
    function getImageInfo() { return $this->getProduct()->getImageInfo(); }
    // @deprecated
    function getTaxClassId() { return $this->getProduct()->getTaxClassId(); }
    /**
     * Get selected attributes for this cart item.
     *
     * @param array zenItem zc item data.
     * @deprecated
     */
    private function populateAttributes($zenItem) {
        if (!isset($zenItem['attributes']) || !is_array($zenItem['attributes'])) {
            $this->setAttributes(array());
            return;
        }

        // build attribute => value list map
        $attrMap = array();
        foreach ($zenItem['attributes'] as $option => $valueId) {
            $tmp = explode('_', $option);
            $attributeId = $tmp[0];
            if (!array_key_exists($attributeId, $attrMap)) {
                $attrMap[$attributeId] = array();
            }
            $attrMap[$attributeId][$valueId] = $valueId;
        }

        // values of text/upload attributes
        $textValues = $zenItem['attributes_values'];

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
                                if (0 == $tmp->getId()) {
                                    $tmp->setName($textValues[$attribute->getId()]);
                                }
                            }
                            $attribute->addValue($tmp);
                        }
                    }
                    $attributes[] = $attribute;
                }
            }
        }

        // keep copy
        $this->attributes_ = $attributes;
        return $attributes;
    }


    /**
     * Get the tax rate for this item.
     *
     * @param ZMAddress address Optional tax address; default is <code>null</code> to default to the shopping cart tax address.
     * @return ZMTaxRate The tax rate or <code>null</code>.
     */
    public function getTaxRate($address=null) {
        $address = null != $address ? $address : $this->shoppingCart->getTaxAddress();
        $countryId = null != $address ? $address->getCountryId() : 0;
        $zoneId = null != $address ? $address->getZoneId() : 0;
        return $this->container->get('taxRateService')->getTaxRateForClassId($this->getProduct()->getTaxClassId(), $countryId, $zoneId);
    }

    /**
     * Get all tax rates for this item.
     *
     * @param ZMAddress address Optional tax address; default is <code>null</code> to default to the shopping cart tax address.
     * @return array List of <code>ZMTaxRate</code> instances.
     */
    public function getTaxRates($address=null) {
        $address = null != $address ? $address : $this->shoppingCart->getTaxAddress();
        $countryId = null != $address ? $address->getCountryId() : 0;
        $zoneId = null != $address ? $address->getZoneId() : 0;
        return $this->container->get('taxRateService')->getTaxRatesForClassId($this->getProduct()->getTaxClassId(), $countryId, $zoneId);
    }

    /**
     * Get the cart item id (the sku).
     *
     * <p>This will differ from the product id if attributes are attached to the cart item.</p>
     *
     * @return string The product/sku id.
     */
    public function getId() {
        return $this->id_;
    }

    /**
     * Set the cart item id.
     *
     * @param string id The product/sku id.
     */
    public function setId($id) {
        $this->id_ = $id;
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
    public function getProductId() {
        return ZMShoppingCart::getBaseProductIdFor($this->getId());
    }

    /**
     * Get the number of items in the cart.
     *
     * @return int The cart quantity.
     */
    public function getQuantity() {
        return $this->quantity_;
    }

    /**
     * Set the quantity for this item.
     *
     * @param int quantity The cart quantity.
     */
    public function setQuantity($quantity) {
        $this->quantity_ = $quantity;
    }

    /**
     * Get the product this item is associated with.
     *
     * @return ZMProduct The product.
     */
    public function getProduct() {
        return $this->container->get('productService')->getProductForId($this->getProductId());
    }

    /**
     * Get the weight for this item.
     *
     * @return float The full weight.
     */
    public function getWeight() {
        $weight = (float)$this->getProduct()->getWeight();
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
    public function hasAttributes() {
        return 0 != $this->getAttributes();
    }

    /**
     * Get the item/line total.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @return float The price for a single item.
     * @todo include onetime charge
     */
    public function getItemTotal($tax=true) {
        $total = $this->getItemPrice(false) * $this->getQuantity();
        return $tax ? $this->getTaxRate()->addTax($total) : $total;
    }

    /**
     * Check stock availability for the current quantity.
     *
     * @return boolean <code>true</code> if sufficient stock is available, <code>false</code> if not.
     */
    public function isStockAvailable() {
        return $this->container->get('productService')->isQuantityAvailable($this->getId(), $this->getQuantity());
    }

    /**
     * Get the item price.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @return float The price for a single item.
     */
    public function getItemPrice($tax=true) {
        return $tax ? $this->getTaxRate()->addTax($this->itemPrice_) : $this->itemPrice_;
    }

    /**
     * Set the item price.
     *
     * @param float itemPrice The price for a single item (excl. tax).
     */
    public function setItemPrice($itemPrice) {
        $this->itemPrice_ = $itemPrice;
    }

    /**
     * Check if this item has a one time charge attached.
     *
     * @return boolean <code>true</code> if a one time charge is set.
     */
    public function hasOneTimeCharge() {
        // don't need tax for this
        return 0 != $this->getOneTimeCharge(false);
    }

    /**
     * Get optional one time charges for this item.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @return float The amount.
     */
    public function getOneTimeCharge($tax=true) {
        return $tax ? $this->getTaxRate()->addTax($this->oneTimeCharge_) : $this->oneTimeCharge_;
    }

    /**
     * Set optional one time charges for this item.
     *
     * @param float amount The amount.
     */
    public function setOneTimeCharge($amount) {
        $this->oneTimeCharge_ = $amount;
    }

    /**
     * Set selected attributes for this cart item.
     *
     * @param array attributes List of product attributes.
     */
    public function setAttributes($attributes) {
        $this->attributes_ = $attributes;
    }

    /**
     * Get selected attributes for this cart item.
     *
     * @return array List of product attributes.
     */
    public function getAttributes() {
        return $this->attributes_;
    }

}
