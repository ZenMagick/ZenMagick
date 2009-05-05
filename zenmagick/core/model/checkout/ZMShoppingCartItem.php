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
    private $zenItem_;
    private $attributes_;


    /**
     * Create new shopping cart item
     *
     * @param array zenItem The zen-cart shopping item infos.
     */
    function __construct($zenItem=null) {
        parent::__construct();
        $this->zenItem_ = $zenItem;
        if (null !== $zenItem) {
            $this->setId($zenItem['id']);
            $this->setQuantity($zenItem['quantity']);
        }
        $this->attributes_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    // @deprecated
    function getName() { return $this->getProduct()->getName(); }
    // @deprecated
    function getImage() { return $this->getProduct()->getImage(); }
    // @deprecated
    function getImageInfo() { return $this->getProduct()->getImageInfo(); }
    // @deprecated
    function getQty() { return $this->getQuantity(); }
    // @deprecated
    function getTaxClassId() { return $this->getProduct()->getTaxClassId(); }
    /**
     * Get the tax rate for this item.
     *
     * @return ZMTaxRate The tax rate or <code>null</code>.
     * @deprecated use getProduct() and use that instead
     */
    public function getTaxRate() { return ZMTaxRates::instance()->getTaxRateForClassId($this->getTaxClassId()); }



    /**
     * Get the cart item id (the sku).
     *
     * <p>This will differ from the product id if attributes are attached to the cart item.</p>
     *
     * @return string The product/sku id.
     */
    public function getId() {
        return $this->get('id');
    }

    /**
     * Set the cart item id.
     *
     * @param string id The product/sku id.
     */
    public function setId($id) {
        $this->set('id', $id);
    }

    /**
     * Get the number of items in the cart.
     *
     * @return int The cart quantity.
     */
    public function getQuantity() { 
        return $this->get('quantity');
    }

    /**
     * Set the quantity for this item.
     *
     * @param int quantity The cart quantity.
     */
    public function setQuantity($quantity) { 
        $this->set('quantity', $quantity);
    }

    /**
     * Get the product this item is associated with.
     *
     * @return ZMProduct The product.
     */
    public function getProduct() { 
        //TODO: use some sort of base product id method to extract the id from the sku (id:attr-hash)
        return ZMProducts::instance()->getProductForId($this->getId());
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
     */
    public function getItemTotal($tax=true) { 
        return $this->getItemPrice($tax) * $this->getQuantity();
    }

    /**
     * Check stock availability for the current quantity.
     *
     * @return boolean <code>true</code> if sufficient stock is available, <code>false</code> if not.
     */
    public function isStockAvailable() {
        return ZMProducts::instance()->isQuantityAvailable($this->getId(), $this->getQty());
    }

    /**
     * Get the item price.
     *
     * @param boolean tax Optional flag to include/exlcude tax; default is <code>true</code> to include tax.
     * @return float The price for a single item.
     */
    public function getItemPrice($tax=true) { 
        if ($tax) {
            return $this->getTaxRate()->addTax($this->zenItem_['final_price']);
        }

        return $this->zenItem_['final_price'];
    }

    // ( item price + attribute price ) * qty
    // item_price: product->getProductPrice() // the base price excl. attributes (as that would include all lowest option attrs as in catalog view)
    // if attribute price - else qty discount price
    // attribute_price = sum of all attributes in cart (the sku attributes) as calculated in cart->attributes_price(item)

    function hasOneTimeCharges() { return 0 != $this->zenItem_['onetime_charges']; }
    function getOneTimeCharges() { return $this->getTaxRate()->addTax($this->zenItem_['onetime_charges']); }


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

}

?>
