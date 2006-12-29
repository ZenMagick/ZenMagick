<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * A product.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMProduct extends ZMModel {
    var $id_;
    var $name_;
    var $description_;
    var $status_;
    var $model_;
    var $image_;
    var $url_;
    var $dateAvailable_;
    var $dateAdded_;
    var $manufacturerId_;
    var $weight_;
    var $quantity_;
    var $qtyBoxStatus_;
    var $qtyOrderMax_;
    var $isFree_;
    var $isCall_;
    var $taxClassId_;
    var $discountType_;
    var $discountTypeFrom_;
    var $taxRate_;
    var $priceSorter_;
    var $pricedByAttributes_;
    var $masterCategoryId_;

    // raw price
    var $price_;

    // funny bits
    var $offers_;
    var $attributes_;
    var $features_;


    /**
     * Create new product.
     *
     * @param int id The product id.
     * @param string name The product name.
     * @param string description The product description.
     */
    function ZMProduct($id, $name, $description) {
        parent::__construct();

        $this->id_ = $id;
        $this->name_ = $name;
        $this->description_ = $description;
        $this->features_ = array();
    }

    // create new instance
    function __construct($id, $name, $description) {
        $this->ZMProduct($id, $name, $description);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getName() { return $this->name_; }
    function getDescription() { return $this->description_; }
    function getStatus() { return $this->status_; }
    function getModel() { return $this->model_; }
    function getDefaultImage() { return $this->image_; }
    function getURL() { return $this->url_; }
    function getDateAvailable() { return $this->dateAvailable_; }
    function getDateAdded() { return $this->dateAdded_; }
    function getManufacturerId() { return $this->manufacturerId_; }
    function getManufacturer() { global $zm_manufacturers; return $zm_manufacturers->getManufacturerForProduct($this); }
    function getWeight() { return $this->weight_; }
    function getQuantity() { return $this->quantity_; }
    function isSoldOut() { return 0 >= $this->quantity_; }
    function getQtyBoxStatus() { return $this->qtyBoxStatus_; }
    function getQtyOrderMax() { return $this->qtyOrderMax_; }
    function isFree() { return $this->isFree_; }
    function isCall() { return $this->isCall_; }
    function getTaxClassId() { return $this->taxClassId_; }
    function getDiscountType() { return $this->discountType_; }
    function getDiscountTypeFrom() { return $this->discountTypeFrom_; }
    function getTaxRate() { return $this->taxRate_; }
    function getPriceSorter() { return $this->priceSorter_; }
    function getMasterCategoryId() { return $this->masterCategoryId_; }
    function getPrice() { $offers = $this->getOffers(); return $offers->getCalculatedPrice(); }
    function getOffers() { return $this->offers_; }
    function getAttributes() { return $this->attributes_; }
    function getFeatures($hidden=false) {
        if (!$hidden) {
            $arr = array();
            foreach ($this->features_ as $feature) {
                if (!$feature->isHidden()) {
                    $arr[$feature->getName()] = $feature;
                }
            }
            return $arr;
        }

        // include hidden
        return $this->features_;
    }
    function getImageInfo() { return $this->create("ImageInfo", $this->image_); }
    function getAdditionalImages() { return _zm_get_additional_images($this->image_); }

    function isAttributePrice() { return zm_has_product_attributes_values($this->id_); }

    function hasReviews() { global $zm_reviews; return 0 < $zm_reviews->getReviewCount($this); }
    function getReviewCount() { global $zm_reviews; return $zm_reviews->getReviewCount($this); }
    function getTypeSetting($field) { global $zm_products; return $zm_products->getProductTypeSetting($this->id_, $field); }

}

?>
