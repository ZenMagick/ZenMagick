<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

namespace ZenMagick\plugins\productGroupPricing\service;

/**
 * Add logic to transparently apply discounts.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Offers extends \ZMOffers
{
    private $productGroupPricing;
    private $lookupDone;

    /**
     * Create new offers instance for the given product.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Product product The product.
     */
    public function __construct($product)
    {
        parent::__construct($product);
        $this->productGroupPricing = null;
        $this->lookupDone = false;
    }

    /**
     * {@inheritDoc}
     */
    public function getProductPrice($tax=true)
    {
        $price = $this->product->getProductPrice();

        // check for fixed price
        if (null != ($productGroupPricing = $this->getProductGroupPricing())) {
            if ('#' == $productGroupPricing->getType()) {
                $price = $productGroupPricing->getDiscount();
            }
        }

        return $tax ? $this->getTaxRate()->addTax($price) : $price;
    }

    /**
     * Get the group pricing.
     *
     * @return ZMProductGroupPricing A <code>ProductGroupPricing</code> instance or <code>null</code>.
     */
    private function getProductGroupPricing()
    {
        if (!$this->lookupDone) {
            $this->lookupDone = true;
            $account = $this->container->get('request')->getAccount();
            if (null == $account) {
                // no account, no price group
                $this->productGroupPricing = null;

                return null;
            }

            $priceGroup = $account->getPriceGroup();
            if (null == $priceGroup) {
                // no price group
                $this->productGroupPricing = null;

                return null;
            }

            $productGroupPricings = $this->container->get('productGroupPricingService')->getProductGroupPricings($this->product->getId(), $priceGroup->getId(), true);
            if (0 < count($productGroupPricings)) {
                $this->productGroupPricing = array_pop($productGroupPricings);
            }
        }

        return $this->productGroupPricing;
    }

    /**
     * Adjust price.
     *
     * @param string priceMethod The method to use for the price lookup.
     * @param boolean tax Set to <code>true</code> to include tax (if applicable).
     * @param ProductGroupPricing productGroupPricing A <code>ProductGroupPricing</code>.
     */
    private function adjustPrice($priceMethod, $tax, $productGroupPricing)
    {
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
    public function getSalePrice($tax=true)
    {
        $productGroupPricing = $this->getProductGroupPricing();

        if (null != $productGroupPricing && $productGroupPricing->isAllowSaleSpecial()) {
            return $this->adjustPrice('getSalePrice', $tax, $productGroupPricing);
        }

        return parent::getSalePrice($tax);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpecialPrice($tax=true)
    {
        $productGroupPricing = $this->getProductGroupPricing();

        if (null != $productGroupPricing && $productGroupPricing->isAllowSaleSpecial()) {
            return $this->adjustPrice('getSpecialPrice', $tax, $productGroupPricing);
        }

        return parent::getSpecialPrice($tax);
    }

    /**
     * {@inheritDoc}
     */
    public function getBasePrice($tax=true)
    {
        $productGroupPricing = $this->getProductGroupPricing();

        if (null != $productGroupPricing) {
            return $this->adjustPrice('getBasePrice', $tax, $productGroupPricing);
        }

        return parent::getBasePrice($tax);
    }

}
