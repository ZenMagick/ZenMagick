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
 * @author DerManoMann
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMAttributeValue extends ZMModel {
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
    private $taxRate_;


    /**
     * Create new instance.
     */
    function __construct($id=0, $name='') {
        parent::__construct();
        $this->set('attributeValueId', $id);
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
     * @param int qty The quantity.
     * @return float The one time charge.
     */
    protected function getQtyPrice($qtyPrices, $qty) {
        $qtyPriceMap = split("[:,]" , $qtyPrices);
        $price = 0;
        $size = count($qtyPriceMap);
        if (1 < $size) {
            for ($ii=0; $ii<$size; $ii+=2) {
                $price = $qtyPriceMap[$ii+1];
                if ($qty <= $qtyPriceMap[$ii]) {
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
        if (ZMSettings::get('isDiscountAttributePriceFactor') && 0 != $discountPrice) {
            return $discountPrice * ($priceFactor - $priceFactorOffset);
        } else {
            return $price * ($priceFactor - $priceFactorOffset);
        }
    }

    /**
     * Get the final attribute price without discount.
     *
     * @param int qty The quantity.
     * @return float The price.
     * @todo: proper handling of priceFactor and PriceFactorOffset properties
     */
    protected function getFinalPriceForQty($qty) {
        $price = $this->price_;
        if ('-' == $this->pricePrefix_) {
            $price = -$this->price_;
        }

        // qty onetime discounts
        $price += $this->getQtyPrice($this->getQtyPrices(), $qty);

        // price factor
        $product = ZMProducts::instance()->getProductForId($this->attribute_->getProductId());
        $offers = $product->getOffers();
        $discountPrice = $offers->isSale() ? $offers->getSalePrice(false) : $offers->getSpecialPrice(false);

        $price += $this->getPriceFactorCharge($offers->getCalculatedPrice(false), $discountPrice ,$this->getPriceFactor(), $this->getPriceFactorOffset());

        return $price;
    }

    /**
     * Get the final (and discounted) value price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return double The price.
     */
    public function getPrice($tax=true) { 
        //TODO: cache value
        $price = $this->price_;
        if ($this->isDiscounted_) {
            $price = $this->getFinalPriceForQty(1);
            $price = _zm_get_discount_calc($this->attribute_->getProductId(), true, $price);
        }

        return $tax ? $this->taxRate_->addTax($price) : $price;
    }

    /**
     * Get the final one time attribute price.
     *
     * @param int qty The quantity.
     * @return float The price.
     * @todo: proper handling of priceFactor and PriceFactorOffset properties
     */
    protected function getFinalOneTimePriceForQty($qty) {
        $price = $this->oneTimePrice_;

        // qty onetime discounts
        $price += $this->getQtyPrice($this->getQtyPricesOneTime(), $qty);

        // price factor
        $product = ZMProducts::instance()->getProductForId($this->attribute_->getProductId());
        $offers = $product->getOffers();
        $discountPrice = $offers->isSale() ? $offers->getSalePrice(false) : $offers->getSpecialPrice(false);

        $price += $this->getPriceFactorCharge($offers->getCalculatedPrice(false), $discountPrice ,$this->getPriceFactorOneTime(), $this->getPriceFactorOneTimeOffset());

        return $price;
    }

    /**
     * Get the final one time price.
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @return double The attributes one time price.
     */
    public function getOneTimePrice($tax=true) { 
        //TODO: cache
        $price = $this->oneTimePrice_;
        if (0 != $price || $this->isPriceFactorOneTime_) {
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

}


  // work in progress
  function _zm_get_discount_calc($product_id, $attributes_id = false, $attributes_amount = false, $check_qty= false) {
    // no charge
    if ($attributes_id > 0 && 0 == $attributes_amount) {
        return 0;
    }

    $product = ZMProducts::instance()->getProductForId($product_id);
    $offers = $product->getOffers();

    $new_products_price = $offers->getBasePrice(false);
    $new_special_price = $offers->getSpecialPrice(false);
    $new_sale_price = $offers->getSalePrice(false);

    $discount_type_id = _zm_get_products_sale_discount_type($product_id);

    if ($new_products_price != 0) {
      $special_price_discount = ($new_special_price != 0 ? ($new_special_price/$new_products_price) : 1);
    } else {
      $special_price_discount = '';
    }
    $sale_maker_discount = _zm_get_products_sale_discount_type($product_id, '', 'amount');

    // percentage adjustment of discount
    if (($discount_type_id == 120 or $discount_type_id == 1209) or ($discount_type_id == 110 or $discount_type_id == 1109)) {
      $sale_maker_discount = ($sale_maker_discount != 0 ? (100 - $sale_maker_discount)/100 : 1);
    }

   $qty = $check_qty;

// fix here
// BOF: percentage discounts apply to price
    switch (true) {
      case (zen_get_discount_qty($product_id, $qty) and !$attributes_id):
        // discount quanties exist and this is not an attribute
        // $this->contents[$products_id]['qty']
        $check_discount_qty_price = zen_get_products_discount_price_qty($product_id, $qty, $attributes_amount);
//echo 'How much 1 ' . $qty . ' : ' . $attributes_amount . ' vs ' . $check_discount_qty_price . '<br />';
//echo 'zen_get_discount_calc: qty1: '.$attributes_id.": ".$check_discount_qty_price."<BR>";
        return $check_discount_qty_price;
        break;

      case (zen_get_discount_qty($product_id, $qty) and $product->isPricedByAttributes()):
        // discount quanties exist and this is not an attribute
        // $this->contents[$products_id]['qty']
        $check_discount_qty_price = zen_get_products_discount_price_qty($product_id, $qty, $attributes_amount);
//echo 'How much 2 ' . $qty . ' : ' . $attributes_amount . ' vs ' . $check_discount_qty_price . '<br />';

//echo 'zen_get_discount_calc: qty2: '.$attributes_id.": ".$check_discount_qty_price."<BR>";
        return $check_discount_qty_price;
        break;

      case ($discount_type_id == 5):
        // No Sale and No Special
//        $sale_maker_discount = 1;
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            if ($special_price_discount != 0) {
              $calc = ($attributes_amount * $special_price_discount);
            } else {
              $calc = $attributes_amount;
            }

            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
//echo 'How much 3 - ' . $qty . ' : ' . $product_id . ' : ' . $qty . ' x ' .  $attributes_amount . ' vs ' . $check_discount_qty_price . ' - ' . $sale_maker_discount . '<br />';
        break;
      case ($discount_type_id == 59):
        // No Sale and Special
//        $sale_maker_discount = $special_price_discount;
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discount apply to price

// BOF: percentage discounts apply to Sale
      case ($discount_type_id == 120):
        // percentage discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 1209):
        // percentage discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $calc2 = $calc - ($calc * $sale_maker_discount);
            $sale_maker_discount = $calc - $calc2;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discounts apply to Sale

// BOF: percentage discounts skip specials
      case ($discount_type_id == 110):
        // percentage discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
//            $sale_maker_discount = $sale_maker_discount;
            if ($attributes_amount != 0) {
//            $calc = ($attributes_amount * $special_price_discount);
//            $calc2 = $calc - ($calc * $sale_maker_discount);
//            $sale_maker_discount = $calc - $calc2;
              $calc = $attributes_amount - ($attributes_amount * $sale_maker_discount);
              $sale_maker_discount = $calc;
            } else {
              $sale_maker_discount = $sale_maker_discount;
            }
          }
        }
        break;
      case ($discount_type_id == 1109):
        // percentage discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//            $calc2 = $calc - ($calc * $sale_maker_discount);
//            $sale_maker_discount = $calc - $calc2;
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discounts skip specials

// BOF: flat amount discounts
      case ($discount_type_id == 20):
        // flat amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount - $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 209):
        // flat amount discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $calc2 = ($calc - $sale_maker_discount);
            $sale_maker_discount = $calc2;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: flat amount discounts

// BOF: flat amount discounts Skip Special
      case ($discount_type_id == 10):
        // flat amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount - $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 109):
        // flat amount discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = 1;
        } else {
          // compute attribute amount based on Special
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: flat amount discounts Skip Special

// BOF: New Price amount discounts
      case ($discount_type_id == 220):
        // New Price amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 2209):
        // New Price amount discount on Sale and Special with a special
        if (!$attributes_id) {
//          $sale_maker_discount = $sale_maker_discount;
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: New Price amount discounts

// BOF: New Price amount discounts - Skip Special
      case ($discount_type_id == 210):
        // New Price amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 2109):
        // New Price amount discount on Sale and Special with a special
        if (!$attributes_id) {
//          $sale_maker_discount = $sale_maker_discount;
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: New Price amount discounts - Skip Special

      case ($discount_type_id == 0 or $discount_type_id == 9):
      // flat discount
//echo 'zen_get_discount_calc: sm1: '.$attributes_id.": ".$sale_maker_discount."<BR>";
        return $sale_maker_discount;
        break;
      default:
        $sale_maker_discount = 7000;
        break;
    }

//echo 'zen_get_discount_calc: sm2: '.$attributes_id.": ".$sale_maker_discount."<BR>";
    return $sale_maker_discount;
  }




  function _zm_get_products_sale_discount_type($product_id = false, $categories_id = false, $return_value = false) {
    global $db;

/*

0 = flat amount off base price with a special
1 = Percentage off base price with a special
2 = New Price with a special

5 = No Sale or Skip Products with Special

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

*/

    $product = ZMProducts::instance()->getProductForId($product_id);

// get products category
    if ($categories_id == true) {
      $check_category = $categories_id;
    } else {
      //$check_category = zen_get_products_category_id($product_id);
      $check_category = $product->getDefaultCategory()->getId();
    }
/*
    $deduction_type_array = array(array('id' => '0', 'text' => DEDUCTION_TYPE_DROPDOWN_0),
                                  array('id' => '1', 'text' => DEDUCTION_TYPE_DROPDOWN_1),
                                  array('id' => '2', 'text' => DEDUCTION_TYPE_DROPDOWN_2));
*/
    $sale_exists = 'false';
    $sale_maker_discount = '';
    $sale_maker_special_condition = '';
    $salemaker_sales = $db->Execute("select sale_id, sale_status, sale_name, sale_categories_all, sale_deduction_value, sale_deduction_type, sale_pricerange_from, sale_pricerange_to, sale_specials_condition, sale_categories_selected, sale_date_start, sale_date_end, sale_date_added, sale_date_last_modified, sale_date_status_change from " . TABLE_SALEMAKER_SALES . " where sale_status='1'");
    while (!$salemaker_sales->EOF) {
      $categories = explode(',', $salemaker_sales->fields['sale_categories_all']);
  	  while (list($key,$value) = each($categories)) {
	      if ($value == $check_category) {
          $sale_exists = 'true';
  	      $sale_maker_discount = $salemaker_sales->fields['sale_deduction_value'];
  	      $sale_maker_special_condition = $salemaker_sales->fields['sale_specials_condition'];
	        $sale_maker_discount_type = $salemaker_sales->fields['sale_deduction_type'];
	        break;
        }
      }
      $salemaker_sales->MoveNext();
    }

    //$check_special = zen_get_products_special_price($product_id, true);
    $offers = $product->getOffers();
    $special_price = $offers->getSpecialPrice(false);

    if ($sale_exists == 'true' and $sale_maker_special_condition != 0) {
      $sale_maker_discount_type = (($sale_maker_discount_type * 100) + ($sale_maker_special_condition * 10));
    } else {
      $sale_maker_discount_type = 5;
    }

    //if (!$check_special) {
    if (!$special_price) {
      // do nothing
    } else {
      $sale_maker_discount_type = ($sale_maker_discount_type * 10) + 9;
    }

    switch (true) {
      case (!$return_value):
        return $sale_maker_discount_type;
        break;
      case ($return_value == 'amount'):
        return $sale_maker_discount;
        break;
      default:
        return 'Unknown Request';
        break;
    }
  }

?>
