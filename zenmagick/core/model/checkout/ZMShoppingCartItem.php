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
 * A single shopping cart item.
 *
 * @author mano
 * @package org.zenmagick.model.order
 * @version $Id$
 */
class ZMShoppingCartItem extends ZMModel {
    var $cart_;
    var $zenItem_;
    var $taxRate_;
    var $attributes_;


    /**
     * Create new shopping cart item
     *
     * @param ZMShoppingCart cart The associated shopping cart.
     * @param array zenItem The zen-cart shopping item infos.
     */
    function ZMShoppingCartItem($cart, $zenItem) {
        parent::__construct();

        $this->cart_ = $cart;
        $this->zenItem_ = $zenItem;
        $this->attributes_ = null;
    }

    /**
     * Create new shopping cart item
     *
     * @param ZMShoppingCart cart The associated shopping cart.
     * @param array zenItem The zen-cart shopping item infos.
     */
    function __construct($cart, $zenItem) {
        $this->ZMShoppingCartItem($cart, $zenItem);
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
    function getImageInfo() { return $this->create("ImageInfo", $this->zenItem_['image'], $this->zenItem_['name']); }
    function getQty() { return $this->zenItem_['quantity']; }
    function getItemPrice() { $taxRate = $this->getTaxRate(); return $taxRate->addTax($this->zenItem_['final_price']); }
    function getItemTotal() { $taxRate = $this->getTaxRate(); return $taxRate->addTax($this->zenItem_['final_price']) * $this->zenItem_['quantity']; }
    function getTaxClassId() { return $this->zenItem_['tax_class_id']; }

    /**
     * Get the tax rate for this item.
     *
     * @return ZMTaxRate The tax rate or <code>null</code>.
     */
    function getTaxRate() {
        return ZMTaxRates::instance()->getTaxRateForClassId($this->zenItem_['tax_class_id']);
    }

    /**
     * Get the product this item is associated to.
     *
     * @return ZMProduct The product.
     */
    function getProduct() { 
        return ZMProducts::instance()->getProductForId($this->getId());
    }

    function hasOneTimeCharges() { return 0 != $this->zenItem_['onetime_charges']; }
    function getOneTimeCharges() { $taxRate = $this->getTaxRate(); return $taxRate->addTax($this->zenItem_['onetime_charges']); }

    function hasAttributes() { return 0 != $this->getAttributes(); }
    function getAttributes() { 
        if (null == $this->attributes_) {
            $this->attributes_ = $this->cart_->_getItemAttributes($this);
        }
        return $this->attributes_;
    }

    /**
     * Check stock availability for the current quantity.
     *
     * @return boolean <code>true</code> if sufficient stock is available, <code>false</code> if not.
     */
    function isStockAvailable() {
        return !zen_check_stock($this->getId(), $this->getQty());
    }

}

?>
