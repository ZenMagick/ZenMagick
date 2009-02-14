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
 * A product.
 *
 * @author DerManoMann
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMProduct extends ZMModel {
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
    var $isAlwaysFreeShipping_;
    var $isCall_;
    var $taxClassId_;
    var $discountType_;
    var $discountTypeFrom_;
    var $priceSorter_;
    var $pricedByAttributes_;
    var $masterCategoryId_;
    var $sortOrder_;

    // raw price
    var $productPrice_;
    var $specialPrice_;

    // funny bits
    var $attributes_;
    var $offers_;


    /**
     * Create new product.
     *
     * @param int id The product id.
     * @param string name The product name.
     * @param string description The product description.
     */
    function __construct($id=0, $name='', $description='') {
        parent::__construct();
        $this->setId($id);
        $this->name_ = $name;
        $this->description_ = $description;
        $this->productPrice_ = 0;
        $this->specialPrice_ = 0;
        $this->sortOrder_ = 0;
        $this->attributes_ = null;
        $this->offers_ = null;
        $this->isQtyMixed_ = false;
        $this->qtyBoxStatus_ = 1;
        $this->priceSorter_ = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the product id.
     *
     * @return int The product id.
     */
    function getId() { return $this->get('productId'); }

    /**
     * Set the product id.
     *
     * @param int id The product id.
     */
    public function setId($id) { $this->set('productId', $id); }

    /**
     * Get the product name.
     *
     * @return string The product name.
     */
    public function getName() { return $this->name_; }

    /**
     * Set the product name.
     *
     * @param string name The product name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Get the description.
     *
     * @return string The product description.
     */
    public function getDescription() { return $this->description_; }

    /**
     * Set the description.
     *
     * @param string description The product description.
     */
    public function setDescription($description) { $this->description_ = $description; }

    /**
     * Get the product status.
     *
     * @return boolean The product status.
     */
    public function getStatus() { return $this->status_; }

    /**
     * Set the product status.
     *
     * @param boolean status The product status.
     */
    public function setStatus($status) { $this->status_ = $status; }

    /**
     * Get the model.
     *
     * @return string The model.
     */
    public function getModel() { return $this->model_; }

    /**
     * Set the model.
     *
     * @param string model The model.
     */
    public function setModel($model) { $this->model_ = $model; }

    /**
     * Get the product default image.
     *
     * @return string The default image.
     */
    public function getDefaultImage() { 
        return (empty($this->image_) && ZMSettings::get('isShowNoPicture')) ? ZMSettings::get('imgNotFound') : $this->image_;
    }

    /**
     * Set the product default image.
     *
     * @param string image The default image.
     */
    public function setDefaultImage($image) { $this->image_ = $image; }

    /**
     * Get the product url.
     *
     * @return string The product url.
     */
    public function getUrl() { return $this->url_; }

    /**
     * Set the product url.
     *
     * @param string url The product url.
     */
    public function setUrl($url) { $this->url_ = $url; }
    /**
     * Get the available date.
     *
     * @return string The available date.
     */
    public function getDateAvailable() { return $this->dateAvailable_; }

    /**
     * Get the date the product was added.
     *
     * @return string The product added date.
     */
    public function getDateAdded() { return $this->dateAdded_; }

    /**
     * Get the manufacturer id.
     *
     * @return int The manufacturer id.
     */
    public function getManufacturerId() { return $this->manufacturerId_; }

    /**
     * Set the manufacturer id.
     *
     * @param int manufacturerId The manufacturer id.
     */
    public function setManufacturerId($manufacturerId) { $this->manufacturerId_ = $manufacturerId; }

    /**
     * Get the manufacturer.
     *
     * @return ZMManufacturer The manufacturer.
     */
    public function getManufacturer() { 
        return ZMManufacturers::instance()->getManufacturerForProduct($this); 
    }

    /**
     * Get the product weight.
     *
     * @return float The weight.
     */
    public function getWeight() { return $this->weight_; }

    /**
     * Set the product weight.
     *
     * @param float weight The weight.
     */
    public function setWeight($weight) { $this->weight_ = $weight; }

    /**
     * Get the quantity.
     *
     * @return int The quantity.
     */
    public function getQuantity() { return $this->quantity_; }

    /**
     * Set the quantity.
     *
     * @param int quantity The quantity.
     */
    public function setQuantity($quantity) { $this->quantity_ = $quantity; }

    /**
     * Checks if the product quantity is calculated across product variations or not.
     *
     * @return boolean <code>true</code> if the quantity is calculated across variations, <code>false</code> if not.
     */
    public function isQtyMixed() { return $this->isQtyMixed_; }

    /**
     * Checks if the product is sold out.
     *
     * @return boolean <code>true</code> if the product is sold out, <code>false</code> if not.
     */
    public function isSoldOut() { return 0 >= $this->quantity_; }

    /**
     * Get the quantity box status.
     *
     * @return int The quantity box status.
     */
    public function getQtyBoxStatus() { return $this->qtyBoxStatus_; }

    /**
     * Get the max quantity per order.
     *
     * @return int The max quantity per order.
     */
    public function getMaxOrderQty() { return $this->qtyOrderMax_; }

    /**
     * Get the min quantity per order.
     *
     * @return int The min quantity per order.
     */
    public function getMinOrderQty() { return $this->qtyOrderMin_; }

    /**
     * Checks if the product is free.
     *
     * @return boolean <code>true</code> if the product is free, <code>false</code> if not.
     */
    public function isFree() { return $this->isFree_; }

    /**
     * Set the product is free flag.
     *
     * @param boolean value <code>true</code> if the product is free, <code>false</code> if not.
     */
    public function setFree($value) { $this->isFree_ = $value; }

    /**
     * Checks if the product is always free shipping
     *
     * @return boolean <code>true</code> if the product is free shipping, <code>false</code> if not.
     */
    public function isAlwaysFreeShipping() { return $this->isAlwaysFreeShipping_; }

    /**
     * Configure if the product is always free shipping
     *
     * @param boolean b <code>true</code> if the product is free shipping, <code>false</code> if not.
     */
    public function setAlwaysFreeShipping($b) { $this->isAlwaysFreeShipping_ = $b; }

    /**
     * Checks if the user needs to call for this product.
     *
     * @return boolean <code>true</code> if the user must call, <code>false</code> if not.
     */
    public function isCall() { return $this->isCall_; }

    /**
     * Sets the flag to indicate that the user needs to call for this product.
     *
     * @param boolean value <code>true</code> if the user must call, <code>false</code> if not.
     */
    public function setCall($value) { $this->isCall_ = $value; }
    /**
     * Get the tax class id.
     *
     * @return int The tax class id.
     */
    public function getTaxClassId() { return $this->taxClassId_; }

    /**
     * Set the tax class id.
     *
     * @param int taxClassId The tax class id.
     */
    public function setTaxClassId($taxClassId) { $this->taxClassId_ = $taxClassId; }

    /**
     * Get the discount type.
     *
     * <p>Legal values:</p>
     * <ul>
     *  <li><em>ZMOffers::DISCOUNT_TYPE_NONE</em> - no discount</li>
     *  <li><em>ZMOffers::DISCOUNT_TYPE_PERCENT</em> - value is percent value</li>
     *  <li><em>ZMOffers::DISCOUNT_TYPE_PRICE</em> - value is fixed price</li>
     *  <li><em>ZMOffers::DISCOUNT_TYPE_AMOUNT</em> - value to be subtracted from base/special price</li>
     * </ul>
     *
     * @return int The discount type.
     */
    public function getDiscountType() { return $this->discountType_; }

    /**
     * Get the discount type from.
     * 
     * <p>Legal values:</p>
     * <ul>
     *  <li><em>ZMOffers::DISCOUNT_FROM_BASE_PRICE</em> - use base price to calculate discount pricing</li>
     *  <li><em>ZMOffers::DISCOUNT_FROM_SPECIAL_PRICE</em> - use special price to calculate discount pricing</li>
     * </ul>
     *
     * @return int The discount type from.
     */
    public function getDiscountTypeFrom() { return $this->discountTypeFrom_; }

    /**
     * Get the tax rate.
     *
     * @return ZMTaxRate The tax rate.
     */
    public function getTaxRate() { return ZMTaxRates::instance()->getTaxRateForClassId($this->taxClassId_); }

    /**
     * Get the product price sorter.
     *
     * @return float The price sorter.
     */
    public function getPriceSorter() { return $this->priceSorter_; }

    /**
     * Get the master category id.
     *
     * @return int The master category id.
     */
    public function getMasterCategoryId() { return $this->masterCategoryId_; }

    /**
     * Set the master category id.
     *
     * @param int categoryId The master category id.
     */
    public function setMasterCategoryId($categoryId) { $this->masterCategoryId_ = $categoryId; }

    /**
     * Get the calculated product price.
     *
     * @return float The product price.
     */
    public function getPrice() { return $this->getOffers()->getCalculatedPrice(); }

    /**
     * Get the product price.
     *
     * @return float The product price.
     */
    public function getProductPrice() { return $this->productPrice_; }

    /**
     * Set the product price.
     *
     * @param float productPrice The product price.
     */
    public function setProductPrice($productPrice) { $this->productPrice_ = $productPrice; }

    /**
     * Get the product special price.
     *
     * @return float The product special price.
     */
    public function getSpecialPrice() { return $this->specialPrice_; }

    /**
     * Set the product special price.
     *
     * @param float specialPrice The product special price.
     */
    public function setSpecialPrice($specialPrice) { $this->specialPrice_ = $specialPrice; }

    /**
     * Get the product offers.
     *
     * @return ZMOffers The offers (if any), for this product.
     */
    public function getOffers() { 
        if (null == $this->offers_) {
            $this->offers_ = ZMLoader::make("Offers", $this); 
        }
        return $this->offers_;
    }

    /**
     * Check if this product has attributes or not.
     *
     * @return boolean <code>true</code> if there are attributes (values) available,
     *  <code>false</code> if not.
     */
    public function hasAttributes() { return 0 < count($this->getAttributes()); }

    /**
     * Get the product attributes.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array A list of {@link org.zenmagick.model.catalog.ZMAttribute ZMAttribute} instances.
     */
    public function getAttributes($languageId=null) { 
        if (null === $this->attributes_) {
            $this->attributes_ = ZMAttributes::instance()->getAttributesForProduct($this, $languageId);
        }

        return $this->attributes_;
    }

    /**
     * Get the product image info.
     *
     * @return ZMImageInfo The product image info.
     */
    public function getImageInfo() { return ZMLoader::make("ImageInfo", $this->image_, $this->name_); }

    /**
     * Set the product image.
     *
     * @param string image The product image.
     */
    public function setImage($image) { $this->image_ = $image; }

    /**
     * Get the product image.
     *
     * @return image The product image.
     */
    public function getImage() { return $this->image_; }

    /**
     * Get additional product images.
     *
     * @return array List of optional <code>ZMImageInfo</code> instances.
     */
    public function getAdditionalImages() { return ZMImageInfo::getAdditionalImages($this->image_); }


    /**
     * Checks if the price is affected by attribute prices.
     *
     * @return boolean <code>true</code> if the price is affected by attributes, <code>false</code> if not.
     * @deprecated use ZMOffers::isAttributePrice() instead
     */
    public function isAttributePrice() { return $this->getOffers()->isAttributePrice(); }


    /**
     * Checks if reviews exist for this product.
     *
     * @return boolean <code>true</code> if reviews exist, <code>false</code> if not.
     */
    public function hasReviews() { 
        return 0 < ZMReviews::instance()->getReviewCount($this->getId());
    }

    /**
     * Get the number of reviews for this product.
     *
     * @return int The number of reviews.
     */
    public function getReviewCount() { 
        return ZMReviews::instance()->getReviewCount($this);
    }

    /**
     * Get the product type config values for this product.
     *
     * <p>This corresponds to the 'Catalog' -&gt; 'Product Type' settings in the admin interface.</p>
     *
     * @param string field The field name.
     * @return mixed The setting value.
     */
    public function getTypeSetting($field) { 
        return ZMProducts::instance()->getProductTypeSetting($this->getId(), $field);
    }

    /**
     * Get the default category.
     *
     * <p>This will return either the master category or the first mapped category for this
     * product.</p>
     *
     * @return ZMCategory The default category.
     */
    public function getDefaultCategory() {
        return null != $this->masterCategoryId_ ? ZMCategories::instance()->getCategoryForId($this->masterCategoryId_) :
            ZMCategories::instance()->getDefaultCategoryForProductId($this->getId());
    }

    /**
     * Get the average rating.
     *
     * <p>Convenience method for <code>ZMReviews::instance()->getAverageRatingForProductId($product->getId())</code>.</p>
     *
     * @return float The average rating.
     */
    public function getAverageRating() {
        return ZMReviews::instance()->getAverageRatingForProductId($this->getId());
    }

    /**
     * Get the srt order.
     *
     * @return int The sort order.
     */
    public function getSortOrder() { return $this->sortOrder_; }

    /**
     * Set the sort order.
     *
     * @param int sortOrder The sort order.
     */
    public function setSortOrder($sortOrder) { $this->sortOrder_ = $sortOrder; }

    /**
     * Set the priced by attributes flag.
     *
     * @param boolean value The new value.
     */
    public function setPricedByAttributes($value) { $this->pricedByAttributes_ = $value; }

    /**
     * Check if the product is priced by attributes.
     *
     * @return boolean <code>true</code> if priced by attributes.
     */
    public function isPricedByAttributes() { return $this->pricedByAttributes_; }

    /**
     * Set the discount type.
     *
     * @param int type The discount type.
     */
    public function setDiscountType($type) { $this->discountType_ = $type; }

    /**
     * Set the discount type from.
     *
     * @param int The discount type from.
     */
    public function setDiscountTypeFrom($typeFrom) { $this->discountTypeFrom_ = $typeFrom; }

}

?>
