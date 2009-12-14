<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Add logic to transparently apply discounts.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.groupPricing
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
     * Get the group pricing.
     *
     * @return ProductGroupPricing A <code>ProductGroupPricing</code> instance or <code>null</code>.
     */
    private function getProductGroupPricing() {
        $account = ZMRequest::instance()->getAccount();
        if (null == $account) {
            // no account, no price group
            return null;
        }

        $priceGroup = $account->getPriceGroup();
        if (null == $priceGroup) {
            // no price group
            return null;
        }

        return ProductGroupPricingService::instance()->getProductGroupPricing($this->product_->getId(), $priceGroup->getId(), true);
    }

    /**
     * Adjust price.
     *
     * @param string priceMethod The method to use for the price lookup.
     * @param boolean tax Set to <code>true</code> to include tax (if applicable).
     * @param ProductGroupPricing productGroupPricing A <code>ProductGroupPricing</code>.
     */
    private function adjustPrice($priceMethod, $tax, $productGroupPricing) {
        // handle base price
        $price = parent::$priceMethod(!$productGroupPricing->isBeforeTax());

        // appy discount...
        switch ($productGroupPricing->getType()) {
            case '$':
                $price = $price - $productGroupPricing->getDiscount();
                break;
            case '%':
                $price = $price - (($price * $productGroupPricing->getDiscount()) / 100);
                break;
        }

        if ($tax && $productGroupPricing->isBeforeTax()) {
            $price = $this->getTaxRate()->addTax($price);
        }

        if (0 > $price) {
            // just in case
            $price = 0;
        }

        return $price;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalePrice($tax=true) {
        $productGroupPricing = $this->getProductGroupPricing();

        if (null != $productGroupPricing && !$productGroupPricing->isRegularPriceOnly()) {
            return $this->adjustPrice('getSalePrice', $tax, $productGroupPricing);
        }

        return parent::getSalePrice($tax);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecialPrice($tax=true) {
        $productGroupPricing = $this->getProductGroupPricing();

        if (null != $productGroupPricing && !$productGroupPricing->isRegularPriceOnly()) {
            return $this->adjustPrice('getSpecialPrice', $tax, $productGroupPricing);
        }

        return parent::getSpecialPrice($tax);
    }

    /**
     * {@inheritDoc}
     */
    public function getBasePrice($tax=true) {
        $productGroupPricing = $this->getProductGroupPricing();

        if (null != $productGroupPricing) {
            return $this->adjustPrice('getBasePrice', $tax, $productGroupPricing);
        }

        return parent::getBasePrice($tax);
    }

}

?>
