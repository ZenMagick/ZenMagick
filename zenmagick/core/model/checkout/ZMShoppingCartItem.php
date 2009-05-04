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
 * A single shopping cart item.
 *
 * @author DerManoMann
 * @package org.zenmagick.model.checkout
 * @version $Id$
 */
class ZMShoppingCartItem extends ZMObject {
    private $cart_;
    private $zenItem_;
    private $attributes_;


    /**
     * Create new shopping cart item
     *
     * @param ZMShoppingCart cart The associated shopping cart.
     * @param array zenItem The zen-cart shopping item infos.
     */
    function __construct($cart, $zenItem) {
        parent::__construct();
        $this->cart_ = $cart;
        $this->zenItem_ = $zenItem;
        $this->attributes_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    // getter/setter
    function getId() { return $this->zenItem_['id']; }
    function getName() { return $this->zenItem_['name']; }
    function getImage() { return $this->zenItem_['image']; }
    function getImageInfo() { return ZMLoader::make("ImageInfo", $this->zenItem_['image'], $this->zenItem_['name']); }
    function getQty() { return $this->zenItem_['quantity']; }
    function getItemPrice() { return $this->getTaxRate()->addTax($this->zenItem_['final_price']); }
    // ( item price + attribute price ) * qty
    // item_price: product->getProductPrice() // the base price excl. attributes (as that would include all lowest option attrs as in catalog view)
    // if attribute price - else qty discount price
    // attribute_price = sum of all attributes in cart (the sku attributes) as calculated in cart->attributes_price(item)
    function getItemTotal() { return $this->getTaxRate()->addTax($this->zenItem_['final_price']) * $this->zenItem_['quantity']; }
    function getTaxClassId() { return $this->zenItem_['tax_class_id']; }
    function hasOneTimeCharges() { return 0 != $this->zenItem_['onetime_charges']; }
    function getOneTimeCharges() { return $this->getTaxRate()->addTax($this->zenItem_['onetime_charges']); }


    /**
     * Get the number of items in the cart.
     *
     * @return int The cart quantity.
     */
    public function getQuantity() { 
        return $this->zenItem_['quantity'];
    }

    /**
     * Get the tax rate for this item.
     *
     * @return ZMTaxRate The tax rate or <code>null</code>.
     */
    public function getTaxRate() {
        return ZMTaxRates::instance()->getTaxRateForClassId($this->zenItem_['tax_class_id']);
    }

    /**
     * Get the product this item is associated with.
     *
     * @return ZMProduct The product.
     */
    public function getProduct() { 
        return ZMProducts::instance()->getProductForId($this->getId());
    }

    /**
     * Check if this cart item has attributes or not.
     *
     * @return boolean <code>true</code> if there are attributes (values) available,
     *  <code>false</code> if not.
     */
    function hasAttributes() { 
        return 0 != $this->getAttributes();
    }

    /**
     * Get selected attributes for this cart item.
     *
     * @return array List of product attributes.
     */
    public function getAttributes() { 
        if (null !== $this->attributes_) {
            return $this->attributes_;
        }
        if (!isset($this->zenItem_['attributes']) || !is_array($this->zenItem_['attributes'])) {
            return array();
        }

        // build attribute => value list map
        $attrMap = array();
        foreach ($this->zenItem_['attributes'] as $option => $valueId) {
            $tmp = explode('_', $option);
            $attributeId = $tmp[0];
            if (!array_key_exists($attributeId, $attrMap)) {
                $attrMap[$attributeId] = array();
            }
            $attrMap[$attributeId][$valueId] = $valueId;
        }

        // values of text/upload attributes
        $textValues = $this->zenItem_['attributes_values'];

        // now get all attributes and strip the not selected stuff
        $productAttributes = $this->getProduct()->getAttributes();
        $attributes = array();
        foreach ($productAttributes as $productAttribute) {
            // drop optional attributes and unselected values
            if (array_key_exists($productAttribute->getId(), $attrMap)) {
                $attribute = clone $productAttribute;
                $valueIds = $attrMap[$productAttribute->getId()];
                foreach ($productAttribute->getValues() as $value) {
                    if (!array_key_exists($value->getId(), $valueIds)) {
                        $attribute->removeValue($value);
                    } else if (in_array($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_TEXT, PRODUCTS_OPTIONS_TYPE_FILE))) {
                        // these should have only a single value
                        if (0 == $value->getId()) {
                            $value->setName($textValues[$attribute->getId()]);
                        }
                    }
                }
                $attributes[] = $attribute;
            }
        }

        // keep copy
        $this->attributes_ = $attributes;
        return $attributes;
    }

    /**
     * Check stock availability for the current quantity.
     *
     * @return boolean <code>true</code> if sufficient stock is available, <code>false</code> if not.
     */
    public function isStockAvailable() {
        return ZMProducts::instance()->isQuantityAvailable($this->getId(), $this->getQty());
    }

}

?>
