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

/**
 * Test product.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMProduct extends ZMTestCase {

    /**
     * Test existing manufacturer.
     */
    public function testExistingManufacturer() {
        $product = $this->container->get('productService')->getProductForId(19, 1);
        if ($this->assertNotNull($product)) {
            $manufacturer = $product->getManufacturer();
            if ($this->assertNotNull($manufacturer)) {
                $this->assertEqual(4, $manufacturer->getId());
            }
        }
    }

    /**
     * Test missing manufacturer.
     */
    public function testMissingManufacturer() {
        $product = $this->container->get('productService')->getProductForId(31, 1);
        if ($this->assertNotNull($product)) {
            $manufacturer = $product->getManufacturer();
            $this->assertNull($manufacturer);
        }
    }

    /**
     * Test null manufacturer.
     */
    public function testNULLManufacturer() {
        $product = $this->container->get('productService')->getProductForId(169, 1);
        if ($this->assertNotNull($product)) {
            $manufacturer = $product->getManufacturer();
            $this->assertNull($manufacturer);
        }
    }

}
