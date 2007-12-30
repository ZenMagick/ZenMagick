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
 * A product.
 *
 * @author mano
 * @package org.zenmagick.model.catalog
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
    var $isQtyMixed_;
    var $qtyBoxStatus_;
    var $qtyOrderMin_;
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
    var $sortOrder_;

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
        $this->sortOrder_ = 0;
    }

    /**
     * Create new product.
     *
     * @param int id The product id.
     * @param string name The product name.
     * @param string description The product description.
     */
    function __construct($id, $name, $description) {
        $this->ZMProduct($id, $name, $description);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the product id.
     *
     * @return int The product id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the product name.
     *
     * @return string The product name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the description.
     *
     * @return string The product description.
     */
    function getDescription() { return $this->description_; }

    /**
     * Get the product status.
     *
     * @return boolean The product status.
     */
    function getStatus() { return $this->status_; }

    /**
     * Get the model.
     *
     * @return string The model.
     */
    function getModel() { return $this->model_; }

    /**
     * Get the product default image.
     *
     * @return string The default image.
     */
    function getDefaultImage() { return $this->image_; }

    /**
     * Get the product URL.
     *
     * @return string The product URL.
     */
    function getURL() { return $this->url_; }

    /**
     * Get the available date.
     *
     * @return string The available date.
     */
    function getDateAvailable() { return $this->dateAvailable_; }

    /**
     * Get the date the product was added.
     *
     * @return string The product added date.
     */
    function getDateAdded() { return $this->dateAdded_; }

    /**
     * Get the manufacturer id.
     *
     * @return int The manufacturer id.
     */
    function getManufacturerId() { return $this->manufacturerId_; }

    /**
     * Get the manufacturer.
     *
     * @return ZMManufacturer The manufacturer.
     */
    function getManufacturer() { global $zm_manufacturers; return $zm_manufacturers->getManufacturerForProduct($this); }

    /**
     * Get the product weight.
     *
     * @return float The weight.
     */
    function getWeight() { return $this->weight_; }

    /**
     * Get the quantity.
     *
     * @return int The quantity.
     */
    function getQuantity() { return $this->quantity_; }

    /**
     * Checks if the product quantity is calculated across product variations or not.
     *
     * @return boolean <code>true</code> if the quantity is calculated across variations, <code>false</code> if not.
     */
    function isQtyMixed() { return $this->isQtyMixed_; }

    /**
     * Checks if the product is sold out.
     *
     * @return boolean <code>true</code> if the product is sold out, <code>false</code> if not.
     */
    function isSoldOut() { return 0 >= $this->quantity_; }

    /**
     * Get the quantity box status.
     *
     * @return int The quantity box status.
     */
    function getQtyBoxStatus() { return $this->qtyBoxStatus_; }

    /**
     * Get the max quantity per order.
     *
     * @return int The max quantity per order.
     */
    function getMaxOrderQty() { return $this->qtyOrderMax_; }

    /**
     * Get the min quantity per order.
     *
     * @return int The min quantity per order.
     */
    function getMinOrderQty() { return $this->qtyOrderMin_; }

    /**
     * Checks if the product is free.
     *
     * @return boolean <code>true</code> if the product is free, <code>false</code> if not.
     */
    function isFree() { return $this->isFree_; }

    /**
     * Checks if the user needs to call for this product.
     *
     * @return boolean <code>true</code> if the user must call, <code>false</code> if not.
     */
    function isCall() { return $this->isCall_; }

    /**
     * Get the tax class id.
     *
     * @return int The tax class id.
     */
    function getTaxClassId() { return $this->taxClassId_; }

    /**
     * Get the discount type.
     *
     * @return int The discount type.
     */
    function getDiscountType() { return $this->discountType_; }

    /**
     * Get the discount start date.
     *
     * @return string The discount start date.
     */
    function getDiscountTypeFrom() { return $this->discountTypeFrom_; }

    /**
     * Get the tax rate.
     *
     * @return ZMTaxRate The tax rate.
     */
    function getTaxRate() { return $this->taxRate_; }

    /**
     * Get the product price sorter.
     *
     * @return float The price sorter.
     */
    function getPriceSorter() { return $this->priceSorter_; }

    /**
     * Get the master category id.
     *
     * @return int The master category id.
     */
    function getMasterCategoryId() { return $this->masterCategoryId_; }

    /**
     * Get the product price.
     *
     * @return float The product price.
     */
    function getPrice() { $offers = $this->getOffers(); return $offers->getCalculatedPrice(); }

    /**
     * Get the product offers.
     *
     * @return ZMOffers The offers (if any), for this product.
     */
    function getOffers() { $this->offers_->setProduct(&$this); return $this->offers_; }

    /**
     * Check if this product has attributes or not.
     *
     * @return boolean <code>true</code> if there are attributes (values) available,
     *  <code>false</code> if not.
     */
    function hasAttributes() { return $this->attributes_->hasAttributes_; }

    /**
     * Get the product attributes.
     *
     * @return array A list of {@link org.zenmagick.model.ZMAttribute ZMAttribute} instances.
     */
    function getAttributes() { return $this->attributes_->getAttributes(); }

    /**
     * Get the product features.
     *
     * @param boolean hidden If <code>true</code>, hidden features will be included in the returned list.
     * @return array List of product features.
     */
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

    /**
     * Get the product image info.
     *
     * @return ZMImageInfo The product image info.
     */
    function getImageInfo() { return $this->create("ImageInfo", $this->image_, $this->name_); }

    /**
     * Get additional product images.
     *
     * @return array List of optional <code>ZMImageInfo</code> instances.
     */
    function getAdditionalImages() { return _zm_get_additional_images($this->image_); }


    /**
     * Checks if the price is affected by attribute prices.
     *
     * @return boolean <code>true</code> if the price is affected by attributes, <code>false</code> if not.
     */
    function isAttributePrice() { return zm_has_product_attributes_values($this->id_); }


    /**
     * Checks if reviews exist for this product.
     *
     * @return boolean <code>true</code> if reviews exist, <code>false</code> if not.
     */
    function hasReviews() { global $zm_reviews; return 0 < $zm_reviews->getReviewCount($this->id_); }

    /**
     * Get the number of reviews for this product.
     *
     * @return int The number of reviews.
     */
    function getReviewCount() { global $zm_reviews; return $zm_reviews->getReviewCount($this); }

    /**
     * Get the product type config values for this product.
     *
     * <p>This corresponds to the 'Catalog' -&gt; 'Product Type' settings in the admin interface.</p>
     *
     * @param string field The field name.
     * @return mixed The setting value.
     */
    function getTypeSetting($field) { global $zm_products; return $zm_products->getProductTypeSetting($this->id_, $field); }

    /**
     * Get the default category.
     *
     * <p>This will return either the master category or the first mapped category for this
     * product.</p>
     *
     * @return ZMCategory The default category.
     */
    function getDefaultCategory() {
    global $zm_categories;

        return null != $this->masterCategoryId_ ? $zm_categories->getCategoryForId($this->masterCategoryId_) :
            $zm_categories->getDefaultCategoryForProductId($this->id_);
    }

    /**
     * Get the average rating.
     *
     * <p>Convenience method for <code>$zm_reviews->getAverageRatingForProductId($product->getId())</code>.</p>
     *
     * @return float The average rating.
     */
    function getAverageRating() {
    global $zm_reviews;

        return $zm_reviews->getAverageRatingForProductId($this->id_);
    }

    /**
     * Get the srt order.
     *
     * @return int The sort order.
     */
    function getSortOrder() { return $this->sortOrder_; }

    /**
     * Set the srt order.
     *
     * @param int sortOrder The sort order.
     */
    function setSortOrder($sortOrder) { $this->sortOrder_ = $sortOrder; }

}

?>
