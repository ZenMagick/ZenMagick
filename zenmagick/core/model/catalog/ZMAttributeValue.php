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
     * Create new instance.
     */
    function __construct($id=0, $name='') {
        parent::__construct();
        $this->id_ = $id;
        $this->name_ = $name;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the attribute value id.
     *
     * @return int The attribute value id.
     */
    public function getId() { return $this->id_; }

    /**
     * Get the attribute value name.
     *
     * @return string The attribute value name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the attribute price.
     *
     * @return double The price.
     */
    public function getPrice() { return $this->price_; }

    /**
     * Get the attributes one time price.
     *
     * @return double The attributes one time price.
     */
    public function getOneTimePrice() { return $this->oneTimePrice_; }

    /**
     * Get the price prefix.
     *
     * @return string The price prefix.
     */
    public function getPricePrefix() { return $this->pricePrefix_; }

    /**
     * Check if the attribute is free.
     *
     * @return boolean <code>true</code> if the value is free, <code>false</code> if not.
     */
    public function isFree() { return $this->isFree_; }

    /**
     * Get the attribute weight.
     *
     * @return double The attribute weight.
     */
    public function getWeight() { return $this->weight_; }

    /**
     * Get the weight prefix.
     *
     * @return string The weight prefix.
     */
    public function getWeightPrefix() { return $this->weightPrefix_; }

    /**
     * Check if the attribute is 'display only'.
     *
     * @return boolean <code>true</code> if the value is display only, <code>false</code> if not.
     */
    public function isDisplayOnly() { return $this->isDisplayOnly_; }

    /**
     * Check if this is the default value.
     *
     * @return boolean <code>true</code> if this is the default value, <code>false</code> if not.
     */
    public function isDefault() { return $this->isDefault_; }

    /**
     * Check if this value is discounted.
     *
     * @return boolean <code>true</code> if this value is discounted, <code>false</code> if not.
     */
    public function isDiscounted() { return $this->isDiscounted_; }

    /**
     * Check if this value has an associated image.
     *
     * @return boolean <code>true</code> if an image is available, <code>false</code> if not.
     */
    public function hasImage() { return null !== $this->image_ && '' != $this->image_; }

    /**
     * Get the attribute value image (if any).
     *
     * @return string The attribute value image name.
     */
    public function getImage() { return $this->image_; }

    /**
     * Check if this is a one time value.
     *
     * @return boolean <code>true</code> if this is a one time value, <code>false</code> if not.
     */
    public function isOneTime() { return $this->isOneTime_; }

    /**
     * Check if the price factor is one time.
     *
     * @return boolean <code>true</code> if the price factor is one time only, <code>false</code> if not.
     */
    public function isPriceFactorOneTime() { return $this->isPriceFactorOneTime_; }

    /**
     * Set the attribute value id.
     *
     * @param int id The attribute value id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * Set the attribute value name.
     *
     * @param string name The attribute value name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the attribute price.
     *
     * @param double price The price.
     */
    public function setPrice($price) { $this->price_ = $price; }

    /**
     * Set the attributes one time price.
     *
     * @param double oneTimePrice The attributes one time price.
     */
    public function setOneTimePrice($oneTimePrice) { $this->oneTimePrice_ = $oneTimePrice; }

    /**
     * Set the price prefix.
     *
     * @param string pricePrefix The price prefix.
     */
    public function setPricePrefix($pricePrefix) { $this->pricePrefix_ = $pricePrefix; }

    /**
     * Sheck the attribute free flag.
     *
     * @param boolean value <code>true</code> if the value is free, <code>false</code> if not.
     */
    public function setFree($value) { $this->isFree_ = $value; }

    /**
     * Set the attribute weight.
     *
     * @return double weight The attribute weight.
     */
    public function setWeight($weight) { $this->weight_ = $weight; }

    /**
     * Set the weight prefix.
     *
     * @param string weightPrefix The weight prefix.
     */
    public function setWeightPrefix($weightPrefix) { $this->weightPrefix_ = $weightPrefix; }

    /**
     * Set the attribute is 'display only' flag.
     *
     * @param boolean value <code>true</code> if the value is display only, <code>false</code> if not.
     */
    public function setDisplayOnly($value) { $this->isDisplayOnly_ = $value; }

    /**
     * Set the default value flag.
     *
     * @param boolean value <code>true</code> if this is the default value, <code>false</code> if not.
     */
    public function setDefault($value) { $this->isDefault_ = $value; }

    /**
     * Set the is discounted flag.
     *
     * @param boolean value <code>true</code> if this value is discounted, <code>false</code> if not.
     */
    public function setDiscounted($value) { $this->isDiscounted_ = $value; }

    /**
     * Set the attribute value image (if any).
     *
     * @param string image The attribute value image name.
     */
    public function setImage($image) { $this->image_ = $image; }

    /**
     * Set the one time value flag.
     *
     * @param boolean value <code>true</code> if this is a one time value, <code>false</code> if not.
     */
    public function setOneTime($value) { $this->isOneTime_ = $value; }

    /**
     * Set the price factor is one time flag.
     *
     * @param boolean value <code>true</code> if the price factor is one time only, <code>false</code> if not.
     */
    public function setPriceFactorOneTime($value) { $this->isPriceFactorOneTime_ = $value; }

}

?>
