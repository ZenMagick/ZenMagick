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
 *
 * $Id$
 */
?>
<?php


/**
 * A single attribute value.
 *
 * @author mano
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMAttributeValue extends ZMModel {
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

    /**
     * Default c'tor.
     */
    function __construct($id, $name) {
        $this->ZMAttributeValue($id, $name);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the attribute value id.
     *
     * @return int The attribute value id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the attribute value name.
     *
     * @return string The attribute value name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the attribute price.
     *
     * @return double The price.
     */
    function getPrice() { return $this->price_; }

    /**
     * Get the attributes one time price.
     *
     * @return double The attributes one time price.
     */
    function getOneTimePrice() { return $this->oneTimePrice_; }

    /**
     * Get the price prefix.
     *
     * @return string The price prefix.
     */
    function getPricePrefix() { return $this->pricePrefix_; }

    /**
     * Check if the attribute is free.
     *
     * @return boolean <code>true</code> if the value is free, <code>false</code> if not.
     */
    function isFree() { return $this->isFree_; }

    /**
     * Get the attribute weight.
     *
     * @return double The attribute weight.
     */
    function getWeight() { return $this->weight_; }

    /**
     * Get the weight prefix.
     *
     * @return string The weight prefix.
     */
    function getWeightPrefix() { return $this->weightPrefix_; }

    /**
     * Check if the attribute is 'display only'.
     *
     * @return boolean <code>true</code> if the value is display only, <code>false</code> if not.
     */
    function isDisplayOnly() { return $this->isDisplayOnly_; }

    /**
     * Check if this is the default value.
     *
     * @return boolean <code>true</code> if this is the default value, <code>false</code> if not.
     */
    function isDefault() { return $this->isDefault_; }

    /**
     * Check if this value is discounted.
     *
     * @return boolean <code>true</code> if this value is discounted, <code>false</code> if not.
     */
    function isDiscounted() { return $this->isDiscounted_; }

    /**
     * Check if this value has an associated image.
     *
     * @return boolean <code>true</code> if an image is available, <code>false</code> if not.
     */
    function hasImage() { return null !== $this->image_ && '' != $this->image_; }

    /**
     * Get the attribute value image (if any).
     *
     * @return string The attribute value image name.
     */
    function getImage() { return $this->image_; }

    /**
     * Check if this is a one time value.
     *
     * @return boolean <code>true</code> if this is a one time value, <code>false</code> if not.
     */
    function isOneTime() { return $this->isOneTime_; }

    /**
     * Check if the price factor is one time.
     *
     * @return boolean <code>true</code> if the price factor is one time only, <code>false</code> if not.
     */
    function isPriceFactorOneTime() { return $this->isPriceFactorOneTime_; }

}

?>
