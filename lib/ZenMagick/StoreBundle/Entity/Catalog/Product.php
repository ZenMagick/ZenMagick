<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
namespace ZenMagick\StoreBundle\Entity\Catalog;


use ZMImageInfo;
use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * A product.
 *
 * @author DerManoMann
 * @ORM\Table(name="products",
 *  indexes={
 *      @ORM\Index(name="idx_products_date_added_zen", columns={"products_date_added"}),
 *      @ORM\Index(name="idx_products_status_zen", columns={"products_status"}),
 *      @ORM\Index(name="idx_products_date_available_zen", columns={"products_date_available"}),
 *      @ORM\Index(name="idx_products_ordered_zen", columns={"products_ordered"}),
 *      @ORM\Index(name="idx_products_model_zen", columns={"products_model"}),
 *      @ORM\Index(name="idx_products_price_sorter_zen", columns={"products_price_sorter"}),
 *      @ORM\Index(name="idx_master_categories_id_zen", columns={"master_categories_id"}),
 *      @ORM\Index(name="idx_products_sort_order_zen", columns={"products_sort_order"}),
 *      @ORM\Index(name="idx_manufacturers_id_zen", columns={"manufacturers_id"}),
 *  })
 * @ORM\Entity
 */
class Product extends ZMObject {
    const SHIPPING_NORMAL = 0;
    const SHIPPING_ALWAYS_FREE = 1;
    const SHIPPING_SPECIAL = 2;

    /**
     * @var integer $productId
     * @ORM\Column(name="products_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $productId;
    /**
     * @var integer $type
     *
     * @ORM\Column(name="products_type", type="integer", nullable=false)
     */
    private $type;
    /**
     * @var boolean $status
     *
     * @ORM\Column(name="products_status", type="boolean", nullable=false)
     */
    private $status;
    /**
     * @var string $model
     *
     * @ORM\Column(name="products_model", type="string", length=32, nullable=true)
     */
    private $model;
    /**
     * @var string $image
     *
     * @ORM\Column(name="products_image", type="string", length=64, nullable=true)
     */
    private $image;
    /**
     * @var datetime $dateAvailable
     *
     * @ORM\Column(name="products_date_available", type="datetime", nullable=true)
     */
    private $dateAvailable;
    /**
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="products_date_added", type="datetime", nullable=false)
     */
    private $dateAdded;
    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="products_last_modified", type="datetime", nullable=true)
     */
    private $lastModified;
    /**
     * @var integer $manufacturerId
     *
     * @ORM\Column(name="manufacturers_id", type="integer", nullable=true)
     */
    private $manufacturerId;
    /**
     * @var float $weight
     *
     * @ORM\Column(name="products_weight", type="float", nullable=false)
     */
    private $weight;
    /**
     * @var float $quantity
     *
     * @ORM\Column(name="products_quantity", type="float", nullable=false)
     */
    private $quantity;
    /**
     * @var int $ordered
     *
     * @todo a product shouldn't keep count of how
     *       many times it has been ordered REMOVE!
     * @ORM\Column(name="products_ordered", type="float", nullable=false)
     */
    private $ordered;
    /**
     * @var boolean $qtyMixed
     *
     * @ORM\Column(name="products_quantity_mixed", type="boolean", nullable=false)
     */
    private $qtyMixed;
    /**
     * @var boolean $qtyBoxStatus
     *
     * @ORM\Column(name="products_qty_box_status", type="boolean", nullable=false)
     */
    private $qtyBoxStatus;
    /**
     * @var float $qtyOrderMin
     *
     * @ORM\Column(name="products_quantity_order_min", type="float", nullable=false)
     */
    private $qtyOrderMin;
    /**
     * @var float $qtyOrderMax
     *
     * @ORM\Column(name="products_quantity_order_max", type="float", nullable=false)
     */
    private $qtyOrderMax;
    /**
     * @var float $qtyOrderUnits
     *
     * @ORM\Column(name="products_quantity_order_units", type="float", nullable=false)
     */
    private $qtyOrderUnits;
    /**
     * @var boolean $qtyMixedDiscount
     *
     * @ORM\Column(name="products_mixed_discount_quantity", type="float", nullable=false)
     */
    private $qtyMixedDiscount;
    /**
     * @var boolean $free
     *
     * @ORM\Column(name="product_is_free", type="boolean", nullable=false)
     */
    private $free;
    /**
     * @var boolean $alwaysFreeShipping
     *
     * @ORM\Column(name="product_is_always_free_shipping", type="integer", nullable=false)
     */
    private $alwaysFreeShipping;
    /**
     * @var boolean $call
     *
     * @ORM\Column(name="product_is_call", type="boolean", nullable=false)
     */
    private $call;
    /**
     * @var boolean $virtual
     *
     * @ORM\Column(name="products_virtual", type="boolean", nullable=false)
     */
    private $virtual;
    /**
     * @var integer $taxClassId
     *
     * @ORM\Column(name="products_tax_class_id", type="integer", nullable=false)
     */
    private $taxClassId;
    /**
     * @var boolean $discountType
     *
     * @ORM\Column(name="products_discount_type", type="boolean", nullable=false)
     */
    private $discountType;
    /**
     * @var boolean $discountTypeFrom
     *
     * @ORM\Column(name="products_discount_type_from", type="boolean", nullable=false)
     */
    private $discountTypeFrom;
    /**
     * @var decimal $priceSorter
     *
     * @ORM\Column(name="products_price_sorter", type="decimal", nullable=false)
     */
    private $priceSorter;
    /**
     * @var boolean $pricedByAttributes
     *
     * @ORM\Column(name="products_priced_by_attribute", type="boolean", nullable=false)
     */
    private $pricedByAttributes;
    /**
     * @var integer $masterCategoryId
     *
     * @ORM\Column(name="master_categories_id", type="integer", nullable=false)
     */
    private $masterCategoryId;
    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="products_sort_order", type="integer", nullable=false)
     */
    private $sortOrder;
    /**
     * @var boolean $metaTagsTitleStatus
     *
     * @ORM\Column(name="metatags_title_status", type="boolean", nullable=false)
     */
    private $metaTagsTitleStatus;
    /**
     * @var boolean $metaTagsProductNameStatus
     *
     * @ORM\Column(name="metatags_products_name_status", type="boolean", nullable=false)
     */
    private $metaTagsProductNameStatus;
    /**
     * @var boolean $metaTagsModelStatus
     *
     * @ORM\Column(name="metatags_model_status", type="boolean", nullable=false)
     */
    private $metaTagsModelStatus;
    /**
     * @var boolean $metaTagsPriceStatus
     *
     * @ORM\Column(name="metatags_price_status", type="boolean", nullable=false)
     */
    private $metaTagsPriceStatus;
    /**
     * @var boolean $metaTagsTitleTaglineStatus
     *
     * @ORM\Column(name="metatags_title_tagline_status", type="boolean", nullable=false)
     */
    private $metaTagsTitleTaglineStatus;
    /**
     * Raw product price
     * @var decimal $productPrice
     *
     * @ORM\Column(name="products_price", type="decimal", nullable=false)
     */
    private $productPrice;

    // Info comes from other tables

    private $specialPrice;

    private $languageId;
    private $name;
    private $description;
    private $url;

    private $attributes;
    private $offers;


    /**
     * Create new product.
     *
     * @param int id The product id.
     * @param string name The product name.
     * @param string description The product description.
     */
    public function __construct($id=0, $name='', $description='') {
        parent::__construct();
        $this->setId($id);
        $this->name = $name;
        $this->description = $description;
        $this->productPrice = 0;
        $this->specialPrice = 0;
        $this->sortOrder = 0;
        $this->attributes = null;
        $this->offers = null;
        $this->qtyMixed = false;
        $this->qtyBoxStatus = 1;
        $this->priceSorter = 0;
        $this->languageId = 0;
        $this->setDateAdded(null);
        $this->setLastModified(null);
    }


    /**
     * Get the product id.
     *
     * @return int The product id.
     */
    public function getId() { return $this->productId; }

    // @todo deprecated doctrine backwards compatibility
    public function getProductId() { return $this->getId(); }
    /**
     * Set the product id.
     *
     * @param int id The product id.
     */
   public function setId($id) { $this->productId = $id; }

    // @todo deprecated doctrine backwards compatibility
    public function setProductId($id) { $this->setId($id); }

    /**
     * Get the product type.
     *
     * @return integer $type
     */
    public function getType() { return $this->type; }

    /**
     * Set the product type.
     *
     * @param integer $type
     */
    public function setType($type) { $this->type = $type; }

    /**
     * Get the product name.
     *
     * @return string The product name.
     */
    public function getName() { return $this->name; }

    /**
     * Set the product name.
     *
     * @param string name The product name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Get the description.
     *
     * @return string The product description.
     */
    public function getDescription() { return $this->description; }

    /**
     * Set the description.
     *
     * @param string description The product description.
     */
    public function setDescription($description) { $this->description = $description; }

    /**
     * Get the product status.
     *
     * @return boolean $status The product status.
     */
    public function getStatus() { return $this->status; }

    /**
     * Set the product status.
     *
     * @param boolean $status The product status.
     */
    public function setStatus($status) { $this->status = $status; }

    /**
     * Get the model.
     *
     * @return string $model The model.
     */
    public function getModel() { return $this->model; }

    /**
     * Set the model.
     *
     * @param string $model The model.
     */
    public function setModel($model) { $this->model = $model; }

    /**
     * Get the product default image.
     *
     * @return string $image The default image.
     */
    public function getDefaultImage() {
        return (empty($this->image) && $this->container->get('settingsService')->get('isShowNoPicture')) ? $this->container->get('settingsService')->get('imgNotFound') : $this->image;
    }

    /**
     * Set the product default image.
     *
     * @param string $image The default image.
     */
    public function setDefaultImage($image) { $this->image = $image; }

    /**
     * Get the product url.
     *
     * @return string The product url.
     */
    public function getUrl() { return $this->url; }

    /**
     * Set the product url.
     *
     * @param string url The product url.
     */
    public function setUrl($url) { $this->url = $url; }
    /**
     * Get the available date.
     *
     * @return string $dateAvailable The available date.
     */
    public function getDateAvailable() { return $this->dateAvailable; }

    /**
     * Set the date the product is available.
     *
     * @param datetime $productsDateAvailable
     */
    public function setDateAvailable($dateAvailable) { $this->dateAvailable = $dateAvailable; }

    /**
     * Get the date the product was added.
     *
     * @return string The product added date.
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Set the date the product was added.
     *
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Get the date the product was last modified.
     *
     * @return datetime $lastModified
     */
    public function getLastModified() { return $this->lastModified; }

    /**
     * Set the date the product was modified.
     *
     * @param datetime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Get the manufacturer id.
     *
     * @return int The manufacturer id.
     */
    public function getManufacturerId() { return $this->manufacturerId; }

    /**
     * Set the manufacturer id.
     *
     * @param int manufacturerId The manufacturer id.
     */
    public function setManufacturerId($manufacturerId) { $this->manufacturerId = $manufacturerId; }

    /**
     * Get the manufacturer.
     *
     * @return Manufacturer The manufacturer.
     */
    public function getManufacturer() {
        return $this->container->get('manufacturerService')->getManufacturerForProduct($this);
    }

    /**
     * Get the product weight.
     *
     * @return float The weight.
     */
    public function getWeight() { return $this->weight; }

    /**
     * Set the product weight.
     *
     * @param float weight The weight.
     */
    public function setWeight($weight) { $this->weight = $weight; }

    /**
     * Get the quantity.
     *
     * @return int The quantity.
     */
    public function getQuantity() { return $this->quantity; }

    /**
     * Set the quantity.
     *
     * @param int quantity The quantity.
     */
    public function setQuantity($quantity) { $this->quantity = $quantity; }

    /**
     * Checks if the product quantity is calculated across product variations or not.
     *
     * @return boolean <code>true</code> if the quantity is calculated across variations, <code>false</code> if not.
     */
    public function isQtyMixed() { return $this->qtyMixed; }

    /**
     * Checks if the product quantity is calculated across product variations or not.
     *
     * @return boolean
     */
    public function getQtyMixed() { return $this->qtyMixed; }

    /**
     * Set the product quantity mixed flag.
     *
     * @param int $qtyMixed
     */
    public function setQtyMixed($qtyMixed) { $this->qtyMixed = $qtyMixed; }

    /**
     * Checks if the product is sold out.
     *
     * @return boolean <code>true</code> if the product is sold out, <code>false</code> if not.
     */
    public function isSoldOut() { return 0 >= $this->quantity; }

    /**
     * Get the quantity box status.
     *
     * @return int The quantity box status.
     */
    public function getQtyBoxStatus() { return $this->qtyBoxStatus; }

    /**
     * Set the quantity box status.
     *
     * @param boolean $qtyBoxStatus
     */
    public function setQtyBoxStatus($qtyBoxStatus) { $this->qtyBoxStatus = $qtyBoxStatus; }

    /**
     * Get the max quantity per order.
     *
     * @return int The max quantity per order.
     */
    public function getMaxOrderQty() { return $this->qtyOrderMax; }

    /**
     * Set the max quantity per order.
     *
     * @param float $productsQuantityOrderMax
     */
    public function setMaxOrderQty($qtyOrderMax) { $this->qtyOrderMax = $qtyOrderMax; }

    /**
     * Set qtyOrderMax
     *
     * @param float $qtyOrderMax
     */
    public function setQtyOrderMax($qtyOrderMax) { $this->qtyOrderMax = $qtyOrderMax; }

    /**
     * Get qtyOrderMax
     *
     * @return float
     */
    public function getQtyOrderMax() { return $this->qtyOrderMax; }

    /**
     * Get the min quantity per order.
     *
     * @return int The min quantity per order.
     */
    public function getMinOrderQty() { return $this->qtyOrderMin; }

    /**
     * Set the min quantity per order
     *
     * @param float $qtyOrderMin
     */
    public function setMinOrderQty($qtyOrderMin) { $this->qtyOrderMin = $qtyOrderMin; }

    /**
     * Set qtyOrderMin
     *
     * @param float $qtyOrderMin
     */
    public function setQtyOrderMin($qtyOrderMin) { $this->qtyOrderMin = $qtyOrderMin; }

    /**
     * Get qtyOrderMin
     *
     * @return float
     */
    public function getQtyOrderMin() { return $this->qtyOrderMin; }

    /**
     * Get the quantity units.
     *
     * @return float The quantity units.
     */
    public function getQtyOrderUnits() { return $this->qtyOrderUnits; }

    /**
     * Set the quantity units.
     *
     * @param float value The quantity units value.
     */
    public function setQtyOrderUnits($value) { $this->qtyOrderUnits = $value; }

    /**
     * Get qtyMixedDiscount
     *
     * @return boolean $qtyMixedDiscount
     */
    public function getQtyMixedDiscount() { return $this->qtyMixedDiscount; }

    /**
     * Set qtyMixedDiscount
     *
     * @param boolean $qtyMixedDiscount
     */
    public function setQtyMixedDiscount($qtyMixedDiscount) { $this->qtyMixedDiscount = $qtyMixedDiscount; }

    /**
     * Checks if the product is free.
     *
     * @return boolean <code>true</code> if the product is free, <code>false</code> if not.
     */
    public function isFree() { return $this->free; }

    /**
     * Get free
     *
     * @return boolean
     */
    public function getFree() { return $this->free; }

    /**
     * Set the product is free flag.
     *
     * @param boolean value <code>true</code> if the product is free, <code>false</code> if not.
     */
    public function setFree($value) { $this->free = $value; }

    /**
     * Checks if the product is virtual.
     *
     * @return boolean <code>true</code> if the product is virtual, <code>false</code> if not.
     */
    public function isVirtual() { return $this->virtual; }

    /**
     * Checks if the product is virtual.
     *
     * @return boolean <code>true</code> if the product is virtual, <code>false</code> if not.
     */
    public function getVirtual() { return $this->virtual; }

    /**
     * Set the product is virtual flag.
     *
     * @param boolean value <code>true</code> if the product is virtual, <code>false</code> if not.
     */
    public function setVirtual($value) { $this->virtual = $value; }

    /**
     * Get the always free shipping setting.
     *
     * @return int The value.
     */
    public function getAlwaysFreeShipping() { return $this->alwaysFreeShipping; }

    /**
     * Checks if the product is always free shipping
     *
     * @return boolean <code>true</code> if the product is free shipping, <code>false</code> if not.
     */
    public function isAlwaysFreeShipping() { return self::SHIPPING_ALWAYS_FREE == $this->alwaysFreeShipping; }

    /**
     * Configure if the product is always free shipping
     *
     * @param int value The new value.
     */
    public function setAlwaysFreeShipping($value) { $this->alwaysFreeShipping = $value; }

    /**
     * Checks if the user needs to call for this product.
     *
     * @return boolean <code>true</code> if the user must call, <code>false</code> if not.
     */
    public function isCall() { return $this->call; }

    /**
     * Checks if the user needs to call for this product.
     *
     * @return boolean <code>true</code> if the user must call, <code>false</code> if not.
     */
    public function getCall() { return $this->call; }

    /**
     * Sets the flag to indicate that the user needs to call for this product.
     *
     * @param boolean value <code>true</code> if the user must call, <code>false</code> if not.
     */
    public function setCall($value) { $this->call = $value; }
    /**
     * Get the tax class id.
     *
     * @return int The tax class id.
     */
    public function getTaxClassId() { return $this->taxClassId; }

    /**
     * Set the tax class id.
     *
     * @param int taxClassId The tax class id.
     */
    public function setTaxClassId($taxClassId) { $this->taxClassId = $taxClassId; }

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
    public function getDiscountType() { return $this->discountType; }

    /**
     * Set discountType
     *
     * @param boolean $discountType
     */
    public function setDiscountType($discountType) { $this->discountType = $discountType; }

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
    public function getDiscountTypeFrom() { return $this->discountTypeFrom; }

    /**
     * Set discountTypeFrom
     *
     * @param boolean $discountTypeFrom
     */
    public function setDiscountTypeFrom($discountTypeFrom) { $this->discountTypeFrom = $discountTypeFrom; }

    /**
     * Get the tax rate.
     *
     * @return TaxRate The tax rate.
     */
    public function getTaxRate() { return $this->container->get('taxService')->getTaxRateForClassId($this->taxClassId); }

    /**
     * Get the product price sorter.
     *
     * @return float The price sorter.
     */
    public function getPriceSorter() { return $this->priceSorter; }

    /**
     * Set the product price sorter.
     *
     * @param decimal $priceSorter The price sorter.
     */
    public function setPriceSorter($priceSorter) { $this->priceSorter = $priceSorter; }

    /**
     * Get the master category id.
     *
     * @return int The master category id.
     */
    public function getMasterCategoryId() { return $this->masterCategoryId; }

    /**
     * Set the master category id.
     *
     * @param int categoryId The master category id.
     */
    public function setMasterCategoryId($categoryId) { $this->masterCategoryId = $categoryId; }

    /**
     * Get the calculated product price.
     *
     * <p>This is the same as <code>getOffers->getCalculatedPrice(true)</code>. This also means this
     * method will always include TAX.</p>
     *
     * @return float The product price.
     */
    public function getPrice() { return $this->getOffers()->getCalculatedPrice(); }

    /**
     * Get the product price.
     *
     * @return float The product price.
     */
    public function getProductPrice() { return $this->productPrice; }

    /**
     * Set the product price.
     *
     * @param float productPrice The product price.
     */
    public function setProductPrice($productPrice) { $this->productPrice = $productPrice; }

    /**
     * Get the product special price.
     *
     * @return float The product special price.
     */
    public function getSpecialPrice() { return $this->specialPrice; }

    /**
     * Set the product special price.
     *
     * @param float specialPrice The product special price.
     */
    public function setSpecialPrice($specialPrice) { $this->specialPrice = $specialPrice; }

    /**
     * Get the product offers.
     *
     * @return ZMOffers The offers (if any), for this product.
     */
    public function getOffers() {
        if (null == $this->offers) {
            $this->offers = Beans::getBean('ZMOffers');
            $this->offers->setProduct($this);
        }
        return $this->offers;
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
     * @return array A list of {@link org.zenmagick.model.catalog.ZMAttribute ZMAttribute} instances.
     */
    public function getAttributes() {
        if (null === $this->attributes) {
            $this->attributes = $this->container->get('attributeService')->getAttributesForProduct($this);
        }

        return $this->attributes;
    }

    /**
     * Get the product image info.
     *
     * @return ZMImageInfo The product image info.
     */
    public function getImageInfo() {
        $imageInfo = Beans::getBean('ZMImageInfo');
        $imageInfo->setAltText($this->name);
        $imageInfo->setDefaultImage($this->image);
        return $imageInfo;
    }

    /**
     * Set the product image.
     *
     * @param string image The product image.
     */
    public function setImage($image) { $this->image = $image; }

    /**
     * Get the product image.
     *
     * @return image The product image.
     */
    public function getImage() { return $this->image; }

    /**
     * Get additional product images.
     *
     * @return array List of optional <code>ZMImageInfo</code> instances.
     */
    public function getAdditionalImages() { return ZMImageInfo::getAdditionalImages($this->image); }


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
        return 0 < $this->getReviewCount();
    }

    /**
     * Get the number of reviews for this product.
     *
     * @return int The number of reviews.
     */
    public function getReviewCount() {
        return $this->container->get('reviewService')->getReviewCount($this->getId(), $this->languageId);
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
        return $this->container->get('productService')->getProductTypeSetting($this->getId(), $field);
    }

    /**
     * Get the default category.
     *
     * <p>This will return either the master category or the first mapped category for this
     * product.</p>
     *
     * @param int languageId The languageId; default is <code>null</code> for session setting.
     * @return ZMCategory The default category.
     */
    public function getDefaultCategory($languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->container->get('session')->getLanguageId();

        $categoryService = $this->container->get('categoryService');
        return null != $this->masterCategoryId ? $categoryService->getCategoryForId($this->masterCategoryId, $languageId) :
            $categoryService->getDefaultCategoryForProductId($this->getId(), $languageId);
    }

    /**
     * Get the average rating.
     *
     * <p>Convenience method for <code>getAverageRatingForProductId($product->getId(), $product->getLanguageId())</code>.</p>
     *
     * @return float The average rating.
     */
    public function getAverageRating() {
        return $this->container->get('reviewService')->getAverageRatingForProductId($this->getId(), $this->languageId);
    }

    /**
     * Get the sort order.
     *
     * @return int The sort order.
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Set the sort order.
     *
     * @param int sortOrder The sort order.
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }

    /**
     * Set the priced by attributes flag.
     *
     * @param boolean value The new value.
     */
    public function setPricedByAttributes($value) { $this->pricedByAttributes = $value; }

    /**
     * Check if the product is priced by attributes.
     *
     * @return boolean <code>true</code> if priced by attributes.
     */
    public function getPricedByAttributes() { return $this->pricedByAttributes; }

    /**
     * Check if the product is priced by attributes.
     *
     * @return boolean <code>true</code> if priced by attributes.
     */
    public function isPricedByAttributes() { return $this->pricedByAttributes; }

    /**
     * Get the language id.
     *
     * @return int The language id.
     */
    public function getLanguageId() { return $this->languageId; }

    /**
     * Set the language id.
     *
     * @param int id The language id.
     */
    public function setLanguageId($id) { $this->languageId = $id; }

    /**
     * Get product associations for the given type(s) and parameter.
     *
     * @param string type The association type.
     * @param array args Optional parameter that might be required by the used type; default is an empty array.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociations($type, $args=array(), $all=false) {
        // some defaults
        if (!array_key_exists('languageId', $args)) {
            $args['languageId'] = $this->getLanguageId();
        }
        return $this->container->get('productAssociationService')->getProductAssociationsForProductId($this->getId(), $type, $args, $all);
    }

    /**
     * Get meta tag details if available.
     *
     * @param int languageId Optional language id; default is <code>null</code> to use the current language id of this instance.
     * @return ZMMetaTagDetails The details or <code>null</code>.
     */
    public function getMetaTagDetails($languageId=null) {
        return $this->container->get('productService')->getMetaTagDetailsForId($this->getId(), null != $languageId ? $languageId : $this->getLanguageId());
    }

    /**
     * Get meta tags title status
     *
     * @return boolean $metaTagsTitleStatus
     */
    public function getMetaTagsTitleStatus() { return $this->metaTagsTitleStatus; }

    /**
     * Set meta tags title status
     *
     * @param boolean $metaTagsTitleStatus
     */
    public function setMetaTagsTitleStatus($metaTagsTitleStatus) { $this->metaTagsTitleStatus = $metaTagsTitleStatus; }

    /**
     * Get meta tags product name status
     *
     * @return boolean $metaTagsProductNameStatus
     */
    public function getMetaTagsProductNameStatus() { return $this->metaTagsProductNameStatus; }

    /**
     * Set meta tags product name status
     *
     * @param boolean $metaTagsProductNameStatus
     */
    public function setMetaTagsProductNameStatus($metaTagsProductNameStatus) { $this->metaTagsProductNameStatus = $metaTagsProductNameStatus; }

    /**
     * Get meta tags model status
     *
     * @return boolean $metaTagsModelStatus
     */
    public function getMetaTagsModelStatus() { return $this->metaTagsModelStatus; }

    /**
     * Set meta tags model status
     *
     * @param boolean $metaTagsModelStatus
     */
    public function setMetaTagsModelStatus($metaTagsModelStatus) { $this->metaTagsModelStatus = $metaTagsModelStatus; }

    /**
     * Get meta tags price status.
     *
     * @return boolean $metaTagsPriceStatus
     */
    public function getMetaTagsPriceStatus() { return $this->metaTagsPriceStatus; }

    /**
     * Set meta tags price status.
     *
     * @param boolean $metaTagsPriceStatus
     */
    public function setMetaTagsPriceStatus($metaTagsPriceStatus) { $this->metaTagsPriceStatus = $metaTagsPriceStatus; }

    /**
     * Get meta tags title tagline status.
     *
     * @return boolean $metaTagsTitleTaglineStatus
     */
    public function getMetaTagsTitleTaglineStatus() { return $this->metaTagsTitleTaglineStatus; }

    /**
     * Set meta tags title tagline status.
     *
     * @param boolean $metaTagsTitleTaglineStatus
     */
    public function setMetaTagsTitleTaglineStatus($metaTagsTitleTaglineStatus) { $this->metaTagsTitleTaglineStatus = $metaTagsTitleTaglineStatus; }

    /**
     * Set ordered
     *
     * @param float $ordered
     */
    public function setOrdered($ordered) { $this->ordered = $ordered; }

    /**
     * Get ordered
     *
     * @return float
     */
    public function getOrdered() { return $this->ordered; }
}
