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
use ZenMagick\Base\ZMException;
use ZenMagick\Base\ZMObject;

/**
 * All stuff related to product prices and offers.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.catalog
 */
class ZMOffers extends ZMObject
{
    // sale type constants
    const SALE_TYPE_AMOUNT = 0;
    const SALE_TYPE_PERCENT = 1;
    const SALE_TYPE_PRICE = 2;

    // discount constants
    const DISCOUNT_TYPE_NONE = 0;
    const DISCOUNT_TYPE_PERCENT = 1;
    const DISCOUNT_TYPE_PRICE = 2;
    const DISCOUNT_TYPE_AMOUNT = 3;
    const DISCOUNT_FROM_BASE_PRICE = 0;
    const DISCOUNT_FROM_SPECIAL_PRICE = 1;

    protected $product;
    private $basePrice;
    private $specialPrice;
    private $salePrice;
    private $taxRate;
    private $discountPercent;
    private $discounts;

    /**
     * Create new instance.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Product product The product; default is <code>null</code>.
     */
    public function __construct($product=null)
    {
        parent::__construct();
        $this->basePrice = null;
        $this->specialPrice = null;
        $this->salePrice = null;
        $this->taxRate = null;
        $this->discountPercent = 0;
        $this->discounts = array(true => null, false => null);
        $this->setProduct($product);
    }

    /**
     * Checks if there are attribute prices that will affect the final price.
     *
     * @return boolean <code>true</code> if attribute prices exist.
     */
    public function isAttributePrice()
    {
        foreach ($this->product->getAttributes() as $attribute) {
            foreach ($attribute->getValues() as $value) {
                if (0 < $value->getPrice()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Set the product.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Product product The product.
     */
    public function setProduct($product)
    {
        $this->product = $product;
        $this->calculatePrice();
    }

    /**
     * Get the product price.
     *
     * <p>This is the price as configured in the database.</p>
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return float The product price.
     */
    public function getProductPrice($tax=true)
    {
        return $tax ? $this->getTaxRate()->addTax($this->product->getProductPrice()) : $this->product->getProductPrice();
    }

    /**
     * Get the base price; this is the lowest possible product price.
     *
     * <p>The base price consists of the product price plus the lowest attribute price (if any).</p>
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return float The base price.
     */
    public function getBasePrice($tax=true)
    {
        if (null === $this->basePrice) {
            $this->basePrice = $this->doGetBasePrice();
        }

        return $tax ? $this->getTaxRate()->addTax($this->basePrice) : $this->basePrice;
    }

    /**
     * Calculate the base price.
     */
    protected function doGetBasePrice()
    {
        $basePrice = 0;

        $attributes = $this->product->getAttributes();
        if ($this->product->isPricedByAttributes() && 0 < count($attributes)) {
            // add minimum attributes price to price
            foreach ($attributes as $attribute) {
                $lowest = null;
                foreach ($attribute->getValues() as $value) {
                    if (!$value->isDisplayOnly() && $value->isIncludeInBasePrice()) {
                        if (null == $lowest || $lowest->getValuePrice(false) > $value->getValuePrice(false)) {
                            $lowest = $value;
                        }
                    }
                }
                if (null != $lowest) {
                    $basePrice += $lowest->getValuePrice(false);
                }
            }
        }

        // this is for price factor based attributes (the lower limit is the set price [even though priced by attr])
        $basePrice += $this->getProductPrice(false);

        return $basePrice;
    }

    /**
     * Get the special price.
     *
     * <p>Special price as configured.</p>
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return float The special price.
     */
    public function getSpecialPrice($tax=true)
    {
        if (null === $this->specialPrice) {
            $this->specialPrice = $this->product->getSpecialPrice();
        }

        return $tax ? $this->getTaxRate()->addTax($this->specialPrice) : $this->specialPrice;
    }

    /**
     * Get the discount price.
     *
     * <p>This price is the price as set up with the sales maker in the admin interface.</p>
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return float The discount price.
     */
    public function getSalePrice($tax=true)
    {
        if (null === $this->salePrice) {
            $this->salePrice = $this->doGetSalePrice();
        }

        return $tax ? $this->getTaxRate()->addTax($this->salePrice) : $this->salePrice;
    }

    /**
     * Calculate the discount price.
     */
    protected function doGetSalePrice()
    {
        $basePrice = $this->getBasePrice(false);
        $specialPrice = $this->getSpecialPrice(false);

        // get available sales
        $sql = "SELECT sale_specials_condition, sale_deduction_value, sale_deduction_type
                FROM %table.salemaker_sales%
                WHERE sale_categories_all LIKE '%," . $this->product->getMasterCategoryId() . ",%' AND sale_status = '1'
                AND (sale_date_start <= now() OR sale_date_start = '0001-01-01')
                AND (sale_date_end >= now() OR sale_date_end = '0001-01-01')
                AND (sale_pricerange_from <= :priceFrom  OR sale_pricerange_from = '0')
                AND (sale_pricerange_to >= :priceFrom OR sale_pricerange_to = '0')";
        $args = array('priceFrom' => $basePrice, 'categoriesAll' => '%,'.$this->product->getMasterCategoryId().',%');
        $results = ZMRuntime::getDatabase()->fetchAll($sql, $args, 'salemaker_sales');

        if (0 == count($results)) {
            return $specialPrice;
        }

        // read first result
        $saleType = $results[0]['deductionType'];
        $saleValue = $results[0]['deductionValue'];
        $saleCondition = $results[0]['specialsCondition'];

        // best special price available
        $bestSpecialPrice = $specialPrice ? $specialPrice : $basePrice;

        switch ($saleType) {
            case self::SALE_TYPE_AMOUNT:
                $saleBasePrice = $basePrice - $saleValue;
                $saleSpecialPrice = $bestSpecialPrice - $saleValue;
                break;
            case self::SALE_TYPE_PERCENT:
                $saleBasePrice = $basePrice - (($basePrice * $saleValue) / 100);
                $saleSpecialPrice = $bestSpecialPrice - (($bestSpecialPrice * $saleValue) / 100);
                break;
            case self::SALE_TYPE_PRICE:
                $saleBasePrice = $saleValue;
                $saleSpecialPrice = $saleValue;
                break;
            default:
                // gosh, how'd we get here??
                return $bestSpecialPrice;
        }

        $calculationDecimals = Runtime::getSettings()->get('calculationDecimals');

        // sanitize
        $saleBasePrice = $saleBasePrice < 0 ? 0 : $saleBasePrice;
        $saleSpecialPrice = $saleSpecialPrice < 0 ? 0 : $saleSpecialPrice;

        // default default
        $salePrice = $specialPrice;
        if (!$specialPrice) {
            $salePrice = $saleBasePrice;
        } else {
            switch ($saleCondition) {
                case 0:
                    $salePrice = $saleBasePrice;
                    break;
                case 1:
                    $salePrice = $specialPrice;
                    break;
                case 2:
                    $salePrice = $saleSpecialPrice;
                    break;
                default:
                    $salePrice = $specialPrice;
            }
        }

        return number_format($salePrice, $calculationDecimals, '.', '');
    }


    /**
     * Calculate the (best) price.
     */
    protected function calculatePrice()
    {
        if (null != $this->product) {
            $basePrice = $this->getBasePrice(false);
            $specialPrice = $this->getSpecialPrice(false);
            $salePrice = $this->getSalePrice(false);

            // calculate discount
            $this->discountPercent = 0;
            if ((0 != $specialPrice || 0 != $salePrice) && 0 != $basePrice) {
                if (0 != $salePrice) {
                    $this->discountPercent = number_format(100 - (($salePrice / $basePrice) * 100), Runtime::getSettings()->get('discountDecimals'));
                } else {
                    $this->discountPercent = number_format(100 - (($specialPrice / $basePrice) * 100), Runtime::getSettings()->get('discountDecimals'));
                }
            }
        }
    }

    /**
     * Get the discount as percent value.
     *
     * @return float The discount in percent.
     */
    public function getDiscountPercent() { return $this->discountPercent; }

    /**
     * Get the discount amount.
     *
     * @return float The discount amount.
     */
    public function getDiscountAmount()
    {
        $save = 0;
        if (!$this->product->isFree() && ($this->isSpecial() || $this->isSale())) {
          if ($this->isSpecial()) {
              $save = $this->getBasePrice() - $this->getSpecialPrice();
          } elseif ($this->isSale()) {
              $save = $this->getBasePrice() - $this->getSalePrice();
          }
        }

        return $save;
    }

    /**
     * Get the tax rate for the product.
     *
     * @return float The tax rate.
     */
    public function getTaxRate()
    {
        if (null == $this->taxRate) {
            $this->taxRate = $this->product->getTaxRate();
        }

        return $this->taxRate;
    }

    /**
     * Checks if a special price is available.
     *
     * @return boolean <code>true</code> if a special price is available.
     */
    public function isSpecial() { return 0 != $this->specialPrice && $this->specialPrice != $this->basePrice && !$this->isSale(); }

    /**
     * Checks if a sale price is available.
     *
     * @return boolean <code>true</code> if a sale price is available.
     */
    public function isSale() { return 0 != $this->salePrice; }

    /**
     * Get the calculated price.
     *
     * <p>This is the actual price, taking into account if sale or discount are available.</p>
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return float The calculated price.
     */
    public function getCalculatedPrice($tax=true)
    {
        if ($this->product->isFree()) {
            return 0;
        } elseif (0 != ($salePrice = $this->getSalePrice($tax))) {
            return $salePrice;
        } elseif (0 != ($specialPrice = $this->getSpecialPrice($tax))) {
            return $specialPrice;
        } else {
            return $this->getBasePrice($tax);
        }
    }

    /**
     * Check if there are any quantity discounts.
     *
     * @return boolean <code>true</code> if, and only if there are any discounts.
     */
    public function hasQuantityDiscounts()
    {
        return 0 < count($this->getQuantityDiscounts(false));
    }

    /**
     * Get quantity discount for the given quantity.
     *
     * @param int quantity The quantity.
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return ZMQuantityDiscount A discount or <code>null</code>.
     */
    public function getQuantityDiscountFor($quantity, $tax=true)
    {
        $quantityDiscount = null;
        foreach ($this->getQuantityDiscounts($tax) as $discount) {
            if ($discount->getQuantity() <= $quantity) {
                $quantityDiscount = $discount;
            } else {
                break;
            }
        }

        return $quantityDiscount;
    }

    /**
     * Get quantity discounts, if any.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return array A list of <code>ZMQuantityDiscount</code> instances.
     */
    public function getQuantityDiscounts($tax=true)
    {
        if (null !== $this->discounts[$tax]) {
            return $this->discounts[$tax];
        }

        $sql = "SELECT * FROM %table.products_discount_quantity%
                WHERE products_id = :productId
                  AND discount_qty != 0
                ORDER BY discount_qty";

        $args = array('productId' => $this->product->getId());
        $discounts = ZMRuntime::getDatabase()->fetchAll($sql, $args, 'products_discount_quantity', 'ZMQuantityDiscount');

        if (0 < count($discounts)) {
            $product = $this->product;
            $basePrice = $this->getBasePrice(false);
            if (self::DISCOUNT_FROM_SPECIAL_PRICE == $product->getDiscountTypeFrom() && 0 != ($specialPrice = $this->getSpecialPrice(false))) {
                $basePrice = $specialPrice;
            }

            foreach ($discounts as $discount) {
                $price = 0;
                switch ($product->getDiscountType()) {
                    case self::DISCOUNT_TYPE_NONE:
                        $price = $this->getCalculatedPrice($tax);
                        break;
                    case self::DISCOUNT_TYPE_PERCENT:
                        $price = $basePrice - ($basePrice * ($discount->getValue() / 100));
                        break;
                    case self::DISCOUNT_TYPE_PRICE:
                        $price = $discount->getValue();
                        break;
                    case self::DISCOUNT_TYPE_AMOUNT:
                        $price = $basePrice - $discount->getValue();
                        break;
                    default:
                        throw new ZMException('invalid discount type: '.$product->getDiscountType());
                        break;
                }
                $price = $tax ? $this->getTaxRate()->addTax($price) : $price;
                $discount->setPrice($price);
            }
        }

        $this->discounts[$tax] = $discounts;

        return $this->discounts[$tax];
    }

    /**
     * Calculate discount for either product or the given amount
     * (for example to calculate discounts on attributes).
     *
     * @param float amount Optional amount; default is <code>null</code> to calculate the discount
     *  of the product price.
     * @return float The discounted amount.
     */
    public function calculateDiscount($amount=null)
    {
        $basePrice = $this->getBasePrice(false);
        $specialPrice = $this->getSpecialPrice(false);

        /*==================
        0 = flat amount off base price with a special
        1 = Percentage off base price with a special
        2 = New Price with a special

        5 = No Sale or Skip Products with Special_zm_calc_discount

        special options + option * 10
        0 = Ignore special and apply to Price
        1 = Skip Products with Specials switch to 5
        2 = Apply to Special Price

        If a special exist * 10+9

        0*100 + 0*10 = flat apply to price = 0 or 9
        0*100 + 1*10 = flat skip Specials = 5 or 59
        0*100 + 2*10 = flat apply to special = 20 or 209

        1*100 + 0*10 = Percentage apply to price = 100 or 1009
        1*100 + 1*10 = Percentage skip Specials = 110 or 1109 / 5 or 59
        1*100 + 2*10 = Percentage apply to special = 120 or 1209

        2*100 + 0*10 = New Price apply to price = 200 or 2009
        2*100 + 1*10 = New Price skip Specials = 210 or 2109 / 5 or 59
        2*100 + 2*10 = New Price apply to Special = 220 or 2209
        ====================*/

        $typeInfo = $this->container->get('salemakerService')->getSaleDiscountTypeInfo($this->product->getId());
        $discountTypeId = $typeInfo['type'];

        if (0 != $basePrice) {
            $special_price_discount = (0 != $specialPrice ? ($specialPrice/$basePrice) : 1);
        } else {
            $special_price_discount = '';
        }
        $discountAmount = $typeInfo['amount'];

        if ($discountTypeId == 120 || $discountTypeId == 1209 || $discountTypeId == 110 || $discountTypeId == 1109) {
            // percentage adjustment of discount
            $discountAmount = (0 != $discountAmount ? (100 - $discountAmount)/100 : 1);
        }

        if (null == $amount && 109 == $discountTypeId) {
            // XXX: ??? flat amount discount on Sale and Special with a special
            $discountAmount = 1;
        }

        if (null !== $amount) {
            switch ($discountTypeId) {
            case 5:
                // No Sale and No Special
                if (0 != $special_price_discount) {
                    $discountAmount = ($amount * $special_price_discount);
                } else {
                    $discountAmount = $amount;
                }
                break;
            case 59:
                // No Sale and Special
                $discountAmount = ($amount * $special_price_discount);
                break;
            case 120:
                // percentage discount Sale and Special without a special
                $discountAmount = ($amount * $discountAmount);
                break;
            case 1209:
                // percentage discount on Sale and Special with a special
                $calc = ($amount * $special_price_discount);
                $calc2 = $calc - ($calc * $discountAmount);
                $discountAmount = $calc - $calc2;
                break;
            case 110:
                // percentage discount Sale and Special without a special
                $discountAmount = ($amount * $discountAmount);
                break;
            case 1109:
                // percentage discount on Sale and Special with a special
                $discountAmount = ($amount * $special_price_discount);
                break;
            case 20:
                // flat amount discount Sale and Special without a special
                $discountAmount = ($amount - $discountAmount);
                break;
            case 209:
                // flat amount discount on Sale and Special with a special
                $calc = ($amount * $special_price_discount);
                $calc2 = ($calc - $discountAmount);
                $discountAmount = $calc2;
                break;
            case 10:
                // flat amount discount Sale and Special without a special
                $discountAmount = ($amount - $discountAmount);
                break;
            case 109:
                // flat amount discount on Sale and Special with a special
                $discountAmount = ($amount * $special_price_discount);
                break;
            case 220:
                // New Price amount discount Sale and Special without a special
                $discountAmount = ($amount * $special_price_discount);
                break;
            case 2209:
                // New Price amount discount on Sale and Special with a special
                $discountAmount = ($amount * $special_price_discount);
                break;
            case 210:
                // New Price amount discount Sale and Special without a special
                $discountAmount = ($amount * $special_price_discount);
                break;
            case 2109:
                // New Price amount discount on Sale and Special with a special
                $discountAmount = ($amount * $special_price_discount);
                break;
            case 0:
            case 9:
                // flat discount
                break;
            default:
                // XXX
                throw new ZMException('FTW!');
            }
        }

        return $discountAmount;
    }

}
