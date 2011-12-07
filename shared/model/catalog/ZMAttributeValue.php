<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A single attribute value.
 *
 * <p>For attributes that accept user input (text/upload), the name will be replaced
 * with the entered data to allow handling all values the same way.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.catalog
 */
class ZMAttributeValue extends ZMObject {
    private $attribute_;
    private $name_;
    private $price_;
    private $oneTimePrice_;
    private $pricePrefix_;
    private $isFree_;
    private $weight_;
    private $weightPrefix_;
    private $isDisplayOnly_;
    private $isDefault_;
    private $isDiscounted_;
    private $image_;
    private $isPriceFactorOneTime_;
    private $isIncludeInBasePrice_;
    private $sortOrder_;
    private $taxRate_;


    /**
     * Create new instance.
     */
    function __construct($id=0, $name=null) {
        parent::__construct();
        $this->setId($id);
        $this->name_ = $name;
        $this->sortOrder_ = 0;
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
    public function getId() { return $this->get('attributeValueId'); }

    /**
     * Get the parent attribute.
     *
     * @return ZMAttribute The attribute.
     */
    public function getAttribute() { return $this->attribute_; }

    /**
     * Get the attribute value name.
     *
     * @return string The attribute value name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the value price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return double The price.
     */
    public function getValuePrice($tax=true) {
        return $tax ? $this->taxRate_->addTax($this->price_) : $this->price_;
    }

    /**
     * Get one time charge (if any) for the given range and quantity.
     *
     * @param string qtyPrices The qty/price mappings.
     * @param int quantity The quantity.
     * @return float The one time charge.
     */
    protected function getQtyPrice($qtyPrices, $quantity) {
        $qtyPriceMap = preg_split("/[:,]/" , $qtyPrices);
        $price = 0;
        $size = count($qtyPriceMap);
        if (1 < $size) {
            for ($ii=0; $ii<$size; $ii+=2) {
                $price = $qtyPriceMap[$ii+1];
                if ($quantity <= $qtyPriceMap[$ii]) {
                    $price = $qtyPriceMap[$ii+1];
                    break;
                }
            }
        }

        return $price;
    }

    /**
     * Get the price factor charge.
     *
     * <p>The setting <em>'isDiscountAttributePriceFactor'</em> will determine whether to use
     * the discount or regular price.</p>
     *
     * @param float price The calculated price.
     * @param float discountPrice The discounted price (if any).
     * @param float priceFactor The price factor.
     * @param int priceFactorOffset The price factopr offset.
     * @return float The price factor price.
     */
    protected function getPriceFactorCharge($price, $discountPrice, $priceFactor, $priceFactorOffset) {
        if (Runtime::getSettings()->get('isDiscountAttributePriceFactor') && 0 != $discountPrice) {
            return $discountPrice * ($priceFactor - $priceFactorOffset);
        } else {
            return $price * ($priceFactor - $priceFactorOffset);
        }
    }

    /**
     * Get the final attribute price without discount.
     *
     * @param int quantity The quantity.
     * @return float The price.
     */
    protected function getFinalPriceForQty($quantity) {
        $price = $this->price_;
        if ('-' == $this->pricePrefix_) {
            $price = -$this->price_;
        }

        // quantity onetime discounts
        $price += $this->getQtyPrice($this->getQtyPrices(), $quantity);

        // price factor
        $product = $this->container->get('productService')->getProductForId($this->attribute_->getProductId());
        $offers = $product->getOffers();
        $discountPrice = $offers->isSale() ? $offers->getSalePrice(false) : $offers->getSpecialPrice(false);

        $price += $this->getPriceFactorCharge($offers->getCalculatedPrice(false), $discountPrice ,$this->getPriceFactor(), $this->getPriceFactorOffset());

        return $price;
    }

    /**
     * Get the final (and discounted) value price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @param int quantity Optional quantity; default is <em>1</em>.
     * @return double The price.
     */
    public function getPrice($tax=true, $quantity=1) {
        $price = $this->price_;
        if ($this->isDiscounted_) {
            //TODO: cache value
            $price = $this->getFinalPriceForQty($quantity);
            // no need to discount free attributes
            if (0 != $price) {
                $product = $this->container->get('productService')->getProductForId($this->attribute_->getProductId());
                $price = $product->getOffers()->calculateDiscount($price);
            }
        }

        return $tax ? $this->taxRate_->addTax($price) : $price;
    }

    /**
     * Get the final one time attribute price.
     *
     * @param int quantity The quantity.
     * @return float The price.
     */
    protected function getFinalOneTimePriceForQty($quantity) {
        $price = $this->oneTimePrice_;

        // quantity onetime discounts
        $price += $this->getQtyPrice($this->getQtyPricesOneTime(), $quantity);

        // price factor
        $product = $this->container->get('productService')->getProductForId($this->attribute_->getProductId());
        $offers = $product->getOffers();
        $discountPrice = $offers->isSale() ? $offers->getSalePrice(false) : $offers->getSpecialPrice(false);

        $price += $this->getPriceFactorCharge($offers->getCalculatedPrice(false), $discountPrice,
                                                $this->getPriceFactorOneTime(), $this->getPriceFactorOneTimeOffset());

        return $price;
    }

    /**
     * Get the final one time price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return double The attributes one time price.
     */
    public function getOneTimePrice($tax=true) {
        $price = $this->oneTimePrice_;
        if (0 != $price || $this->isPriceFactorOneTime_) {
            //TODO: cache
            $price = $this->getFinalOneTimePriceForQty(1);
        }

        return $tax ? $this->taxRate_->addTax($price) : $price;
    }

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
     * Check if the base price is included.
     *
     * @return boolean <code>true</code> if the base price is included, <code>false</code> if not.
     */
    public function isIncludeInBasePrice() { return $this->isIncludeInBasePrice_; }

    /**
     * Get the attribute value image (if any).
     *
     * @return string The attribute value image name.
     */
    public function getImage() { return $this->image_; }

    /**
     * Check if the price factor is one time.
     *
     * @return boolean <code>true</code> if the price factor is one time only, <code>false</code> if not.
     */
    public function isPriceFactorOneTime() { return $this->isPriceFactorOneTime_; }

    /**
     * Get the tax rate.
     *
     * @return ZMTaxRate The tax rate.
     */
    public function getTaxRate() { return $this->taxRate_; }

    /**
     * Set the attribute value id.
     *
     * @param int id The attribute value id.
     */
    public function setId($id) { $this->set('attributeValueId', $id); }

    /**
     * Set the parent attribute.
     *
     * @param ZMAttribute attribute The attribute.
     */
    public function setAttribute($attribute ) { $this->attribute_ = $attribute; }

    /**
     * Set the attribute value name.
     *
     * @param string name The attribute value name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the value price.
     *
     * @param double price The price.
     */
    public function setValuePrice($price) {
        $this->price_ = $price;
    }

    /**
     * Set the values one time price.
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
     * Set the price factor is one time flag.
     *
     * @param boolean value <code>true</code> if the price factor is one time only, <code>false</code> if not.
     */
    public function setPriceFactorOneTime($value) { $this->isPriceFactorOneTime_ = $value; }

    /**
     * Set the base price is included flag.
     *
     * @param boolean value <code>true</code> if the base price is included, <code>false</code> if not.
     */
    public function setIncludeInBasePrice($value) { $this->isIncludeInBasePrice_ = $value; }

    /**
     * Set the tax rate.
     *
     * @param ZMTaxRate taxRate The tax rate.
     */
    public function setTaxRate($taxRate) { $this->taxRate_ = $taxRate; }

    /**
     * Get the attribute value sort order.
     *
     * @return int The attribute sort order.
     */
    public function getSortOrder() { return $this->sortOrder_; }

    /**
     * Set the attribute value sort order.
     *
     * @param int sortOrder The attribute sort order.
     */
    public function setSortOrder($sortOrder) { $this->sortOrder_ = $sortOrder; }

}
