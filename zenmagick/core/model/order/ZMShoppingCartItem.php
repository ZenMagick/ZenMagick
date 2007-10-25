<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
        $this->taxRate_ = null;
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
     * Default d'tor.
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
    function isStockAvailable() { return !zm_not_null(zm_check_stock($this->getId(), $this->zenItem_['quantity'])); }
    function getItemPrice() { return zm_add_tax($this->zenItem_['final_price'], $this->getTaxRate()); }
    function getItemTotal() { return zm_add_tax($this->zenItem_['final_price'], $this->getTaxRate()) * $this->zenItem_['quantity']; }
    function getTaxClassId() { return $this->zenItem_['tax_class_id']; }
    function getTaxRate() {
        if (null == $this->taxRate_) {
            $this->taxRate_ = zm_get_tax_rate($this->zenItem_['tax_class_id']);
        }
        return $this->taxRate_;
    }
    function hasOneTimeCharges() { return 0 != $this->zenItem_['onetime_charges']; }
    function getOneTimeCharges() { return zm_add_tax($this->zenItem_['onetime_charges'], $this->getTaxRate()); }

    function hasAttributes() { return 0 != $this->getAttributes(); }
    function getAttributes() { 
        if (null == $this->attributes_) {
            $this->attributes_ = $this->cart_->_getItemAttributes($this);
        }
        return $this->attributes_;
    }
}

?>
