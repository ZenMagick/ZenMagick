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

use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test calculated product prices.
 *
 * <p>This test requires a vanilla demo store database setup.</p>
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestProductPricing extends TestCase
{
    /**
     * Test product base price.
     */
    public function testBasePrice()
    {
        foreach ($this->container->get('productService')->getAllProducts(false, 1) as $product) {
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual(zen_get_products_base_price($product->getId()), $offers->getBasePrice(false), '%s productId='.$product->getId());
        }
    }

    /**
     * Test special price.
     */
    public function testSpecialPrice()
    {
        foreach ($this->container->get('productService')->getAllProducts(false, 1) as $product) {
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual(zen_get_products_special_price($product->getId(), true), $offers->getSpecialPrice(false), '%s productId='.$product->getId());
        }
    }

    /**
     * Test sale price.
     */
    public function testSalePrice()
    {
        foreach ($this->container->get('productService')->getAllProducts(false, 1) as $product) {
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual(zen_get_products_special_price($product->getId(), false), $offers->getSalePrice(false), '%s productId='.$product->getId());
        }
    }

}
