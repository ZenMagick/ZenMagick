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
 * Test calculated attribute prices.
 *
 * <p>This test requires a vanilla demo store database setup.</p>
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestAttributePricing extends TestCase {

    /**
     * Test attribute price.
     */
    public function testValuePrice() {
        foreach ($this->container->get('productService')->getAllProducts(false, 1) as $product) {
            foreach ($product->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $value) {
                    if ($value->isDiscounted()) {
                        $er = error_reporting(0);
                        $zprice = zen_get_attributes_price_final($value->getAttributeValueDetailsId(), 1, '');
                        $zprice = zen_get_discount_calc($product->getId(), true, $zprice);
                        error_reporting($er);
                    } else {
                        $zprice = $value->getValuePrice();
                    }
                    // default is 4 decimal digits...
                    $this->assertEqual((int)(10000*$zprice), (int)(10000*$value->getPrice(false)), '%s productId='.$product->getId().' $valueId='.$value->getAttributeValueId().'/'.$value->getAttributeValueDetailsId());
                }
            }
        }
    }

    /**
     * Test one time price.
     */
    public function testValueOneTimePrice() {
        foreach ($this->container->get('productService')->getAllProducts(false, 1) as $product) {
            foreach ($product->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $value) {
                    $er = error_reporting(0);
                    $zprice = zen_get_attributes_price_final_onetime($value->getAttributeValueDetailsId(), 1, '');
                    error_reporting($er);
                    $this->assertEqual($zprice, $value->getOneTimePrice(false), '%s productId='.$product->getId().' $valueId='.$value->getAttributeValueId());
                }
            }
        }
    }

}
