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
 */
?>
<?php


/**
 * All stuff related to product prices and offers.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class Offers extends ZMOffers {

    /**
     * Create new offers instance for the given product.
     *
     * @param ZMProduct product The product.
     */
    function __construct($product) {
        parent::__construct($product);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the calculated price.
     *
     * <p>This is the actual price, taking into account if sale or discount are available.</p>
     *
     * @param boolean tax Set to <code>true</code> to include tax (if applicable); default is <code>true</code>.
     * @param boolean groupDiscount Set to <code>true</code> to adjust prices according to product group discounts; default is <code>true</code>.
     * @return float The calculated price.
     */
    function getCalculatedPrice($tax=true, $groupDiscount=true) { 
        if ($this->product_->isFree()) {
            return 0;
        }

        $account = ZMRequest::getAccount();
        if (null == $account) {
            // no account, no price group
            return parent::getCalculatedPrice($tax);
        }

        $priceGroup = $account->getPriceGroup();
        if (null == $priceGroup) {
            // no price group
            return parent::getCalculatedPrice($tax);
        }

        $productGroupPricing = ProductGroupPricingService::instance()->getProductGroupPricing($this->product->id_, $priceGroup->getId(), true);
        if (null == $productGroupPricing) {
            // no product price group
            return parent::getCalculatedPrice($tax);
        }

        // now let's do everything without tax to avoi rounding issues
        if (0 != ($salePrice = $this->getSalePrice($tax))) {
            return $salePrice;
        } else if (0 != ($specialPrice = $this->getSpecialPrice($tax))) {
            return $specialPrice;
        } else {
            return $this->getBasePrice($tax); 
        }
    }

}

?>
