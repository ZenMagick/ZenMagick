<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 radebatz.net
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
 *
 * $Id$
 */
?>
<?php


/**
 * A single attribute value.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMAttributeValue {
    var $id_;
    var $name_;
    var $price_;
    var $oneTimePrice_;
    var $pricePrefix_;
    var $isFree_;
    var $weight_;
    var $weightPrefix_;
    var $isDisplayOnly_;
    var $isDefault_;
    var $isDiscounted_;
    var $image_;
    var $isOneTime_;
    var $isPriceFactorOneTime_;
/*
[products_options_values_id] => 16 [products_options_values_name] => Red [products_attributes_id] => 508 [products_id] => 78 [options_id] => 1 [options_values_id] => 16 [options_values_price] => 100.0000 [price_prefix] => + [products_options_sort_order] => 10 [product_attribute_is_free] => 0 [products_attributes_weight] => 0 [products_attributes_weight_prefix] => + [attributes_display_only] => 0 [attributes_default] => 0 [attributes_discounted] => 1 [attributes_image] => [attributes_price_base_included] => 1 [attributes_price_onetime] => 0.0000 [attributes_price_factor] => 0.0000 [attributes_price_factor_offset] => 0.0000 [attributes_price_factor_onetime] => 0.0000 [attributes_price_factor_onetime_offset] => 0.0000 [attributes_qty_prices] => [attributes_qty_prices_onetime] => [attributes_price_words] => 0.0000 [attributes_price_words_free] => 0 [attributes_price_letters] => 0.0000 [attributes_price_letters_free] => 0 [attributes_required] => 0 ) 
*/

    // create new instance
    function ZMAttributeValue($id, $name) {
        $this->id_ = $id;
        $this->name_ = $name;
    }

    // create new instance
    function __construct($id, $name) {
        $this->ZMAttributeValue($id, $name);
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getName() { return $this->name_; }
    function getPrice() { return $this->price_; }
    function getOneTimePrice() { return $this->oneTimePrice_; }
    function getPricePrefix() { return $this->pricePrefix_; }
    function isFree() { return $this->isFree_; }
    function getWeight() { return $this->weight_; }
    function getWeightPrefix() { return $this->weightPrefix_; }
    function isDisplayOnly() { return $this->isDisplayOnly_; }
    function isDefault() { return $this->isDefault_; }
    function isDiscounted() { return $this->isDiscounted_; }
    function getImage() { return $this->image_; }
    function isOneTime() { return $this->isOneTime_; }
    function isPriceFactorOneTime() { return $this->isPriceFactorOneTime_; }

}

?>
