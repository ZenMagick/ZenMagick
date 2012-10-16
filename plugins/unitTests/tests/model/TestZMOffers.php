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
 * Test offers.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMOffers extends TestCase {

    /**
     * Set up.
     */
    public function setUp() {
        parent::setUp();
    }


    /**
     * Test quantity discounts
     */
    public function testQtyDiscounts() {
        $product = $this->container->get('productService')->getProductForId(176, 1);
        if ($this->assertNotNull($product)) {
            $offers = $product->getOffers();
            if ($this->assertNotNull($offers)) {
                $discounts = $offers->getQuantityDiscounts(false);
                $this->assertTrue(is_array($discounts));
                if ($this->assertEqual(5, count($discounts))) {
                    // grab one and check details
                    $discount = $discounts[3];
                    $this->assertEqual(176, $discount->getProductId());
                    $this->assertEqual(48, $discount->getQuantity());
                    $this->assertEqual(30.00, $discount->getValue());
                    $this->assertEqual(70.00, $discount->getPrice());
                }
            }

        }
    }

    /**
     * Test products via the macro function.
     */
    public function testViaToolbox() {
        $tests = array(
            127 => array(
                array('qty' => '1-2', 'price' => 15),
                array('qty' => '3-5', 'price' => 14.25),
                array('qty' => '6-8', 'price' => 13.95),
                array('qty' => '9-11', 'price' => 13.80),
                array('qty' => '12+', 'price' => 13.50),
            ),
            130 => array(
                array('qty' => '1-2', 'price' => 10),
                array('qty' => '3-5', 'price' => 9.5),
                array('qty' => '6-8', 'price' => 9.3),
                array('qty' => '9-11', 'price' => 9.2),
                array('qty' => '12+', 'price' => 9),
            ),
            175 => array(
                array('qty' => '1', 'price' => 60),
                array('qty' => '2', 'price' => 58.2),
                array('qty' => '3', 'price' => 57.6),
                array('qty' => '4', 'price' => 57),
                array('qty' => '5', 'price' => 56.4),
                array('qty' => '6', 'price' => 55.8),
                array('qty' => '7', 'price' => 55.2),
                array('qty' => '8', 'price' => 54.6),
                array('qty' => '9', 'price' => 54),
                array('qty' => '10+', 'price' => 53.4),
            ),
            176 => array(
                array('qty' => '1-11', 'price' => 100),
                array('qty' => '12-23', 'price' => 95),
                array('qty' => '24-35', 'price' => 90),
                array('qty' => '36-47', 'price' => 80),
                array('qty' => '48-59', 'price' => 70),
                array('qty' => '60+', 'price' => 60),
            ),
            177 => array(
                array('qty' => '1-5', 'price' => 75),
                array('qty' => '6-11', 'price' => 71.25),
                array('qty' => '12-23', 'price' => 67.5),
                array('qty' => '24-35', 'price' => 60),
                array('qty' => '36-47', 'price' => 52.5),
                array('qty' => '48-59', 'price' => 45),
                array('qty' => '60+', 'price' => 37.5),
            ),
            178 => array(
                array('qty' => '1', 'price' => 50),
                array('qty' => '2', 'price' => 58.2),
                array('qty' => '3', 'price' => 57.6),
                array('qty' => '4', 'price' => 57),
                array('qty' => '5', 'price' => 56.4),
                array('qty' => '6', 'price' => 55.8),
                array('qty' => '7', 'price' => 55.2),
                array('qty' => '8', 'price' => 54.6),
                array('qty' => '9', 'price' => 54),
                array('qty' => '10+', 'price' => 53.4),
            ),
        );

        foreach ($tests as $productId => $expected) {
            $product = $this->container->get('productService')->getProductForId($productId, 1);
            $details = $this->container->get('macroTool')->buildQuantityDiscounts($product, false);
            if (!$this->assertEqual($expected, $details, '%s: productId: '.$productId))
                var_dump($details);
        }
    }

}
