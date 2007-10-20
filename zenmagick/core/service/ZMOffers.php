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
 * All stuff related to product prices and offers.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMOffers extends ZMService {
    var $product_;
    var $basePrice_;
    var $specialPrice_;
    var $salePrice_;
    var $taxRate_;
    var $discountPercent_;


    /**
     * Create new offers instance for the given product.
     *
     * @param ZMProduct product The product.
     */
    function ZMOffers($product) {
        parent::__construct();

        $this->product_ = $product;
        $this->basePrice_ = null;
        $this->specialPrice_ = null;
        $this->salePrice_ = null;
        $this->discountPercent_ = 0;
        $this->taxRate_ = zm_get_tax_rate($product->taxClassId_);
        $this->_calculatePrice();
    }

    /**
     * Create new offers instance for the given product.
     *
     * @param ZMProduct product The product.
     */
    function __construct($product) {
        $this->ZMOffers($product);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if there are attribute prices that will affect the final price.
     *
     * @return boolean <code>true</code> if attribute prices exist.
     */
    function isAttributePrice() { return zm_has_product_attributes_values($this->product_->getId()); }

    /**
     * Set the product.
     *
     * @param ZMProduct product The product.
     */
    function setProduct(&$product) { $this->product_ = $product; }

    /**
     * Get the product price.
     *
     * @return float The product price.
     */
    function getProductPrice() {
        return zm_add_tax($this->product_->price_, $this->taxRate_);
    }

    /**
     * Get the base price; this is the lowest possible price.
     *
     * <p>The base price consists of the product price plus the lowest attribute price (if any).</p>
     *
     * @return float The base price.
     */
    function getBasePrice() {
        if (null === $this->basePrice_) {
            $this->basePrice_ = zm_add_tax($this->_getBasePrice(), $this->taxRate_);
        }
        return $this->basePrice_;
    }

    /**
     * Calculate the base price.
     */
    function _getBasePrice() {
        if (!$this->product_->pricedByAttributes_)
            return $this->product_->price_;

        $db = $this->getDB();
        // **non** display_only **but** attributes_price_base_included
        $sql = "select options_id, price_prefix, options_values_price, attributes_display_only, attributes_price_base_included
                from " . TABLE_PRODUCTS_ATTRIBUTES . "
                where products_id = :productId
                and attributes_display_only != '1' and attributes_price_base_included='1'". "
                order by options_id, price_prefix, options_values_price";
        $sql = $db->bindVars($sql, ':productId', $this->product_->id_, 'integer');
        $results = $db->Execute($sql);

        // add attributes price to price
        $basePrice = $this->product_->price_;
        if (0 < $results->RecordCount()) {
            $options_id = 'x';
            while (!$results->EOF) {
                if ($options_id != $results->fields['options_id']) {
                    $options_id = $results->fields['options_id'];
                    $basePrice += $results->fields['options_values_price'];
                }
                $results->MoveNext();
            }
        }
        return $basePrice;
    }

    /**
     * Get the special price.
     *
     * @return float The special price.
     */
    function getSpecialPrice() {
        if (null === $this->specialPrice_) {
            $this->specialPrice_ = zm_add_tax($this->_getSpecialPrice(), $this->taxRate_);
        }
        return $this->specialPrice_;
    }

    /**
     * Calculate the special price.
     */
    function _getSpecialPrice() {
        $db = $this->getDB();
        $sql = "select specials_new_products_price
                from " . TABLE_SPECIALS .  "
                where products_id = :productId and status='1'";
        $sql = $db->bindVars($sql, ":productId", $this->product_->getId(), "integer");
        $results = $db->Execute($sql);
        $specialPrice = null;
        if (0 < $results->RecordCount()) {
    	      $specialPrice = $results->fields['specials_new_products_price'];
        }
        return !zm_is_empty($specialPrice) ? $specialPrice : null;
    }

    /**
     * Get the discount price.
     *
     * @return float The discount price.
     */
    function getSalePrice() {
        if (null === $this->salePrice_) {
            // no tax here, as sale price is based on base/special price
            $this->salePrice_ = $this->_getSalePrice();
        }
        return $this->salePrice_;
    }

    /**
     * Calculate the discount price.
     */
    function _getSalePrice() {
  	    $basePrice = $this->getBasePrice();
  	    $specialPrice = $this->getSpecialPrice();

        $db = $this->getDB();
        // get available sales
        $sql = "select sale_specials_condition, sale_deduction_value, sale_deduction_type
                from " . TABLE_SALEMAKER_SALES . "
                where sale_categories_all like '%," . $this->product_->masterCategoryId_ . ",%' and sale_status = '1'
                and (sale_date_start <= now() or sale_date_start = '0001-01-01')
                and (sale_date_end >= now() or sale_date_end = '0001-01-01')
                and (sale_pricerange_from <= :basePrice  or sale_pricerange_from = '0')
                and (sale_pricerange_to >= :basePrice or sale_pricerange_to = '0')";
        $sql = $db->bindVars($sql, ":basePrice", $basePrice, "currency");
        $results = $db->Execute($sql);

        if ($results->RecordCount() < 1) {
           return 0;
        }

        // read result
        $saleType = $results->fields['sale_deduction_type'];
        $saleValue = $results->fields['sale_deduction_value'];
        $saleCondition = $results->fields['sale_specials_condition'];

        // best special price available
        $bestSpecialPrice = $specialPrice ? $specialPrice : $basePrice;

        switch ($saleType) {
          case 0:
            // fixed discount
            $saleBasePrice = $basePrice - $saleValue;
            $saleSpecialPrice = $bestSpecialPrice - $saleValue;
            break;
          case 1: // %
            $saleBasePrice = $basePrice - (($basePrice * $saleValue) / 100);
            $saleSpecialPrice = $bestSpecialPrice - (($bestSpecialPrice * $saleValue) / 100);
            break;
          case 2: // fixed new price
            $saleBasePrice = $saleValue;
            $saleSpecialPrice = $saleValue;
            break;
          default:
            // gosh, how'd we get here
            return $bestSpecialPrice;
        }

        // sanitize
        $saleBasePrice = $saleBasePrice < 0 ? 0 : $saleBasePrice;
        $saleSpecialPrice = $saleSpecialPrice < 0 ? 0 : $saleSpecialPrice;

        if (!$specialPrice) {
            return number_format($saleBasePrice, 4, '.', '');
        } else {
            switch($saleCondition){
                case 0:
                    return number_format($saleBasePrice, 4, '.', '');
                    break;
                case 1:
                    return number_format($specialPrice, 4, '.', '');
                    break;
                case 2:
                    return number_format($saleSpecialPrice, 4, '.', '');
                    break;
                default:
                    return number_format($specialPrice, 4, '.', '');
            }
        }
    }


    /**
     * Calculate the (best) price.
     */
    function _calculatePrice() {
        $basePrice = $this->getBasePrice();
        $specialPrice = $this->getSpecialPrice();
        $salePrice = $this->getSalePrice();

        // calculate discount
        $this->discountPercent_ = 0;
        if (0 != $specialPrice || 0 != $salePrice) {
            if (0 != $salePrice) {
                $this->discountPercent_ = number_format(100 - (($salePrice / $basePrice) * 100), SHOW_SALE_DISCOUNT_DECIMALS);
            } else {
                $this->discountPercent_ = number_format(100 - (($specialPrice / $basePrice) * 100), SHOW_SALE_DISCOUNT_DECIMALS);
            }
        }
    }

    /**
     * Get the discount.
     *
     * @return float The discount in percent.
     */
    function getDiscount() { return $this->discountPercent_; }

    /**
     * Get the tax rate for the product.
     *
     * @return float The tax rate.
     */
    function getTaxRate() { return $this->taxRate_; }

    /**
     * Checks if a special price is available.
     *
     * @return boolean <code>true</code> if a special price is available.
     */
    function isSpecial() { return 0 != $this->specialPrice_ && $this->specialPrice_ != $this->basePrice_ && !$this->isSale(); }

    /**
     * Checks if a sale price is available.
     *
     * @return boolean <code>true</code> if a sale price is available.
     */
    function isSale() { return 0 != $this->salePrice_; }

    /**
     * Get the calculated price.
     *
     * <p>This is the actual price, taking into account if sale or discount are available.</p>
     *
     * @return float The calculated price.
     */
    function getCalculatedPrice() { 
        if ($this->product_->isFree()) {
            return 0;
        } else if (0 != $this->salePrice_) {
            return  $this->salePrice_;
        } else if (0 != $this->specialPrice_) {
            return  $this->specialPrice_;
        } else {
            return $this->basePrice_; 
        }
    }

}

?>
