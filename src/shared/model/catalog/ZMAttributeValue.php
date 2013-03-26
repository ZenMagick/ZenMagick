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

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * A single attribute value.
 *
 * <p>For attributes that accept user input (text/upload), the name will be replaced
 * with the entered data to allow handling all values the same way.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.catalog
 */
class ZMAttributeValue extends ZMObject
{
    private $attribute;
    private $name;
    private $price;
    private $oneTimePrice;
    private $pricePrefix;
    private $isFree;
    private $weight;
    private $weightPrefix;
    private $isDisplayOnly;
    private $isDefault;
    private $isDiscounted;
    private $image;
    private $isPriceFactorOneTime;
    private $isIncludeInBasePrice;
    private $sortOrder;
    private $taxRate;

    /**
     * Create new instance.
     */
    public function __construct($id=0, $name=null)
    {
        parent::__construct();
        $this->setId($id);
        $this->name = $name;
        $this->sortOrder = 0;
    }

    /**
     * Get the attribute value id.
     *
     * @return int The attribute value id.
     */
    public function getId()
    {
        return $this->get('attributeValueId');
    }

    /**
     * Get the parent attribute.
     *
     * @return ZenMagick\StoreBundle\Entity\Catalog\Attribute The attribute.
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Get the attribute value name.
     *
     * @return string The attribute value name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return double The price.
     */
    public function getValuePrice($tax=true)
    {
        return $tax ? $this->taxRate->addTax($this->price) : $this->price;
    }

    /**
     * Get one time charge (if any) for the given range and quantity.
     *
     * @param string qtyPrices The qty/price mappings.
     * @param int quantity The quantity.
     * @return float The one time charge.
     */
    protected function getQtyPrice($qtyPrices, $quantity)
    {
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
    protected function getPriceFactorCharge($price, $discountPrice, $priceFactor, $priceFactorOffset)
    {
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
    protected function getFinalPriceForQty($quantity)
    {
        $price = $this->price;
        if ('-' == $this->pricePrefix) {
            $price = -$this->price;
        }

        // quantity onetime discounts
        $price += $this->getQtyPrice($this->getQtyPrices(), $quantity);

        // price factor
        $product = $this->container->get('productService')->getProductForId($this->attribute->getProductId());
        $offers = $product->getOffers();
        $discountPrice = $offers->isSale() ? $offers->getSalePrice(false) : $offers->getSpecialPrice(false);

        $price += $this->getPriceFactorCharge($offers->getCalculatedPrice(false), $discountPrice, $this->getPriceFactor(), $this->getPriceFactorOffset());

        return $price;
    }

    /**
     * Calculate text price.
     *
     * @param string text The text.
     * @return double The price.
     */
    protected function calculateTextPrice($text)
    {
        $letterPrice = $this->countPriceableLetters($text) * $this->getPriceLetters();
        $wordPrice = $this->countPriceableWords($text) * $this->getPriceWords();

        return $letterPrice + $wordPrice;
    }

    /**
     * Get the final (and discounted) value price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @param int quantity Optional quantity for quantity discounts; default is <em>1</em>.
     * @param string value Optional value for attributes that accept customer input (text attribute, for example); default is <code>null</code>.
     * @return double The price.
     */
    public function getPrice($tax=true, $quantity=1, $value=null)
    {
        $price = $this->price;
        if ($this->isDiscounted) {
            $price = $this->getFinalPriceForQty($quantity);

            if (null !== $value) {
                $price += $this->calculateTextPrice($value);
            }

            // no need to discount free attributes
            if (0 != $price) {
                $product = $this->container->get('productService')->getProductForId($this->attribute->getProductId());
                $price = $product->getOffers()->calculateDiscount($price);
            }
        }

        return $tax ? $this->taxRate->addTax($price) : $price;
    }

    /**
     * Get the final one time price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @param int quantity The quantity; default is 1.
     * @return double The attributes one time price.
     */
    public function getOneTimePrice($tax=true, $quantity=1)
    {
        $price = $this->oneTimePrice;
        if (0 != $price || $this->isPriceFactorOneTime) {
            // quantity onetime discounts
            $price += $this->getQtyPrice($this->getQtyPricesOneTime(), $quantity);

            // price factor
            $product = $this->container->get('productService')->getProductForId($this->attribute->getProductId());
            $offers = $product->getOffers();
            $discountPrice = $offers->isSale() ? $offers->getSalePrice(false) : $offers->getSpecialPrice(false);

            $price += $this->getPriceFactorCharge($offers->getCalculatedPrice(false), $discountPrice,
                                                    $this->getPriceFactorOneTime(), $this->getPriceFactorOneTimeOffset());
        }

        return $tax ? $this->taxRate->addTax($price) : $price;
    }

    /**
     * Get the price prefix.
     *
     * @return string The price prefix.
     */
    public function getPricePrefix()
    {
        return $this->pricePrefix;
    }

    /**
     * Check if the attribute is free.
     *
     * @return boolean <code>true</code> if the value is free, <code>false</code> if not.
     */
    public function isFree()
    {
        return $this->isFree;
    }

    /**
     * Get the attribute weight.
     *
     * @return double The attribute weight.
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Get the weight prefix.
     *
     * @return string The weight prefix.
     */
    public function getWeightPrefix()
    {
        return $this->weightPrefix;
    }

    /**
     * Check if the attribute is 'display only'.
     *
     * @return boolean <code>true</code> if the value is display only, <code>false</code> if not.
     */
    public function isDisplayOnly()
    {
        return $this->isDisplayOnly;
    }

    /**
     * Check if this is the default value.
     *
     * @return boolean <code>true</code> if this is the default value, <code>false</code> if not.
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * Check if this value is discounted.
     *
     * @return boolean <code>true</code> if this value is discounted, <code>false</code> if not.
     */
    public function isDiscounted()
    {
        return $this->isDiscounted;
    }

    /**
     * Check if this value has an associated image.
     *
     * @return boolean <code>true</code> if an image is available, <code>false</code> if not.
     */
    public function hasImage()
    {
        return null !== $this->image && '' != $this->image;
    }

    /**
     * Check if the base price is included.
     *
     * @return boolean <code>true</code> if the base price is included, <code>false</code> if not.
     */
    public function isIncludeInBasePrice()
    {
        return $this->isIncludeInBasePrice;
    }

    /**
     * Get the attribute value image (if any).
     *
     * @return string The attribute value image name.
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Check if the price factor is one time.
     *
     * @return boolean <code>true</code> if the price factor is one time only, <code>false</code> if not.
     */
    public function isPriceFactorOneTime()
    {
        return $this->isPriceFactorOneTime;
    }

    /**
     * Get the tax rate.
     *
     * @return TaxRate The tax rate.
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * Set the attribute value id.
     *
     * @param int id The attribute value id.
     */
    public function setId($id)
    {
        $this->set('attributeValueId', $id);

        return $this;
    }

    /**
     * Set the parent attribute.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Attribute attribute The attribute.
     */
    public function setAttribute($attribute )
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set the attribute value name.
     *
     * @param string name The attribute value name.
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the value price.
     *
     * @param double price The price.
     */
    public function setValuePrice($price)
    {
        $this->price = $price;
    }

    /**
     * Set the values one time price.
     *
     * @param double oneTimePrice The attributes one time price.
     */
    public function setOneTimePrice($oneTimePrice)
    {
        $this->oneTimePrice = $oneTimePrice;

        return $this;
    }

    /**
     * Set the price prefix.
     *
     * @param string pricePrefix The price prefix.
     */
    public function setPricePrefix($pricePrefix)
    {
        $this->pricePrefix = $pricePrefix;

        return $this;
    }

    /**
     * Sheck the attribute free flag.
     *
     * @param boolean value <code>true</code> if the value is free, <code>false</code> if not.
     */
    public function setFree($value)
    {
        $this->isFree = $value;

        return $this;
    }

    /**
     * Set the attribute weight.
     *
     * @return double weight The attribute weight.
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Set the weight prefix.
     *
     * @param string weightPrefix The weight prefix.
     */
    public function setWeightPrefix($weightPrefix)
    {
        $this->weightPrefix = $weightPrefix;

        return $this;
    }

    /**
     * Set the attribute is 'display only' flag.
     *
     * @param boolean value <code>true</code> if the value is display only, <code>false</code> if not.
     */
    public function setDisplayOnly($value)
    {
        $this->isDisplayOnly = $value;

        return $this;
    }

    /**
     * Set the default value flag.
     *
     * @param boolean value <code>true</code> if this is the default value, <code>false</code> if not.
     */
    public function setDefault($value)
    {
        $this->isDefault = $value;

        return $this;
    }

    /**
     * Set the is discounted flag.
     *
     * @param boolean value <code>true</code> if this value is discounted, <code>false</code> if not.
     */
    public function setDiscounted($value)
    {
        $this->isDiscounted = $value;

        return $this;
    }

    /**
     * Set the attribute value image (if any).
     *
     * @param string image The attribute value image name.
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Set the price factor is one time flag.
     *
     * @param boolean value <code>true</code> if the price factor is one time only, <code>false</code> if not.
     */
    public function setPriceFactorOneTime($value)
    {
        $this->isPriceFactorOneTime = $value;

        return $this;
    }

    /**
     * Set the base price is included flag.
     *
     * @param boolean value <code>true</code> if the base price is included, <code>false</code> if not.
     */
    public function setIncludeInBasePrice($value)
    {
        $this->isIncludeInBasePrice = $value;

        return $this;
    }

    /**
     * Set the tax rate.
     *
     * @param TaxRate taxRate The tax rate.
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Get the attribute value sort order.
     *
     * @return int The attribute sort order.
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set the attribute value sort order.
     *
     * @param int sortOrder The attribute sort order.
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Calculate the letter count.
     *
     * @param string text The text.
     * @return int The letters accountable for pricing.
     */
    protected function countPriceableLetters($text)
    {
        $ignoreWS = $this->container->get('settingsService')->get('apps.store.pricing.text.ignoreWS');
        $sws = $ignoreWS ? '' : ' ';
        $text = str_replace(array("\r\n", "\n", "\r", "\t"), $sws, trim($text));
        // shrink multi WS to single ws
        while (strstr($text, '  ')) { $text = str_replace('  ', ' ', $text); }
        $text = str_replace(' ', $sws, $text);
        $count = strlen($text) - $this->getPriceLettersFree();

        return 0 > $count ? 0 : $count;
    }

    /**
     * Calculate the word count.
     *
     * @param string text The text.
     * @return int The words accountable for pricing.
     */
    protected function countPriceableWords($text)
    {
        $words = preg_split('/[\s,]+/', trim($text));
        $count = count($words) - $this->getPriceWordsFree();

        return 0 > $count ? 0 : $count;
    }

}
