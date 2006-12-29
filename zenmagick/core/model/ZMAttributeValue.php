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


    /**
     * Default c'tor.
     */
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
    function hasImage() { return null !== $this->image_ && '' != $this->image_; }
    function getImage() { return $this->image_; }
    function isOneTime() { return $this->isOneTime_; }
    function isPriceFactorOneTime() { return $this->isPriceFactorOneTime_; }

}

?>
